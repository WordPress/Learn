<?php
/**
 * @package Polylang-Pro
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class that defines PLL's current language in the site editor's screen.
 *
 * @since 3.2
 */
class PLL_FSE_Language extends PLL_FSE_Abstract_Module implements PLL_Module_Interface {

	/**
	 * Returns the module's name.
	 *
	 * @since 3.2
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'fse_language';
	}

	/**
	 * Sub-module init.
	 *
	 * @since 3.2
	 *
	 * @return self
	 */
	public function init() {
		add_filter( 'pll_admin_current_language', array( $this, 'define_current_language_in_site_editor' ), 10, 2 );
		return $this;
	}

	/**
	 * Defines the current language in the site editor.
	 *
	 * @since 3.2
	 *
	 * @param  PLL_Language|false|null $curlang  Instance of the current language.
	 * @param  PLL_Admin_Base          $polylang Instance of the main Polylang's object.
	 * @return PLL_Language|false|null
	 */
	public function define_current_language_in_site_editor( $curlang, $polylang ) {
		if ( ! $polylang instanceof PLL_Admin_Base || ! PLL_FSE_Tools::is_site_editor() ) {
			return $curlang;
		}

		$editor_lang = $this->get_site_editor_language();

		if ( empty( $editor_lang ) ) {
			return false;
		}

		return $editor_lang;
	}

	/**
	 * Returns the language object to use in the site editor.
	 *
	 * @since 3.2
	 * @since 3.5 Removed `$model` parameter.
	 *
	 * @return PLL_Language|false
	 */
	private function get_site_editor_language() {
		if ( empty( $_GET['postType'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return $this->model->get_default_language();
		}

		$post = null;

		if ( PLL_FSE_Tools::is_template_post_type( sanitize_key( $_GET['postType'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$post = PLL_FSE_Tools::get_template_post();
		} elseif ( ! empty( $_GET['postId'] ) && is_numeric( sanitize_key( $_GET['postId'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$post = get_post( (int) $_GET['postId'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		if ( empty( $post ) ) {
			return $this->model->get_default_language();
		}

		$post_lang = $this->model->post->get_language( $post->ID );

		if ( empty( $post_lang ) ) {
			return $this->model->get_default_language();
		}

		return $post_lang;
	}
}
