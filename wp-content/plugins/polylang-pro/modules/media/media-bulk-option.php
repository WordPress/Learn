<?php
/**
 * @package Polylang-Pro
 */

/**
 * Duplicates medias in Bulk Translate actions
 *
 * @since 2.7
 */
class PLL_Media_Bulk_Option extends PLL_Bulk_Translate_Option {
	/**
	 * The post CRUD to create translations.
	 *
	 * @since 2.7
	 *
	 * @var PLL_CRUD_Posts
	 */
	private $posts;

	/**
	 * PLL_Media_Bulk_Action constructor.
	 *
	 * @since 2.7
	 *
	 * @param array          $args {
	 *     string $name
	 *     string $description
	 * }.
	 * @param PLL_Model      $model An instance to the current PLL_Model.
	 * @param PLL_CRUD_Posts $posts Used to create translations.
	 */
	public function __construct( $args, $model, $posts ) {
		parent::__construct( $args, $model );
		$this->posts = $posts;
	}

	/**
	 * Checks whether the option should be selectable by the user.
	 *
	 * @since 2.7
	 *
	 * @return bool
	 */
	public function is_available() {
		$screen = get_current_screen();
		return $screen && 'upload' === $screen->base && current_user_can( 'upload_files' );
	}


	/**
	 * Duplicates a media object
	 *
	 * @since 2.7
	 *
	 * @param int    $object_id The media id.
	 * @param string $lang A language locale.
	 * @return void
	 */
	public function translate( $object_id, $lang ) {
		$this->posts->create_media_translation( $object_id, $lang );
	}
}
