<?php
/**
 * @package  Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Integrations\ACF\Entity;

use WP_Post;
use PLL_Admin_Links;
use PLL_Sync_Post_Model;
use PLL_Language;
use WP_Syntex\Polylang_Pro\Integrations\ACF\Strategy\Copy;
use WP_Syntex\Polylang_Pro\Integrations\ACF\Strategy\Copy_All;

/**
 * This class is part of the ACF compatibility.
 * Handles posts.
 *
 * @since 3.7
 */
class Post extends Abstract_Object {

	/**
	 * The previous language slug of the target post.
	 *
	 * @var string
	 */
	protected static $previous_lang = '';

	/**
	 * Returns the object ID.
	 *
	 * @since 3.7
	 *
	 * @param WP_Post $object The object.
	 * @return int
	 */
	protected function get_object_id( $object ): int {
		return $object->ID;
	}

	/**
	 * Transforms a post ID to the corresponding ACF post ID.
	 *
	 * @since 3.7
	 *
	 * @param int $id Post ID.
	 * @return int ACF post ID.
	 */
	protected static function acf_id( $id ) {
		return $id;
	}

	/**
	 * Returns source object ID passed in the main request if exists.
	 *
	 * @since 3.7
	 *
	 * @return int
	 */
	protected function get_from_id_in_request(): int {
		if ( ! PLL()->links instanceof PLL_Admin_Links ) {
			return 0;
		}

		$data = PLL()->links->get_data_from_new_post_translation_request();

		return ! empty( $data['from_post'] ) ? $data['from_post']->ID : 0;
	}

	/**
	 * Returns current object type.
	 *
	 * @since 3.7
	 *
	 * @return string
	 * @phpstan-return non-falsy-string
	 */
	public function get_type(): string {
		return 'post';
	}

	/**
	 * Renders the field with its wrapping element type and instruction render position (label|field).
	 *
	 * @since 3.7
	 *
	 * @param array $field The field to render.
	 * @return string HTML rendered string of the field.
	 */
	protected function render_field( array $field ): string {
		ob_start();
		acf_render_fields( array( $field ), static::acf_id( $this->get_id() ), 'div', 'label' );
		return (string) ob_get_clean();
	}

	/**
	 * Copies or synchronizes ACF custom fields when using Polylang's copy post function (and not the post-new.php where ACF filters are applied).
	 * (e.g. using bulk translate, creating a synchronized post).
	 *
	 * @since 3.7
	 *
	 * @param int    $tr_post_id ID of the target post.
	 * @param string $lang       Language of the target post.
	 * @param string $sync      `sync` if doing synchro, `copy` otherwise.
	 * @return void
	 *
	 * @phpstan-param 'sync'|'copy' $sync
	 */
	public function on_post_synchronized( $tr_post_id, $lang, $sync ) {
		$lang = PLL()->model->get_language( $lang );
		if ( empty( $lang ) ) {
			return;
		}

		$this->maybe_reset_fields_store( $lang );

		if ( PLL_Sync_Post_Model::COPY === $sync ) {
			$this->apply_to_all_fields( new Copy(), $tr_post_id, array( 'target_language' => $lang ) );
			return;
		}

		// Sync all custom fields between synchronized posts.
		$post_id = $this->get_id();
		foreach ( pll_get_post_translations( $post_id ) as $tr_lang => $tr_id ) {
			if ( $tr_id === $post_id || ! PLL()->sync_post_model->are_synchronized( $post_id, $tr_id ) ) {
				continue;
			}
			/** @var PLL_Language */
			$tr_lang = PLL()->model->get_language( $tr_lang );

			$this->apply_to_all_fields( new Copy_All(), $tr_id, array( 'target_language' => $tr_lang ) );
		}
	}

	/**
	 * Resets the `fields` store to translate the default values in the correct language.
	 * Only if the current target language has been changed.
	 *
	 * @since 3.7
	 *
	 * @param PLL_Language $lang Language of the target post.
	 * @return void
	 */
	protected function maybe_reset_fields_store( PLL_Language $lang ) {
		if ( self::$previous_lang !== $lang->slug ) {
			$store = acf_get_store( 'fields' );
			$store->reset();
			self::$previous_lang = $lang->slug;
		}
	}

	/**
	 * Ajax response for changing the language in the post metabox.
	 *
	 * @since 2.0
	 * @since 3.8 Renamed from `acf_post_lang_choice` and Moved from `Ajax_Lang_Choice` to `Post`.
	 *
	 * @return void
	 */
	public static function on_ajax_post_lang_choice(): void {
		check_ajax_referer( 'pll_language', '_pll_nonce' );

		if ( ! isset( $_POST['fields'], $_POST['lang'], $_POST['post_id'] ) ) {
			wp_die( 0 );
		}

		$post_id   = (int) $_POST['post_id'];
		$post_type = get_post_type( $post_id );

		if ( ! $post_type || ! PLL()->model->is_translated_post_type( $post_type ) ) {
			wp_die( 0 );
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( -1 );
		}

		$language = PLL()->model->languages->get( sanitize_key( $_POST['lang'] ) );
		if ( ! $language instanceof PLL_Language ) {
			wp_die( 0 );
		}

		$response = ( new self( $post_id ) )->on_lang_choice(
			$language,
			sanitize_text_field( wp_unslash( $_POST['fields'] ) )
		);

		wp_send_json( $response );
	}

	/**
	 * Enqueues Javascript to refresh fields on language change for post creation and editing pages.
	 *
	 * @since 3.8
	 *
	 * @return void
	 */
	public static function admin_enqueue_scripts() {
		global $pagenow, $typenow;

		if ( in_array( $pagenow, array( 'post.php', 'post-new.php' ), true ) &&
			PLL()->model->is_translated_post_type( $typenow ) ) {
			self::enqueue_scripts();
		}
	}
}
