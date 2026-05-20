<?php
/**
 * @package  Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Integrations\ACF\Entity;

use PLL_Language;
use WP_Term;

/**
 * This class is part of the ACF compatibility.
 * Handles terms.
 *
 * @since 3.7
 */
class Term extends Abstract_Object {
	/**
	 * Returns the object ID.
	 *
	 * @since 3.7
	 *
	 * @param WP_Term $object The object.
	 * @return int
	 */
	protected function get_object_id( $object ): int {
		return $object->term_id;
	}

	/**
	 * Transforms a term ID to the corresponding ACF post ID.
	 *
	 * @since 3.7
	 *
	 * @param int $id Term ID.
	 * @return string ACF post ID.
	 */
	protected static function acf_id( $id ) {
		return 'term_' . $id;
	}

	/**
	 * Returns source object ID passed in the main request if exists.
	 *
	 * @since 3.7
	 *
	 * @return int
	 */
	protected function get_from_id_in_request(): int {
		if ( isset( $_GET['taxonomy'], $_GET['from_tag'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return (int) $_GET['from_tag']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		return 0;
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
		return 'term';
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
		/**
		 * The wrapping element is not the same on term creation page as on term edition page.
		 * If there's a term ID, we're on an editing page, otherwise we're on a creation page.
		 */
		$element = $this->get_id() > 0 ? 'tr' : 'div';

		ob_start();
		acf_render_fields( array( $field ), static::acf_id( $this->get_id() ), $element, 'field' );
		return (string) ob_get_clean();
	}

	/**
	 * Ajax response for changing the language in the term edit or creation page.
	 *
	 * @since 3.8
	 *
	 * @return void
	 */
	public static function on_ajax_term_lang_choice(): void {
		check_ajax_referer( 'pll_language', '_pll_nonce' );

		if ( ! isset( $_POST['fields'], $_POST['lang'], $_POST['term_id'], $_POST['taxonomy'] ) ) {
			wp_die( 0 );
		}

		$tax = get_taxonomy( sanitize_key( $_POST['taxonomy'] ) );
		if ( ! $tax || ! PLL()->model->is_translated_taxonomy( $tax->name ) ) {
			wp_die( 0 );
		}

		$term_id = (int) $_POST['term_id'];
		if ( ( $term_id > 0 && ! current_user_can( 'edit_term', $term_id ) )
			|| ! current_user_can( $tax->cap->manage_terms ) ) {
			wp_die( -1 );
		}

		$language = PLL()->model->languages->get( sanitize_key( $_POST['lang'] ) );
		if ( ! $language instanceof PLL_Language ) {
			wp_die( 0 );
		}

		$response = ( new self( $term_id ) )->on_lang_choice(
			$language,
			sanitize_text_field( wp_unslash( $_POST['fields'] ) )
		);

		wp_send_json( $response );
	}

	/**
	 * Enqueues Javascript to refresh fields on language change for term creation and editing pages.
	 *
	 * @since 3.8
	 *
	 * @return void
	 */
	public static function admin_enqueue_scripts() {
		global $pagenow, $taxnow;

		if ( ! in_array( $pagenow, array( 'edit-tags.php', 'term.php' ), true ) ) {
			return;
		}

		if ( ! PLL()->model->is_translated_taxonomy( $taxnow ) ) {
			return;
		}

		self::enqueue_scripts();
	}
}
