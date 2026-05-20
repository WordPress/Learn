<?php
/**
 * @package Polylang-Pro
 */

use WP_Syntex\Polylang\REST\API;
use WP_Syntex\Polylang\Model\Languages;
use WP_Syntex\Polylang\Capabilities\Capabilities;
use WP_Syntex\Polylang_Pro\Modules\Active_Languages\Languages_Proxy;

/**
 * Manages the ability to enable or disable a language.
 *
 * @since 1.9
 */
class PLL_Active_Languages {
	/**
	 * Stores the plugin options.
	 *
	 * @var \WP_Syntex\Polylang\Options\Options
	 */
	public $options;

	/**
	 * @var PLL_Model
	 */
	public $model;

	/**
	 * @var PLL_Language|false|null
	 */
	private $curlang;

	/**
	 * @var PLL_Language|false
	 */
	private $inactive_curlang = false;

	/**
	 * @var Languages_Proxy
	 */
	private $language_proxy;

	/**
	 * Constructor.
	 *
	 * @since 1.9
	 *
	 * @param PLL_Base $polylang Polylang object.
	 */
	public function __construct( PLL_Base &$polylang ) {
		$this->options        = $polylang->options;
		$this->model          = &$polylang->model;
		$this->curlang        = &$polylang->curlang;
		$this->language_proxy = new Languages_Proxy();

		// Settings.
		if ( $polylang instanceof PLL_Settings && ( empty( $_GET['page'] ) || 'mlang' === $_GET['page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			// CSS.
			add_filter( 'pll_languages_row_classes', array( $this, 'row_classes' ), 10, 2 );
			add_action( 'admin_print_styles', array( $this, 'print_css' ) );

			// Disable/Enable languages.
			add_filter( 'pll_languages_row_actions', array( $this, 'row_actions' ), 10, 2 );
			add_action( 'mlang_action_enable', array( $this, 'enable' ) );
			add_action( 'mlang_action_disable', array( $this, 'disable' ) );

			// Disallow setting an inactive language as the default one.
			add_filter( 'pll_default_lang_row_action', array( $this, 'remove_default_lang_action' ), 10, 2 );
			add_action( 'toplevel_page_mlang', array( $this, 'prevent_default_lang_assignation' ), 5 ); // Before `PLL_Settings::languages_page()`.
			add_action( 'language_page_mlang', array( $this, 'prevent_default_lang_assignation' ), 5 ); // Before `PLL_Settings::languages_page()`.
		}

		if ( $polylang instanceof PLL_Admin_Base ) {
			// No need to wait any hook.
			$this->model->languages->register_proxy( $this->language_proxy, 'automatic' );
			add_action( 'set_current_user', array( $this->model->languages, 'clean_local_cache' ), -10000 );
		} elseif ( $polylang instanceof PLL_REST_Request ) {
			/*
			 * When using an application password, the current user is set late, "right" before `rest_pre_dispatch`.
			 * See the beginning of `WP_REST_Server::serve_request()`:
			 * https://github.com/WordPress/WordPress/blob/6.9/wp-includes/rest-api/class-wp-rest-server.php#L289-L301
			 */
			add_filter( 'rest_pre_dispatch', array( $this, 'maybe_filter_languages_in_rest' ), -10000 );
		} else {
			/*
			 * Wait until the current language is defined, this makes it work when the language is defined by the content.
			 */
			add_filter( 'pll_get_current_language', array( $this, 'maybe_filter_languages_in_frontend' ) );
		}
	}

	/**
	 * Adds a `inactive` CSS class to inactive language rows.
	 *
	 * @since 1.9
	 *
	 * @param string[]     $classes  CSS classes applied to a row in the languages list table.
	 * @param PLL_Language $language The language.
	 * @return string[] Modified list of classes.
	 */
	public function row_classes( $classes, $language ) {
		return empty( $language->active ) ? array( 'inactive' ) : array();
	}

	/**
	 * Styles the inactive language rows.
	 *
	 * @since 1.9
	 *
	 * @return void
	 */
	public function print_css(): void {
		?>
		<style type="text/css">
			#the-list .name {
				padding-left: 14px;
			}
			#the-list .inactive .name {
				padding-left: 10px;
				border-left: 4px solid #d54e21;
			}
			#the-list .inactive {
				background-color: #fef7f1;
			}
		</style>
		<?php
	}

	/**
	 * Adds Disable/Enable links to row actions in the languages list table.
	 *
	 * @since 1.9
	 *
	 * @param string[]     $actions  The list of the HTML markup of row actions.
	 * @param PLL_Language $language The language.
	 * @return string[] Modified list of row actions.
	 */
	public function row_actions( $actions, $language ) {
		if ( $language->is_default ) {
			return $actions;
		}

		$active_action = empty( $language->active ) ?
			array(
				'enable' => sprintf(
					'<a title="%s" href="%s">%s</a>',
					esc_attr__( 'Activate this language', 'polylang-pro' ),
					wp_nonce_url( '?page=mlang&amp;pll_action=enable&amp;noheader=true&amp;lang=' . $language->term_id, 'enable-lang' ),
					esc_html__( 'Activate', 'polylang-pro' )
				),
			) :
			array(
				'disable' => sprintf(
					'<a title="%s" href="%s">%s</a>',
					esc_attr__( 'Deactivate this language', 'polylang-pro' ),
					wp_nonce_url( '?page=mlang&amp;pll_action=disable&amp;noheader=true&amp;lang=' . $language->term_id, 'disable-lang' ),
					esc_html__( 'Deactivate', 'polylang-pro' )
				),
			);

		return array_merge( $active_action, $actions );
	}

	/**
	 * Enables or disables a language.
	 *
	 * @since 1.9
	 *
	 * @param int  $lang_id The language term ID.
	 * @param bool $enable  True to enable, false to disable.
	 * @return void
	 */
	public function _enable( int $lang_id, bool $enable ): void {
		$language = get_term( $lang_id, 'language' );

		if ( ! $language instanceof WP_Term ) {
			return;
		}

		if ( $language->slug === $this->model->options['default_lang'] ) {
			return;
		}

		$description = maybe_unserialize( $language->description );

		if ( ! is_array( $description ) ) {
			return;
		}

		$description['active'] = $enable;

		/** @var string */
		$description = maybe_serialize( $description );

		wp_update_term( $lang_id, 'language', array( 'description' => $description ) );
		$this->model->languages->clean_local_cache();
	}

	/**
	 * Enables a language.
	 *
	 * @since 1.9
	 *
	 * @return void
	 *
	 * @phpstan-return never
	 */
	public function enable(): void {
		check_admin_referer( 'enable-lang' );
		if ( isset( $_GET['lang'] ) && is_numeric( $_GET['lang'] ) ) {
			$this->_enable( (int) $_GET['lang'], true );
		}
		PLL_Settings::redirect();
	}

	/**
	 * Disables a language.
	 *
	 * @since 1.9
	 *
	 * @return void
	 *
	 * @phpstan-return never
	 */
	public function disable(): void {
		check_admin_referer( 'disable-lang' );
		if ( isset( $_GET['lang'] ) && is_numeric( $_GET['lang'] ) ) {
			$this->_enable( (int) $_GET['lang'], false );
		}
		PLL_Settings::redirect();
	}

	/**
	 * Removes the default lang action for disabled languages.
	 *
	 * @since 1.9
	 *
	 * @param string       $action   HTML markup of the action to define the default language.
	 * @param PLL_Language $language The Language.
	 * @return string Modified row action.
	 */
	public function remove_default_lang_action( $action, $language ) {
		if ( empty( $language->active ) ) {
			return '';
		}

		return $action;
	}

	/**
	 * Prevents an inactive language to be set as the default one.
	 * This is done by preventing `PLL_Settings::handle_actions()` to be called on the `default-lang` action.
	 *
	 * @since 3.8
	 *
	 * @return void
	 *
	 * @phpstan-return void|never
	 */
	public function prevent_default_lang_assignation(): void {
		if ( ! isset( $_REQUEST['pll_action'], $_REQUEST['_wpnonce'], $_GET['lang'] ) ) {
			return;
		}

		if ( 'default-lang' !== $_REQUEST['pll_action'] || ! is_numeric( $_GET['lang'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'default-lang' ) ) {
			return;
		}

		$lang = $this->model->get_language( (int) $_GET['lang'] );

		if ( empty( $lang ) || ! empty( $lang->active ) ) {
			return;
		}

		/*
		 * Prevent `PLL_Settings::handle_actions()`.
		 * This should not happen because the users should not get access to a URL allowing this.
		 * See `row_actions()`.
		 */
		wp_die( 0 );
	}

	/**
	 * If the current user is not allowed to see inactive languages, this method:
	 * - registers the language proxy that removes the inactive languages,
	 * - clears the languages cache.
	 * If the current language is inactive, this method:
	 * - replaces it with the default language.
	 *
	 * @since 3.8
	 *
	 * @param mixed $result Response to replace the requested version with. Can be anything
	 *                      a normal endpoint can return, or null to not hijack the request.
	 * @return mixed
	 */
	public function maybe_filter_languages_in_rest( $result = null ) {
		if ( current_user_can( Languages_Proxy::CAPABILITY ) ) {
			// No need to do anything.
			return $result;
		}

		$this->model->languages->register_proxy( $this->language_proxy, 'automatic' );
		$this->model->languages->clean_local_cache();
		add_action( 'set_current_user', array( $this->model->languages, 'clean_local_cache' ), -10000 );

		if ( empty( $this->curlang ) || ! empty( $this->curlang->active ) ) {
			// The current language is not inactive.
			return $result;
		}

		// Set the current language to the default one, so we don't have an inactive current language.
		$this->curlang = $this->model->languages->get_default();

		return $result;
	}

	/**
	 * If the current user is not allowed to see inactive languages, this method:
	 * - registers the language proxy that removes the inactive languages,
	 * - clears the languages cache
	 * If the given language is inactive, this method:
	 * - replaces it with the default language,
	 * - adds a hook that triggers a 404 status and disables the sitemap.
	 *
	 * @since 3.8
	 *
	 * @param PLL_Language|false $curlang Instance of the current language.
	 * @return PLL_Language|false
	 */
	public function maybe_filter_languages_in_frontend( $curlang ) {
		if ( current_user_can( Languages_Proxy::CAPABILITY ) ) {
			// Ne need to do anything.
			return $curlang;
		}

		$this->model->languages->register_proxy( $this->language_proxy, 'automatic' );
		$this->model->languages->clean_local_cache();
		add_action( 'set_current_user', array( $this->model->languages, 'clean_local_cache' ), -10000 );

		if ( empty( $curlang ) || ! empty( $curlang->active ) ) {
			// The current language is not inactive.
			return $curlang;
		}

		$this->inactive_curlang = $curlang;
		add_action( 'wp', array( $this, 'maybe_set_404' ) );

		// Fall back to the default language, so we don't have an inactive current language.
		return $this->model->languages->get_default();
	}

	/**
	 * Sets an error 404 if the requested language is not active.
	 * Also disables the sitemap.
	 *
	 * @since 1.9
	 *
	 * @global WP_Query $wp_query WordPress Query object.
	 *
	 * @return void
	 */
	public function maybe_set_404(): void {
		global $wp_query;

		if ( empty( $this->inactive_curlang ) ) {
			// Should not happen.
			return;
		}

		$wp_query->set_404();
		add_filter( 'wp_sitemaps_enabled', '__return_false' );

		/**
		 * Fires when a visitor attempts to access to an inactive language.
		 *
		 * @since 2.7
		 *
		 * @param string       $slug    Requested language code.
		 * @param PLL_Language $curlang Requested language object.
		 */
		do_action( 'pll_inactive_language_requested', $this->inactive_curlang->slug, $this->inactive_curlang );
	}
}
