<?php
/**
 * @package Polylang-Pro
 */

/**
 * Advanced media functionalities
 *
 * @since 1.9
 */
class PLL_Admin_Advanced_Media {
	/**
	 * Stores the plugin options.
	 *
	 * @var array
	 */
	public $options;

	/**
	 * @var PLL_Model
	 */
	public $model;

	/**
	 * @var PLL_CRUD_Posts
	 */
	public $posts;

	/**
	 * Constructor: setups filters and actions
	 *
	 * @since 1.9
	 * @since 2.7 Now registers an option for the Translate bulk action.
	 *
	 * @param PLL_Admin $polylang Polylang object.
	 */
	public function __construct( &$polylang ) {
		$this->options = &$polylang->options;
		$this->model   = &$polylang->model;
		$this->posts   = &$polylang->posts;

		add_action( 'pll_bulk_translate_options_init', array( $this, 'add_bulk_translate_options' ) );

		if ( ! empty( $this->options['media']['duplicate'] ) ) {
			add_filter( 'wp_insert_attachment_data', array( $this, 'attachment_data' ), 10, 2 );
			add_action( 'add_attachment', array( $this, 'duplicate_media' ), 20 ); // After Polylang.
		}
	}

	/**
	 * Registers options of the Translate bulk action.
	 *
	 * @since 3.6.5
	 *
	 * @param PLL_Bulk_Translate $bulk_translate Instance of `PLL_Bulk_Translate`.
	 * @return void
	 */
	public function add_bulk_translate_options( $bulk_translate ): void {
		$bulk_translate->register_options(
			array(
				new PLL_Media_Bulk_Option(
					array(
						'name'        => 'pll_copy_media',
						'description' => __( 'Copy original items to selected languages', 'polylang-pro' ),
					),
					$this->model,
					$this->posts
				),
			)
		);
	}

	/**
	 * Disables the duplication if we are uploading a plugin or theme.
	 *
	 * @since 3.0
	 *
	 * @param array $data    An array of slashed, sanitized, and processed attachment post data, unused.
	 * @param array $postarr An array of slashed and sanitized attachment post data, but not processed.
	 * @return array Unmodified $data.
	 */
	public function attachment_data( $data, $postarr ) {
		if ( 'upgrader' === $postarr['context'] ) {
			add_filter( 'pll_enable_duplicate_media', '__return_false' );
		}
		return $data;
	}

	/**
	 * Creates media translations each time a new media is uploaded
	 *
	 * @since 1.9
	 *
	 * @param int $post_id The id of the attachment to duplicate.
	 * @return void
	 */
	public function duplicate_media( $post_id ) {
		static $avoid_recursion = false;

		// Avoid recursion and bails if adding a translation from PLL_Admin_Filters_Media::translate_media().
		if ( $avoid_recursion || doing_action( 'admin_init' ) ) {
			return;
		}

		/**
		 * Filters whether to enable the media duplication
		 *
		 * @since 2.1.1
		 *
		 * @param bool $enable  Whether to enable the media duplication. Defaults to true.
		 * @param int  $post_id Media id.
		 */
		if ( ! apply_filters( 'pll_enable_duplicate_media', true, $post_id ) ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/media.php'; // Needed when uploading audio or video files from the block editor.
		$avoid_recursion = true;

		$src_language = $this->model->post->get_language( $post_id );

		if ( ! empty( $src_language ) ) {
			// Don't attempt to create already existing translations (useful in case the function is reused).
			$languages = array_diff( $this->model->get_languages_list( array( 'fields' => 'slug' ) ), array_keys( $this->model->post->get_translations( $post_id ) ) );

			foreach ( $languages as $lang ) {
				$tr_id = $this->posts->create_media_translation( $post_id, $lang );

				if ( ! empty( $tr_id ) ) {
					$post = get_post( $tr_id );
					wp_maybe_generate_attachment_metadata( $post );
				}
			}
		}

		$avoid_recursion = false;
	}
}
