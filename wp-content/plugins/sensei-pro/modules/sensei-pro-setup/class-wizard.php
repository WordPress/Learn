<?php
/**
 * File containing the class \Sensei_WC_Paid_Courses\Admin\Setup_Wizard.
 *
 * @package sensei-pro
 * @since   1.0.0
 */

namespace Sensei_Pro_Setup;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use SenseiLMS_Licensing\License_Manager;

/**
 * Encapsulates the plugin setup. This includes:
 * - Activating the license key for the plugin.
 * - Editing/debugging/updating the license key for the plugin.
 * - Installing the Sensei Core if it is not already installed.
 * - Possibly initiating the Sensei Core setup wizard.
 */
class Wizard {
	/**
	 * Sensei core plugin slug.
	 */
	const SENSEI_PLUGIN_SLUG = 'sensei-lms';

	/**
	 * Instances of this class.
	 *
	 * @var self[]
	 */
	private static $instances = [];

	/**
	 * Plugin context in which the Wizard operates.
	 *
	 * @var Setup_Context
	 */
	private $setup_context;

	/**
	 * Class constructor. Prevents other instances from being created outside of `Class::instance()`.
	 */
	private function __construct() {
	}

	/**
	 * Fetches an instance of the class that is bound to a context.
	 *
	 * @param Setup_Context $setup_context The plugin context under which the setup wizard operates.
	 * @return self
	 */
	public static function instance( Setup_Context $setup_context ) {
		if ( ! isset( self::$instances[ $setup_context->get_plugin_slug() ] ) ) {
			$instance                = new self();
			$instance->setup_context = $setup_context;
			self::$instances[ $setup_context->get_plugin_slug() ] = $instance;
		}
		return self::$instances[ $setup_context->get_plugin_slug() ];
	}

	/**
	 * Initializes the class and adds all filters and actions related to WP admin.
	 */
	public function init() {
		register_activation_hook( $this->setup_context->get_plugin_main_filename(), [ $this, 'set_activation_redirect' ] );
		add_action( 'init', [ $this, 'handle_activation_redirect' ], 10, 1 );
		add_action( 'init', [ $this, 'initiate_setup' ], 20, 1 );
	}

	/**
	 * Set the activation redirect transient when plugin is activated.
	 */
	public function set_activation_redirect() {
		if ( ! get_transient( self::get_licensing_page_slug( $this->setup_context->get_plugin_slug() ) . '_activation_redirect' ) ) {
			set_transient( self::get_licensing_page_slug( $this->setup_context->get_plugin_slug() ) . '_activation_redirect', 1 );
		}
	}

	/**
	 * Redirects if the activation transient is set.
	 */
	public function handle_activation_redirect() {
		if ( get_transient( self::get_licensing_page_slug( $this->setup_context->get_plugin_slug() ) . '_activation_redirect' ) ) {
			delete_transient( self::get_licensing_page_slug( $this->setup_context->get_plugin_slug() ) . '_activation_redirect' );
			$this->redirect_to_setup_wizard();
		}
	}

	/**
	 * Fires up all the necessary hooks.
	 */
	public function initiate_setup() {
		add_action( 'rest_api_init', [ $this, 'register_rest_api' ] );

		$plugin_file = self::get_plugin_file( $this->setup_context->get_plugin_slug() );
		add_filter( "plugin_action_links_{$plugin_file}", [ $this, 'add_license_action' ], 10, 4 );

		if ( ! is_admin() || ! $this->is_sensei_pro() ) {
			return;
		}

		add_action( 'admin_menu', [ $this, 'register_wizard_page' ] );

		if ( $this->is_sensei_licensing_page() ) {
			self::check_sensei_home_redirected();
			add_action( 'admin_print_scripts', [ $this, 'enqueue_licensing_page_scripts' ] );
			add_action( 'admin_body_class', [ $this, 'filter_body_class' ] );
		} elseif ( $this->is_sensei_extensions_page() ) {
			add_action( 'admin_print_scripts', [ $this, 'enqueue_extensions_page_scripts' ] );
		}

		if ( $this->is_sensei_licensing_page() || $this->is_sensei_extensions_page() ) {
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles' ] );
		}
	}

	/**
	 * Tells if the current page is the plugin setup page.
	 *
	 * @return bool
	 */
	public function is_sensei_licensing_page(): bool {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Arguments used for comparison.
		if ( isset( $_GET['page'] ) && self::get_licensing_page_slug( $this->setup_context->get_plugin_slug() ) === $_GET['page'] ) {
			return true;
		}

		return false;
	}

	/**
	 * Tells if the current page is the Sensei extensions page on older versions of Sensei.
	 *
	 * @return bool
	 */
	public function is_sensei_extensions_page(): bool {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Arguments used for comparison.
		if ( isset( $_GET['page'] ) && 'sensei-extensions' === $_GET['page'] ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if the current context is for Sensei Pro.
	 *
	 * @return bool
	 */
	private function is_sensei_pro(): bool {
		return 'sensei-pro' === $this->setup_context->get_plugin_slug();
	}

	/**
	 * Returns the plugin setup url.
	 *
	 * @param string $plugin_slug
	 */
	public static function get_setup_url( string $plugin_slug ): string {
		$setup_url = add_query_arg(
			[
				'page' => self::get_licensing_page_slug( $plugin_slug ),
			],
			admin_url( 'admin.php' )
		);

		/**
		 * Filter the setup URL to allow for customizing the setup page.
		 *
		 * @hook sensei_pro_wizard_setup_url
		 * @since 1.8.0
		 *
		 * @param {string} $setup_url The setup URL.
		 *
		 * @return {string} The setup URL.
		 */
		return apply_filters( 'sensei_pro_wizard_setup_url', $setup_url );
	}

	/**
	 * Redirect to setup wizard.
	 */
	protected function redirect_to_setup_wizard() {
		wp_safe_redirect( self::get_setup_url( $this->setup_context->get_plugin_slug() ) );
		exit;
	}

	/**
	 * Remember if we already added the sensei menu page.
	 *
	 * @var bool
	 */
	private static $added_menu_page = false;

	/**
	 * Register the Setup Wizard admin page via a hidden submenu.
	 */
	public function register_wizard_page() {
		if ( ! self::is_sensei_activated() && ! self::$added_menu_page ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Not using for remote file.
			$sensei_svg = file_get_contents( $this->setup_context->get_plugin_dir() . '/assets/dist/sensei-pro-setup/images/sensei.svg' );
			add_menu_page(
				'Sensei',
				'Sensei',
				'install_plugin',
				'sensei-pro',
				'',
				// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Not obfuscating any sensitive data.
				'data:image/svg+xml;base64,' . base64_encode( $sensei_svg ),
				'50'
			);
			self::$added_menu_page = true;
		}

		$locales = $this->setup_context->get_locales();
		add_submenu_page(
			'sensei-pro',
			$locales['page_title'],
			$locales['menu_title'],
			'administrator',
			self::get_licensing_page_slug( $this->setup_context->get_plugin_slug() ),
			[ $this, 'render_wizard_page' ]
		);
	}

	/**
	 * Render app container for setup wizard.
	 */
	public function render_wizard_page() {
		?>
		<div id="sensei-pro-setup__container" class="sensei-pro-setup__container">
		</div>
		<?php
	}

	/**
	 * Retrieves the sensei plugin installation file path relative
	 * to plugins directory.
	 *
	 * @param string $plugin_slug The plugin slug.
	 * @return string
	 */
	public static function get_plugin_file( $plugin_slug ): string {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		$plugins = get_plugins();
		foreach ( $plugins as $plugin_file => $plugin_data ) {
			if ( "$plugin_slug.php" === basename( $plugin_file ) ) {
				return $plugin_file;
			}
		}
		return '';
	}

	/**
	 * Tells if the sensei core is installed or not.
	 *
	 * @return bool
	 */
	public static function is_sensei_installed(): bool {
		return (bool) self::get_plugin_file( self::SENSEI_PLUGIN_SLUG );
	}


	/**
	 * Tells if the sensei core plugin is activated or not.
	 *
	 * @return bool
	 */
	public static function is_sensei_activated(): bool {
		return class_exists( 'Sensei_Main' );
	}

	/**
	 * Tells if Sensei Home is available or not.
	 *
	 * @since 1.10.0
	 * @return bool
	 */
	public static function is_sensei_home_available(): bool {
		if ( class_exists( 'Sensei_Interactive_Blocks_Sensei_Home\Sensei_Home' ) ) {
			// If this is Sensei Blocks, we can consider Sensei Home as available.
			return true;
		}
		return self::is_sensei_activated() &&
				version_compare( Sensei()->version, '4.8.0' ) >= 0;
	}

	/**
	 * Returns the sensei core plugin activation url.
	 *
	 * @return string
	 */
	public static function get_sensei_activate_url() {
		$plugin_file = self::get_plugin_file( self::SENSEI_PLUGIN_SLUG );
		if ( ! $plugin_file ) {
			return '';
		}
		$plugin_file_url = rawurlencode( $plugin_file );
		$nonce           = wp_create_nonce( "activate-plugin_{$plugin_file}" );
		$url             = "/wp-admin/plugins.php?action=activate&plugin={$plugin_file_url}&_wpnonce={$nonce}";
		return $url;
	}

	/**
	 * Adds the "Activate License" action link to Sensei Pro
	 * plugin in the WP plugins page. Doesn't do anything if the license
	 * is already installed. Replaced by `add_license_action`.
	 *
	 * @deprecated 1.17.0
	 * @param array $actions The plugin actions in the plugins page.
	 * @return array Return back the actions array with additional Activate License link.
	 */
	public function add_activate_license_action( array $actions ) {
		_deprecated_function( __METHOD__, '1.17.0', 'add_license_action' );
		$actions = $this->add_license_action( $actions );
		unset( $actions['manage_license'] );
		return $actions;
	}

	/**
	 * Adds License actions (Activate or Manage) in the WP plugins page.
	 *
	 * @param array $actions The plugin actions in the plugins page.
	 * @return array Return back the actions array with additional "Activate License" or "Manage License" link.
	 */
	public function add_license_action( array $actions ) {
		$license_status = License_Manager::get_license_status( $this->setup_context->get_plugin_slug() );
		$url            = self::get_setup_url( $this->setup_context->get_plugin_slug() );
		if ( $license_status['is_valid'] ) {
			$title                     = __( 'Manage License', 'sensei-pro' );
			$url                       = add_query_arg( 'manage_license', '1', $url );
			$actions['manage_license'] = "<a href='{$url}' aria-label='{$title}'>{$title}</a>";
		} else {
			$title                       = __( 'Activate License', 'sensei-pro' );
			$actions['activate_license'] = "<a href='{$url}' aria-label='{$title}' style='color: red;'>{$title}</a>";
		}
		return $actions;
	}

	/**
	 * Enqueue Sensei Pro Setup script.
	 *
	 * @param string $script_name The name of the script that needs to be enqueued.
	 */
	public function enqueue_script( string $script_name ) {
		wp_enqueue_script(
			self::get_licensing_page_slug( $this->setup_context->get_plugin_slug() ),
			"{$this->setup_context->get_setup_assets_url()}/{$script_name}.js",
			[ 'wp-components', 'wp-api-fetch' ],
			$this->setup_context->get_plugin_version(),
			true
		);
	}


	/**
	 * Enqueue licensing page scripts.
	 */
	public function enqueue_licensing_page_scripts() {
		$this->enqueue_script( 'licensing-page' );
		$this->enqueue_initial_state_script();
	}

	/**
	 * Enqueue extensions page scripts.
	 */
	public function enqueue_extensions_page_scripts() {
		$this->enqueue_script( 'extensions-page' );
		$this->enqueue_initial_state_script();
	}

	/**
	 * Redirect the user to Sensei Home if Sensei is already activated.
	 */
	protected static function check_sensei_home_redirected() {
		if ( self::is_sensei_home_available() ) {
			// If Sensei Home is available, redirect to Sensei Home.
			wp_safe_redirect( admin_url( 'admin.php?page=sensei' ) );
		}
	}

	/**
	 * Enqueue initial state script.
	 */
	public function enqueue_initial_state_script() {
		$license_data = License_Manager::get_license_status( $this->setup_context->get_plugin_slug() );
		$inline_data  = [
			'senseiInstalled'   => self::is_sensei_installed(),
			'senseiActivated'   => self::is_sensei_activated(),
			'senseiActivateUrl' => self::get_sensei_activate_url(),
			'licenseKey'        => $license_data['license_key'],
			'licenseDomain'     => $license_data['domain'],
			'licenseActivated'  => (bool) $license_data['is_valid'],
			'locales'           => $this->setup_context->get_locales(),
			'requires_sensei'   => $this->setup_context->get_requires_sensei(),
			'hasSenseiHome'     => self::is_sensei_home_available(),
			'plugin_slug'       => $this->setup_context->get_plugin_slug(),
		];
		wp_add_inline_script(
			self::get_licensing_page_slug( $this->setup_context->get_plugin_slug() ),
			'window.senseiProSetup=' . wp_json_encode(
				$inline_data
			) . ';',
			'before'
		);
	}

	/**
	 * Enqueue CSS.
	 */
	public function enqueue_styles() {
		wp_enqueue_style(
			self::get_licensing_page_slug( $this->setup_context->get_plugin_slug() ),
			"{$this->setup_context->get_setup_assets_url()}/style.css",
			[ 'wp-components' ],
			$this->setup_context->get_plugin_version()
		);
	}

	/**
	 * Add global classes for Setup Wizard page.
	 *
	 * @param string $classes Current class list.
	 *
	 * @access private
	 * @return string Extended class list.
	 */
	public function filter_body_class( $classes ) {
		$classes .= ' sensei-pro-setup ';
		return $classes;
	}

	/**
	 * Registers the REST API.
	 */
	public function register_rest_api() {
		Rest_Api::instance()->register_routes();
	}

	/**
	 * Given the plugin slug, returns the licensing page slug for that plugin.
	 *
	 * @param string $plugin_slug
	 */
	public static function get_licensing_page_slug( string $plugin_slug ): string {
		return "licensing-page-{$plugin_slug}";
	}
}
