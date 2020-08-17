<?php
namespace WPOrg_Learn;

class Lesson_Plan {

	/**
	 *  Register Lesson Plan Post Type
	 */
	public static function lesson_plan_post_type() {

		$labels = array(
			'name'                  => _x( 'Lesson Plans', 'Post Type General Name', 'wporg_learn' ),
			'singular_name'         => _x( 'Lesson Plan', 'Post Type Singular Name', 'wporg_learn' ),
			'menu_name'             => __( 'Lesson Plans', 'wporg_learn' ),
			'name_admin_bar'        => __( 'Lesson Plan', 'wporg_learn' ),
			'archives'              => __( 'Lesson Archives', 'wporg_learn' ),
			'attributes'            => __( 'Lesson Attributes', 'wporg_learn' ),
			'parent_item_colon'     => __( 'Parent Lesson:', 'wporg_learn' ),
			'all_items'             => __( 'All Lessons', 'wporg_learn' ),
			'add_new_item'          => __( 'Add New Lesson', 'wporg_learn' ),
			'add_new'               => __( 'Add New', 'wporg_learn' ),
			'new_item'              => __( 'New Lesson Plan', 'wporg_learn' ),
			'edit_item'             => __( 'Edit Lesson Plan', 'wporg_learn' ),
			'update_item'           => __( 'Update Lesson Plan', 'wporg_learn' ),
			'view_item'             => __( 'View Lesson', 'wporg_learn' ),
			'view_items'            => __( 'View Lessons', 'wporg_learn' ),
			'search_items'          => __( 'Search Lesson', 'wporg_learn' ),
			'not_found'             => __( 'Not found', 'wporg_learn' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'wporg_learn' ),
			'featured_image'        => __( 'Featured Image', 'wporg_learn' ),
			'set_featured_image'    => __( 'Set featured image', 'wporg_learn' ),
			'remove_featured_image' => __( 'Remove featured image', 'wporg_learn' ),
			'use_featured_image'    => __( 'Use as featured image', 'wporg_learn' ),
			'insert_into_item'      => __( 'Insert into lesson', 'wporg_learn' ),
			'uploaded_to_this_item' => __( 'Uploaded to this lesson', 'wporg_learn' ),
			'items_list'            => __( 'Lessons list', 'wporg_learn' ),
			'items_list_navigation' => __( 'Lessons list navigation', 'wporg_learn' ),
			'filter_items_list'     => __( 'Filter Lessons list', 'wporg_learn' ),
		);
		$args   = array(
			'label'               => __( 'Lesson Plan', 'wporg_learn' ),
			'description'         => __( 'WordPress.org Training Lesson Plan', 'wporg_learn' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor', 'comments', 'revisions', 'custom-fields' ),
			'taxonomies'          => array( 'duration', 'level', 'audience', 'instruction_type' ),
			'hierarchical'        => true,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-welcome-learn-more',
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => 'lesson-plans',
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'page',
			'show_in_rest'        => true,
		);
		register_post_type( 'lesson-plan', $args );
	}

	/**
	 * Append an "Edit on GitHub" link to Lesson Plan document titles
	 */
	public static function filter_the_title_edit_link( $title, $id = null ) {
		// Only apply to the main title for the document.
		if ( ! is_singular( 'lesson_plan' )
			|| ! is_main_query()
			|| ! in_the_loop()
			|| is_embed()
			|| get_queried_object_id() !== $id ) {
			return $title;
		}

		$markdown_source = self::get_markdown_edit_link( get_the_ID() );
		if ( ! $markdown_source ) {
			return $title;
		}

		return $title . ' <a class="github-edit" href="' . esc_url( $markdown_source ) . '"><img src="' . esc_url( plugins_url( 'assets/images/github-mark.svg', dirname( __FILE__ ) ) ) . '"> <span>Edit</span></a>';
	}

	/**
	 * Learn lesson plan pages are maintained in the GitHub repo, so the edit
	 * link should ridirect to there.
	 */
	public static function redirect_edit_link_to_github( $link, $post_id, $context ) {
		if ( is_admin() ) {
			return $link;
		}
		$post = get_post( $post_id );
		if ( ! $post ) {
			return $link;
		}

		if ( 'lesson_plan' !== $post->post_type ) {
			return $link;
		}

		$markdown_source = self::get_markdown_edit_link( $post_id );
		if ( ! $markdown_source ) {
			return $link;
		}

		if ( 'display' === $context ) {
			$markdown_source = esc_url( $markdown_source );
		}

		return $markdown_source;
	}

	/**
	 * The o2 plugin does inline editing, so we also need to remove the class name that it looks for.
	 *
	 * The o2 plugin obeys the edit_post capability for displaying the edit link, so we also need to manually
	 * add the edit link if it isn't there - it always redirects to GitHub, so it doesn't need to
	 * obey the edit_post capability in this instance.
	 */
	public static function redirect_o2_edit_link_to_github( $actions, $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return $actions;
		}

		if ( 'lesson_plan' !== $post->post_type ) {
			return $actions;
		}

		$markdown_source = self::get_markdown_edit_link( $post_id );
		if ( ! $markdown_source ) {
			return $actions;
		}

		/*
		 * Define our own edit post action for o2.
		 *
		 * Notable differences from the original are:
		 * - the 'href' parameter always goes to the GitHub source.
		 * - the 'o2-edit' class is missing, so inline editing is disabled.
		 */
		$edit_action = array(
			'action'       => 'edit',
			'href'         => $markdown_source,
			'classes'      => array( 'edit-post-link' ),
			'rel'          => $post_id,
			'initialState' => 'default',
		);

		// Find and replace the existing edit action.
		$replaced = false;
		foreach ( $actions as &$action ) {
			if ( 'edit' === $action['action'] ) {
				$action   = $edit_action;
				$replaced = true;
				break;
			}
		}
		unset( $action );

		// If there was no edit action replaced, add it in manually.
		if ( ! $replaced ) {
			$actions[30] = $edit_action;
		}

		return $actions;
	}

	/**
	 * Get a link to the wptrainingteam GitHub repo.
	 *
	 * @param int $post_id
	 *
	 * @return mixed|string|string[]|\WP_Error
	 */
	private static function get_markdown_edit_link( $post_id ) {
		$markdown_source = Markdown_Import::get_markdown_source( $post_id );
		if ( is_wp_error( $markdown_source ) ) {
			return '';
		}
		if ( 'wptrainingteam.github.io' !== parse_url( $markdown_source, PHP_URL_HOST )
			|| false !== stripos( $markdown_source, '/edit/master/' ) ) {
			return $markdown_source;
		}
		$markdown_source = str_replace( '/lesson-plan', '', $markdown_source );
		$markdown_source = str_replace( 'wptrainingteam.github.io', 'github.com/wptrainingteam', $markdown_source );
		$markdown_source = str_replace( '/README.md', '/edit/dev/README.md', $markdown_source );
		return $markdown_source;
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
		$markdown_source = Markdown_Import::get_markdown_source( $post_id );
		if ( is_wp_error( $markdown_source ) ) {
			return $content;
		}
		$markdown_source = str_replace( '/README.md', '', $markdown_source );
		$content         = str_replace( '<img src="/images/', '<img src="' . $markdown_source . '/images/', $content );

		return $content;
	}
}
