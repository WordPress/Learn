<?php
/**
 * Markdown Import
 *
 * This functionality has been disabled as of 2020-08-12. All of the lesson plans have been imported
 * to learn.wordpress.org and can be updated via the WP admin interface. Leaving this here for now
 * in case we need to re-activate for some reason.
 */

namespace WPOrg_Learn;

use WP_Error, WP_Query;

// These actions/filters should not be added while markdown import is disabled.
//add_action( 'init', array( 'WPOrg_Learn\Markdown_Import', 'action_init' ) );
//add_action( 'wporg_learn_manifest_import', array( 'WPOrg_Learn\Markdown_Import', 'action_wporg_learn_manifest_import' ) );
//add_action( 'wporg_learn_markdown_import', array( 'WPOrg_Learn\Markdown_Import', 'action_wporg_learn_markdown_import' ) );
//add_action( 'load-post.php', array( 'WPOrg_Learn\Markdown_Import', 'action_load_post_php' ) );
//add_action( 'edit_form_after_title', array( 'WPOrg_Learn\Markdown_Import', 'action_edit_form_after_title' ) );
//add_action( 'save_post', array( 'WPOrg_Learn\Markdown_Import', 'action_save_post' ) );
//add_filter( 'cron_schedules', array( 'WPOrg_Learn\Markdown_Import', 'filter_cron_schedules' ) );

// This filter is still necessary because the lesson plans that were originally imported from GitHub still require
// that image assets be loaded from the same repositories.
add_filter( 'the_content', array( 'WPOrg_Learn\Markdown_Import', 'replace_image_links' ) );

/**
 * Class Markdown_Import
 *
 * @package WPOrg_Learn
 */
class Markdown_Import {

	private static $lesson_plan_manifest = 'https://wptrainingteam.github.io/manifest.json';
	private static $input_name           = 'wporg-learn-markdown-source';
	private static $meta_key             = 'wporg_learn_markdown_source';
	private static $nonce_name           = 'wporg-learn-markdown-source-nonce';
	private static $submit_name          = 'wporg-learn-markdown-import';
	private static $supported_post_type  = 'lesson-plan';
	private static $posts_per_page       = 100;

	/**
	 * Register our cron task if it doesn't already exist
	 */
	public static function action_init() {
		if ( ! wp_next_scheduled( 'wporg_learn_manifest_import' ) ) {
			wp_schedule_event( time(), '15_minutes', 'wporg_learn_manifest_import' );
		}
		if ( ! wp_next_scheduled( 'wporg_learn_markdown_import' ) ) {
			wp_schedule_event( time(), '15_minutes', 'wporg_learn_markdown_import' );
		}
	}

	/**
	 * Actions taken on `wporg_learn_manifest_import` event.
	 */
	public static function action_wporg_learn_manifest_import() {
		$response = wp_remote_get( self::$lesson_plan_manifest );
		if ( is_wp_error( $response ) ) {
			return $response;
		} elseif ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return new WP_Error( 'invalid-http-code', 'Markdown source returned non-200 http code.' );
		}
		$manifest = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! $manifest ) {
			return new WP_Error( 'invalid-manifest', 'Manifest did not unfurl properly.' );
		}
		// Fetch all lesson plan posts for comparison
		$q        = new WP_Query( array(
			'post_type'      => self::$supported_post_type,
			'post_status'    => 'publish',
			'posts_per_page' => self::$posts_per_page,
		) );
		$existing = $q->posts;
		$created  = 0;
		foreach ( $manifest as $doc ) {
			// Already exists
			if ( wp_filter_object_list( $existing, array( 'post_name' => $doc['slug'] ) ) ) {
				if ( class_exists( 'WP_CLI' ) ) {
					\WP_CLI::log( "Found {$doc['slug']} already exits." );
				}
				continue;
			}
			$post_parent = null;
			if ( ! empty( $doc['parent'] ) ) {
				// Find the parent in the existing set
				$parents = wp_filter_object_list( $existing, array( 'post_name' => $doc['parent'] ) );
				if ( ! empty( $parents ) ) {
					$parent = array_shift( $parents );
				} else {
					// Create the parent and add it to the stack
					if ( isset( $manifest[ $doc['parent'] ] ) ) {
						$parent_doc = $manifest[ $doc['parent'] ];
						$parent     = self::create_post_from_manifest_doc( $parent_doc );
						if ( $parent ) {
							$created++;
							$existing[] = $parent;
						} else {
							continue;
						}
					} else {
						continue;
					}
				}
				$post_parent = $parent->ID;
			}
			$post = self::create_post_from_manifest_doc( $doc, $post_parent );
			if ( $post ) {
				$created++;
				$existing[] = $post;
			}
		}
		if ( class_exists( 'WP_CLI' ) ) {
			\WP_CLI::success( "Successfully created {$created} lesson plan pages." );
		}
	}

	/**
	 * Create a new lesson plan page from the manifest document
	 */
	private static function create_post_from_manifest_doc( $doc, $post_parent = null ) {
		$post_data = array(
			'post_type'   => self::$supported_post_type,
			'post_status' => 'publish',
			'post_parent' => $post_parent,
			'post_title'  => sanitize_text_field( wp_slash( $doc['title'] ) ),
			'post_name'   => sanitize_title_with_dashes( $doc['slug'] ),
		);
		$post_id   = wp_insert_post( $post_data );
		if ( ! $post_id ) {
			return false;
		}
		if ( class_exists( 'WP_CLI' ) ) {
			\WP_CLI::log( "Created post {$post_id} for {$doc['title']}." );
		}
		update_post_meta( $post_id, self::$meta_key, esc_url_raw( $doc['markdown_source'] ) );
		return get_post( $post_id );
	}

	/**
	 * Actions taken on `wporg_learn_markdown_import` event.
	 */
	public static function action_wporg_learn_markdown_import() {
		$q       = new WP_Query( array(
			'post_type'      => self::$supported_post_type,
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'posts_per_page' => self::$posts_per_page,
		) );
		$ids     = $q->posts;
		$success = 0;
		foreach ( $ids as $id ) {
			$ret = self::update_post_from_markdown_source( $id );
			if ( class_exists( 'WP_CLI' ) ) {
				if ( is_wp_error( $ret ) ) {
					\WP_CLI::warning( $ret->get_error_message() );
				} else {
					\WP_CLI::log( "Updated {$id} from markdown source" );
					$success++;
				}
			}
		}
		if ( class_exists( 'WP_CLI' ) ) {
			$total = count( $ids );
			\WP_CLI::success( "Successfully updated {$success} of {$total} lesson plan pages." );
		}
	}

	/**
	 * Handle a request to import from the markdown source
	 */
	public static function action_load_post_php() {
		if ( ! isset( $_GET[ self::$submit_name ] )
			|| ! isset( $_GET[ self::$nonce_name ] )
			|| ! isset( $_GET['post'] ) ) {
			return;
		}
		$post_id = (int) $_GET['post'];
		if ( ! current_user_can( 'edit_post', $post_id )
			|| ! wp_verify_nonce( $_GET[ self::$nonce_name ], self::$input_name )
			|| get_post_type( $post_id ) !== self::$supported_post_type ) {
			return;
		}

		$response = self::update_post_from_markdown_source( $post_id );
		if ( is_wp_error( $response ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			wp_die( $response->get_error_message() );
		}

		wp_safe_redirect( get_edit_post_link( $post_id, 'raw' ) );
		exit;
	}

	/**
	 * Add an input field for specifying Markdown source
	 */
	public static function action_edit_form_after_title( $post ) {
		if ( $post->post_type !== self::$supported_post_type ) {
			return;
		}
		$markdown_source = get_post_meta( $post->ID, self::$meta_key, true );
		?>
		<label>Markdown source: <input
			type="text"
			name="<?php echo esc_attr( self::$input_name ); ?>"
			value="<?php echo esc_attr( $markdown_source ); ?>"
			placeholder="Enter a URL representing a markdown file to import"
			size="50" />
		</label>
		<?php
		if ( $markdown_source ) :
			$update_link = add_query_arg( array(
				self::$submit_name => 'import',
				self::$nonce_name  => wp_create_nonce( self::$input_name ),
			), get_edit_post_link( $post->ID, 'raw' ) );
			?>
				<a class="button button-small button-primary" href="<?php echo esc_url( $update_link ); ?>">Import</a>
			<?php endif; ?>
		<?php wp_nonce_field( self::$input_name, self::$nonce_name ); ?>
		<?php
	}

	/**
	 * Save the Markdown source input field
	 */
	public static function action_save_post( $post_id ) {

		if ( ! isset( $_POST[ self::$input_name ] )
			|| ! isset( $_POST[ self::$nonce_name ] )
			|| get_post_type( $post_id ) !== self::$supported_post_type ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST[ self::$nonce_name ], self::$input_name ) ) {
			return;
		}

		$markdown_source = '';
		if ( ! empty( $_POST[ self::$input_name ] ) ) {
			$markdown_source = esc_url_raw( $_POST[ self::$input_name ] );
		}
		update_post_meta( $post_id, self::$meta_key, $markdown_source );
	}

	/**
	 * Filter cron schedules to add a 15 minute schedule
	 */
	public static function filter_cron_schedules( $schedules ) {
		$schedules['15_minutes'] = array(
			'interval' => 15 * MINUTE_IN_SECONDS,
			'display'  => '15 minutes',
		);
		return $schedules;
	}

	/**
	 * Update a post from its Markdown source
	 */
	private static function update_post_from_markdown_source( $post_id ) {
		$markdown_source = self::get_markdown_source( $post_id );
		if ( is_wp_error( $markdown_source ) ) {
			return $markdown_source;
		}
		if ( ! function_exists( 'jetpack_require_lib' ) ) {
			return new WP_Error( 'missing-jetpack-require-lib', 'jetpack_require_lib() is missing on system.' );
		}

		// Transform GitHub repo HTML pages into their raw equivalents
		//$markdown_source = preg_replace( '#https?://github\.com/([^/]+/[^/]+)/blob/(.+)#', 'https://raw.githubusercontent.com/$1/$2', $markdown_source );
		$markdown_source = add_query_arg( 'v', time(), $markdown_source );
		$response        = wp_remote_get( $markdown_source );
		if ( is_wp_error( $response ) ) {
			return $response;
		} elseif ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return new WP_Error( 'invalid-http-code', 'Markdown source returned non-200 http code.' );
		}

		$markdown = wp_remote_retrieve_body( $response );
		// Strip YAML doc from the header
		$markdown = preg_replace( '#^---(.+)---#Us', '', $markdown );

		$title = null;
		if ( preg_match( '/^#\s(.+)/', $markdown, $matches ) ) {
			$title    = $matches[1];
			$markdown = preg_replace( '/^#\s(.+)/', '', $markdown );
		}

		// Transform to HTML and save the post
		jetpack_require_lib( 'markdown' );
		$parser = new \WPCom_GHF_Markdown_Parser();
		$html   = $parser->transform( $markdown );
		$html   = self::replace_markdown_checkboxes( $html );

		$post_data = array(
			'ID'           => $post_id,
			'post_content' => wp_filter_post_kses( wp_slash( $html ) ),
		);
		if ( ! is_null( $title ) ) {
			$post_data['post_title'] = sanitize_text_field( wp_slash( $title ) );
		}
		wp_update_post( $post_data );
		return true;
	}

	/**
	 * Retrieve the markdown source URL for a given post.
	 */
	public static function get_markdown_source( $post_id ) {
		$markdown_source = get_post_meta( $post_id, self::$meta_key, true );
		if ( ! $markdown_source ) {
			return new WP_Error( 'missing-markdown-source', 'Markdown source is missing for post.' );
		}

		return $markdown_source;
	}

	/**
	 * Replace markdown checkboxes in the post-processed HTML.
	 *
	 * @param string $html The HTML after translation from markup.
	 *
	 * @return string The HTML after potentially replacing markdown checkboxes with HTML ones.
	 */
	public static function replace_markdown_checkboxes( $html ) {
		$empty_check_markup = '<input type="checkbox" id="" disabled="" class="task-list-item-checkbox">';
		$full_check_markup  = '<input type="checkbox" id="" disabled="" class="task-list-item-checkbox" checked="">';

		// We need to allow inputs with all of our attributes for wp_filter_post_kses().
		global $allowedposttags;

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$allowedposttags['input'] = array(
			'type'     => array(),
			'disabled' => array(),
			'checked'  => array(),
			'class'    => array(),
			'id'       => array(),
		);

		$html = preg_replace( '/\[ \]/', $empty_check_markup, $html );
		$html = preg_replace( '/\[x\]/', $full_check_markup, $html );
		return $html;
	}

	/**
	 * Source images from the GitHub repo.
	 *
	 * @param string $content
	 *
	 * @return string|string[]
	 */
	public static function replace_image_links( $content ) {
		$post_id         = get_the_ID();
		$markdown_source = self::get_markdown_source( $post_id );
		if ( is_wp_error( $markdown_source ) ) {
			return $content;
		}
		$markdown_source = str_replace( '/README.md', '', $markdown_source );
		$content         = str_replace( '<img src="/images/', '<img src="' . $markdown_source . '/images/', $content );

		return $content;
	}
}
