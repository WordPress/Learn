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
		$post = $this->get_current_post();

		if ( empty( $post ) ) {
			return $this->model->get_default_language();
		}

		$post_lang = $this->model->post->get_language( $post->ID );

		if ( empty( $post_lang ) ) {
			return $this->model->get_default_language();
		}

		return $post_lang;
	}

	/**
	 * Returns the current post using the Site Editor URLs.
	 * As of WordPress 6.8, the Site Editor URLs are:
	 * - `wp-admin/site-editor.php?p=/page`
	 * - `wp-admin/site-editor.php?p=/pattern`
	 * - `wp-admin/site-editor.php?p=/page/123`
	 * - `wp-admin/site-editor.php?p=/wp_block/123`
	 * - `wp-admin/site-editor.php?p=/wp_navigation/123`
	 * - `wp-admin/site-editor.php?p=/wp_template/themeSlug//templateSlug`
	 * - `wp-admin/site-editor.php?p=/wp_template_part/themeSlug//templateSlug`
	 * - ...
	 *
	 * @since 3.7.6
	 *
	 * @return WP_Post|null The current post or null if not found.
	 */
	private function get_current_post(): ?WP_Post {
		if ( ! isset( $_GET['p'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return $this->get_current_post_legacy();
		}

		/** @var string $p */
		$p = $_GET['p']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( preg_match( '/^\/(page|wp_block|wp_navigation)\/(\d+)$/', $p, $matches ) && ! empty( $matches[2] ) ) {
			/*
			 * `$matches[2]` is an numeric string representing the post ID.
			 */
			return get_post( (int) $matches[2] );
		} elseif ( preg_match( '/^\/(wp_template|wp_template_part)\/(.+)$/', $p, $matches ) && ! empty( $matches[2] ) ) {
			/*
			 * `$matches[1]` is a string representing either `wp_template` or `wp_template_part`, `$matches[2]` is the template ID (themeSlug//templateSlug).
			 */
			return PLL_FSE_Tools::get_post_from_template_id( $matches[2], $matches[1] );
		}

		return null;
	}

	/**
	 * Returns the current post using legacy Site Editor URLs.
	 * Backward compatibility with WordPress < 6.8.
	 * Before WordPress 6.8, the Site Editor URLs were:
	 * - `wp-admin/site-editor.php?postId=123&postType=page`
	 * - `wp-admin/site-editor.php?postId=themeSlug//templateSlug&postType=wp_template`
	 * - `wp-admin/site-editor.php?postId=themeSlug//templateSlug&postType=wp_template_part`
	 * - ...
	 *
	 * @since 3.7.6
	 *
	 * @return WP_Post|null The current post or null if not found.
	 */
	private function get_current_post_legacy(): ?WP_Post {
		if ( isset( $_GET['postType'] ) && PLL_FSE_Tools::is_template_post_type( sanitize_key( $_GET['postType'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return PLL_FSE_Tools::get_template_post();
		} elseif ( isset( $_GET['postId'] ) && is_numeric( $_GET['postId'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return get_post( (int) $_GET['postId'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		return null;
	}
}
