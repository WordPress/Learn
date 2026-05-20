<?php
/**
 * @package Polylang-Pro
 */

/**
 * A class to handle posts import.
 *
 * @since 3.3
 *
 * @phpstan-import-type EntryData from PLL_Translation_Post_Model
 */
class PLL_Import_Posts implements PLL_Import_Object_Interface {
	/**
	 * Handle translation of posts.
	 *
	 * @var PLL_Translation_Post_Model
	 */
	private $translation_model;

	/**
	 * The success counter.
	 * Null means that the translation process has not been fired yet.
	 *
	 * @var int|null
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
	 * Service to manage user capabilities, espcecially 'unfiltered_html'.
	 *
	 * @var PLL_Manage_User_Capabilities
	 */
	protected $user_capabilities_manager;

	/**
	 * Constructor.
	 *
	 * @since 3.3
	 *
	 * @param PLL_Translation_Post_Model $translation_model The object to handle translations.
	 */
	public function __construct( PLL_Translation_Post_Model $translation_model ) {
		$this->translation_model = $translation_model;
		$this->user_capabilities_manager = new PLL_Manage_User_Capabilities();
	}

	/**
	 * Handles the import of posts.
	 *
	 * @since 3.3
	 *
	 * @param array        $entry           The current entry to import.
	 * @param PLL_Language $target_language The targeted language for import.
	 *
	 * @phpstan-param EntryData $entry
	 */
	public function translate( $entry, $target_language ) {
		// Make sure `$this->success` is not `null`.
		$this->success = (int) $this->success;

		$source_post = get_post( $entry['id'] );
		// Non matching post source id.
		if ( ! $source_post ) {
			$this->non_existing_post_ids[] = $entry['id'];
			return;
		}

		if ( empty( $this->post_status ) ) {
			$this->post_status = $this->get_post_status();
		}
		$entry['fields']['post_status'] = $this->post_status;

		$this->user_capabilities_manager->forbid_unfiltered_html( $source_post );

		$result = $this->translation_model->translate( $entry, $target_language );
		if ( ! is_wp_error( $result ) ) {
			++$this->success;

			// Store the post ids during the import process.
			$this->post_ids[] = $entry['id'];
		}

		$this->user_capabilities_manager->allow_unfiltered_html();
	}

	/**
	 * Performs actions after an import process.
	 *
	 * @since 3.7
	 *
	 * @param int[]        $ids             The entity ids to process after import.
	 * @param PLL_Language $target_language The target language.
	 * @return void
	 */
	public function do_after_import_process( array $ids, PLL_Language $target_language ) {

		$this->translation_model->do_after_process( $ids, $target_language );
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
	 * Returns update notices to display.
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
	 * Returns warnings notices to display.
	 *
	 * @since 3.3
	 *
	 * @return WP_Error
	 */
	public function get_warning_notice() {
		if ( ! empty( $this->non_existing_post_ids ) ) {
			if ( 1 === count( $this->non_existing_post_ids ) ) {
				/* translators: %s is the post ID */
				$message = __( 'Warning: a matching source wasn\'t found for post ID: %s', 'polylang-pro' );
			} else {
				/* translators: %s is a list of post IDs */
				$message = __( 'Warning: matching sources weren\'t found for post IDs: %s', 'polylang-pro' );
			}
			return new WP_Error(
				'pll_import_posts_no_matching_sources',
				sprintf( $message, wp_sprintf_l( '%l', $this->non_existing_post_ids ) ),
				'warning'
			);
		} elseif ( isset( $this->success ) && ! $this->success ) {
			return new WP_Error(
				'pll_import_posts_nothing_imported',
				__( 'No posts were translated. Please check that the original posts in your file match those on the site.', 'polylang-pro' ),
				'warning'
			);
		}

		return new WP_Error();
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
