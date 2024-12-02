<?php
/**
 * @package Polylang-Pro
 */

/**
 * A class to handle cross domain data and single sign on for multiple domains
 *
 * @since 2.0
 */
class PLL_Xdata_Domain extends PLL_Xdata_Base {
	/**
	 * @var PLL_Choose_Lang_Domain
	 */
	public $choose_lang;

	/**
	 * Constructor
	 *
	 * @since 2.0
	 *
	 * @param object $polylang Polylang object.
	 */
	public function __construct( &$polylang ) {
		parent::__construct( $polylang );

		$this->choose_lang = &$polylang->choose_lang;

		add_action( 'pll_init', array( $this, 'pll_init' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );

		if ( empty( $_POST['wp_customize'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			add_action( 'wp_head', array( $this, 'check_request' ), 0 ); // As soon as possible
			add_action( 'wp_ajax_pll_xdata_check', array( $this, 'xdata_check' ) );
			add_action( 'wp_ajax_nopriv_pll_xdata_check', array( $this, 'xdata_check' ) );
		}

		// Post preview.
		if ( isset( $_GET['preview_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			add_action( 'init', array( $this, 'check_request' ), 5 ); // Before _show_post_preview
		}
	}

	/**
	 * Set language cookie
	 *
	 * @since 2.0
	 *
	 * @param string $lang Language code.
	 * @return void
	 */
	protected function maybe_set_cookie( $lang ) {
		PLL_Cookie::set( $lang, array( 'samesite' => 'None' ) );
	}

	/**
	 * Allow to use the correct domain for preview links
	 * as we force them to be on the main domain when not using the Xdata module
	 *
	 * @since 2.0
	 *
	 * @param object $polylang Polylang object.
	 * @return void
	 */
	public function pll_init( $polylang ) {
		remove_filter( 'preview_post_link', array( $polylang->filters_links, 'preview_post_link' ), 20 );
	}

	/**
	 * Set the cookie to main domain when visiting the admin
	 * This is necessary to avoid 404 when previewing a post on non default domain
	 * Make sure the cookie is set on admin and not on ajax request to avoid infinite redirect loop
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	public function admin_init() {
		if ( ! PLL() instanceof PLL_Frontend ) {
			$this->maybe_set_cookie( $this->links_model->get_language_from_url() );
		}
	}

	/**
	 * Outputs the link to the javascript request to main domain
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	public function check_request() {
		$args = array(
			'action'   => 'pll_xdata_check',
			'redirect' => urlencode( pll_get_requested_url() ),
			'nonce'    => $this->create_nonce( 'xdata_check' ),
			'nologin'  => is_user_logged_in(),
		);

		printf(
			'<script%1$s src="%2$s" async></script>',
			current_theme_supports( 'html5', 'script' ) ? '' : ' type="text/javascript"',
			esc_url( $this->ajax_url( $this->options['default_lang'], $args ) )
		);
	}

	/**
	 * Response to pll_xdata_check request
	 * Redirects to the preferred language home page at first visit
	 * Sets the language cookie on main domain
	 * Initiates a cross domain data transfer if the language has just changed
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	public function xdata_check() {
		if ( ! isset( $_GET['nonce'], $_GET['redirect'] ) || ! $this->verify_nonce( sanitize_key( $_GET['nonce'] ), 'xdata_check' ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			wp_die();
		}

		$redirect    = esc_url_raw( wp_unslash( $_GET['redirect'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		$lang        = $this->links_model->get_language_from_url( $redirect );
		$lang_object = $this->model->get_language( $lang );

		// Redirects to the preferred language home page at first visit.
		if ( ! empty( $this->options['browser'] ) && ! isset( $_COOKIE[ PLL_COOKIE ] ) && ! empty( $lang_object ) && trailingslashit( $redirect ) === $lang_object->get_home_url() ) {
			/** This filter is documented in /polylang/frontend/choose-lang.php */
			$preflang = apply_filters( 'pll_preferred_language', $this->choose_lang->get_preferred_browser_language() );

			if ( ! $this->model->get_language( $preflang ) ) {
				$preflang = $this->options['default_lang']; // Redirect to default language if there is no match.
			}

			if ( $preflang !== $lang ) {
				$preflang_object = $this->model->get_language( $preflang );
				$home_page_url   = ! empty( $preflang_object ) ? $preflang_object->get_home_url() : '';
				/** This filter is documented in /polylang/frontend/choose-lang.php */
				$home_page_url = apply_filters( 'pll_redirect_home', $home_page_url );
				if ( $home_page_url ) {
					header( 'Content-Type: application/javascript' );
					printf( 'window.location.replace("%s");', esc_url_raw( $home_page_url ) );
					wp_die();
				}
			}
		}

		$this->maybe_set_cookie( $lang ); // Sets the language cookie on main domain.

		header( 'Content-Type: application/javascript' );
		echo $this->maybe_get_xdomain_js( $redirect, $lang ); // phpcs:ignore WordPress.Security.EscapeOutput
		wp_die();
	}

	/**
	 * Saves info on the current user session and redirects to the main domain
	 *
	 * @since 2.0
	 *
	 * @param string           $redirect_to           The redirect destination URL.
	 * @param string           $requested_redirect_to The requested redirect destination URL passed as a parameter.
	 * @param WP_User|WP_Error $user                  WP_User object if login was successful, WP_Error object otherwise.
	 * @return string The modified redirect destination URL.
	 */
	public function login_redirect( $redirect_to, $requested_redirect_to, $user ) {
		$main_host      = wp_parse_url( $this->links_model->remove_language_from_link( $redirect_to ), PHP_URL_HOST );
		$requested_host = wp_parse_url( pll_get_requested_url(), PHP_URL_HOST );

		if ( $main_host !== $requested_host && ! is_wp_error( $user ) ) {
			$redirect_to = $this->_login_redirect( $redirect_to, $requested_redirect_to, $user );
		}

		return $redirect_to;
	}
}
