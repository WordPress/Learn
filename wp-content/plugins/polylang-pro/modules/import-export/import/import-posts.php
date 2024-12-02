<?php
/**
 * @package Polylang-Pro
 */

/**
 * A class to handle posts import.
 *
 * @since 3.3
 */
class PLL_Import_Posts implements PLL_Import_Object_Interface {
	/**
	 * Handle translation of posts
	 *
	 * @var PLL_Translation_Post_Model
	 */
	protected $translation_post_model;

	/**
	 * The success counter.
	 *
	 * @var int
	 */
	protected $success;

	/**
	 * The posts status to import with.
	 *
	 * @var string
	 */
	private $post_status;

	/**
	 * The non existing post ids for the warning.
	 *
	 * @var int[]
	 */
	protected $non_existing_post_ids = array();

	/**
	 * The imported source post ids.
	 *
	 * @var int[]
	 */
	protected $post_ids = array();

	/**
	 * Constructor
	 *
	 * @since 3.3
	 *
	 * @param PLL_Translation_Post_Model $translation_post_model The PLL_Translation_Post_Model object.
	 */
	public function __construct( $translation_post_model ) {
		$this->translation_post_model = $translation_post_model;
		add_action( 'pll_after_post_import', array( $this, 'process_translated_post' ), 10, 2 );
	}

	/**
	 * Handles the import of posts.
	 *
	 * @since 3.3
	 *
	 * @param array        $entry           The current entry to import.
	 * @param PLL_Language $target_language The targeted language for import.
	 */
	public function translate( $entry, $target_language ) {
		// Non matching post source id.
		if ( ! get_post( $entry['id'] ) ) {
			$this->non_existing_post_ids[] = $entry['id'];
			return;
		}

		if ( empty( $this->post_status ) ) {
			$this->post_status = $this->get_post_status();
		}
		$entry['fields']['post_status'] = $this->post_status;

		$is_success = $this->translation_post_model->translate( $entry, $target_language );
		if ( $is_success ) {
			++$this->success;

			// Store the post ids during the import process.
			$this->post_ids[] = $entry['id'];
		}
	}

	/**
	 * Performs actions on imported posts.
	 * Translates posts parent.
	 *
	 * @since 3.3
	 *
	 * @param PLL_Language $target_language The targeted language for import.
	 * @param int[]        $post_ids        The imported post ids of the import.
	 * @return void
	 */
	public function process_translated_post( $target_language, $post_ids ) {
		$post_ids = array_filter( array_map( 'absint', (array) $post_ids ) );
		if ( ! empty( $post_ids ) && $target_language instanceof PLL_Language ) {
			$this->translation_post_model->translate_parents( $post_ids, $target_language );
		}
	}

	/**
	 * Retrieves the status for the imported posts in the HTTP request.
	 *
	 * @since 3.3
	 *
	 * @return string The post status, publish or draft.
	 */
	protected function get_post_status() {
		check_admin_referer( PLL_Import_Action::ACTION_NAME, PLL_Import_Action::NONCE_NAME );
		if ( isset( $_POST['post-status'] ) && 'publish' === $_POST['post-status'] ) {
			return 'publish';
		}
		return 'draft';
	}

	/**
	 * Get update notices to display.
	 *
	 * @since 3.3
	 *
	 * @return WP_Error
	 */
	public function get_updated_notice() {
		if ( ! $this->success ) {
			return new WP_Error();
		}

		return new WP_Error(
			'pll_import_posts_success',
			sprintf(
				/* translators: %d is a number of posts translations */
				_n( '%d post translation updated.', '%d posts translations updated.', $this->success, 'polylang-pro' ),
				$this->success
			),
			'success'
		);
	}

	/**
	 * Get warnings notices to display.
	 *
	 * @since 3.3
	 *
	 * @return WP_Error
	 */
	public function get_warning_notice() {
		if ( empty( $this->non_existing_post_ids ) ) {
			return new WP_Error();
		}

		return new WP_Error(
			'pll_import_posts_warning',
			sprintf(
				/* translators: %s is the post IDs */
				_n(
					'Warning: a matching source wasn\'t found for post ID: %s',
					'Warning: matching sources weren\'t found for post IDs: %s',
					count( $this->non_existing_post_ids ),
					'polylang-pro'
				),
				wp_sprintf_l( '%l', $this->non_existing_post_ids )
			),
			'warning'
		);
	}

	/**
	 * Returns the object type.
	 *
	 * @since 3.3
	 *
	 * @return string
	 */
	public function get_type() {
		return PLL_Import_Export::TYPE_POST;
	}

	/**
	 * Returns the imported post ids.
	 *
	 * @since 3.3
	 *
	 * @return int[]
	 */
	public function get_imported_object_ids() {
		return $this->post_ids;
	}
}
