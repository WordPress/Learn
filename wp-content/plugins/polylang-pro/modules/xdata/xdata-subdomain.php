<?php
/**
 * @package Polylang-Pro
 */

/**
 * A class to handle cross domain data and single sign on for subdomains
 *
 * @since 2.0
 */
class PLL_Xdata_Subdomain extends PLL_Xdata_Base {
	/**
	 * Current language (defined by its slug).
	 *
	 * @var string
	 */
	private $curlang;

	/**
	 * The list of ajax endpoints for each subdomain.
	 *
	 * @var string[]
	 */
	private $ajax_urls;

	/**
	 * Constructor
	 *
	 * @since 2.0
	 *
	 * @param object $polylang Polylang object.
	 */
	public function __construct( &$polylang ) {
		parent::__construct( $polylang );

		// Inactive languages will be lost later. Need to save current language and ajax urls now.
		$this->curlang = $this->links_model->get_language_from_url();
		if ( empty( $this->curlang ) ) {
			$this->curlang = $this->options['default_lang'];
		}

		foreach ( $this->model->get_languages_list() as $lang ) {
			$this->ajax_urls[ $lang->slug ] = $this->links_model->switch_language_in_link( admin_url( 'admin-ajax.php' ), $this->model->get_language( $lang ) );
		}

		if ( empty( $_POST['wp_customize'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			add_action( 'wp_head', array( $this, 'maybe_language_switched' ) );
		}

		// Post preview
		if ( isset( $_GET['preview_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			add_action( 'init', array( $this, 'maybe_language_switched' ), 5 ); // Before _show_post_preview.
		}

		add_filter( 'site_url', array( $this, 'site_url' ) );

		add_filter( 'wp_redirect', array( $this, 'fix_cookie_in_redirect' ), 999 );
	}

	/**
	 * Builds an ajax request url to the domain defined by a language
	 * Overrides the parent method to take care of inactive languages
	 *
	 * @since 2.0
	 *
	 * @param string   $lang The language slug.
	 * @param string[] $args Existing url parameters.
	 * @return string The ajax url.
	 */
	public function ajax_url( $lang, $args ) {
		return add_query_arg( $args, $this->ajax_urls[ $lang ] );
	}

	/**
	 * Outputs the javascript to create the cross domain request when the language just switched
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	public function maybe_language_switched() {
		$js = $this->maybe_get_xdomain_js( pll_get_requested_url(), $this->curlang );

		if ( empty( $js ) ) {
			return;
		}

		$type_attr = current_theme_supports( 'html5', 'script' ) ? '' : ' type="text/javascript"';

		echo "<script{$type_attr} async>\n{$js}\n</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput
	}

	/**
	 * Saves info on the current user session and redirects to the main domain
	 *
	 * @since 2.0
	 *
	 * @param string           $redirect_to           The redirect destination URL.
	 * @param string           $requested_redirect_to The requested redirect destination URL passed as a parameter.
	 * @param WP_User|WP_Error $user                  WP_User object if login was successful, WP_Error object otherwise.
	 * @return string
	 */
	public function login_redirect( $redirect_to, $requested_redirect_to, $user ) {
		if ( ! $this->options['hide_default'] && ! is_wp_error( $user ) ) {
			$redirect_to = $this->_login_redirect( $redirect_to, $requested_redirect_to, $user );
		}
		return $redirect_to;
	}

	/**
	 * Returns the correct site url ( mainly to get the correct login form )
	 *
	 * @since 2.0
	 *
	 * @param string $url The main site url.
	 * @return string
	 */
	public function site_url( $url ) {
		$lang = $this->links_model->get_language_from_url();
		$lang = $this->model->get_language( $lang );
		return $this->links_model->add_language_to_link( $url, $lang );
	}

	/**
	 * Prevents changing the language cookie when the page is redirected
	 * to avoid failed login when previewing a page
	 *
	 * @since 2.1
	 *
	 * @param string $location Redirect url.
	 * @return string
	 */
	public function fix_cookie_in_redirect( $location ) {
		$cookie = PLL_Cookie::get();

		if ( ! empty( $location ) && $cookie && ! empty( $this->curlang ) && $cookie !== $this->curlang ) {
			PLL_Cookie::set( $cookie, array( 'domain' => wp_parse_url( $this->links_model->home, PHP_URL_HOST ) ) );
		}

		return $location;
	}
}
