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
		$this->model->post->create_media_translation( $object_id, $lang );
	}
}
