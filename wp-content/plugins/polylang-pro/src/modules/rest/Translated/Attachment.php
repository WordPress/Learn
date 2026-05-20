<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\REST\Translated;

use PLL_REST_API;

/**
 * Expose attachments language and translations in the REST API
 *
 * @since 3.8
 */
class Attachment extends Post {
	/**
	 * Constructor
	 *
	 * @since 2.2
	 *
	 * @param PLL_REST_API $rest_api Instance of PLL_REST_API.
	 */
	public function __construct( PLL_REST_API $rest_api ) {
		parent::__construct( $rest_api, array( 'attachment' ) );

		add_action( 'add_attachment', array( $this, 'set_media_language' ) );
	}

	/**
	 * Assigns the language to the edited media.
	 *
	 * When a media is edited in the block image, a new media is created and we need to set the language from the original one.
	 *
	 * @see https://make.wordpress.org/core/2020/07/20/editing-images-in-the-block-editor/ the new WordPress 5.5 feature: Editing Images in the Block Editor.
	 *
	 * @since 2.8
	 * @since 3.8 Moved from PLL_REST_Post.
	 *
	 * @param int $post_id Post id.
	 * @return void
	 */
	public function set_media_language( $post_id ) {
		$id = $this->request->get_id();
		if ( empty( $id ) || (int) $post_id === $id ) {
			return;
		}
		$lang = $this->model->post->get_language( $id );
		if ( ! empty( $lang ) ) {
			$this->model->post->set_language( $post_id, $lang );
		}
	}
}
