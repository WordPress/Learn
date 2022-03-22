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
	 * Setup wizard page slug.
	 */
	const SETUP_WIZARD_PAGE_SLUG = 'sensei_pro_setup_wizard';

	/**
	 * Instance of class.
	 *
	 * @var self
	 */
	private static $instance;

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
	 * Fetches an instance of the class.
	 *
	 * @param Setup_Context $setup_context The plugin context under which the setup wizard operates.
	 * @return self
	 */
	public static function instance( Setup_Context $setup_context ) {
		if ( ! self::$instance ) {
			self::$instance                = new self();
			self::$instance->setup_context = $setup_context;
		}
		return self::$instance;
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
		if ( ! get_transient( self::SETUP_WIZARD_PAGE_SLUG . '_activation_redirect' ) ) {
			set_transient( self::SETUP_WIZARD_PAGE_SLUG . '_activation_redirect', 1 );
		}
	}

	/**
	 * Redirects if the activation transient is set.
	 */
	public function handle_activation_redirect() {
		if ( get_transient( self::SETUP_WIZARD_PAGE_SLUG . '_activation_redirect' ) ) {
			delete_transient( self::SETUP_WIZARD_PAGE_SLUG . '_activation_redirect' );
			$this->redirect_to_setup_wizard();
		}
	}

	/**
	 * Fires up all the necessary hooks.
	 */
	public function initiate_setup() {
		$plugin_file = self::get_plugin_file( $this->setup_context->plugin_slug );
		add_filter( "plugin_action_links_{$plugin_file}", [ $this, 'add_activate_license_action' ], 10, 4 );
		add_action( 'admin_menu', [ $this, 'register_wizard_page' ] );
		add_action( 'rest_api_init', [ $this, 'register_rest_api' ] );

		if ( ! is_admin() ) {
			return;
		}

		if ( $this->is_sensei_licensing_page() ) {
			add_action( 'admin_print_scripts', [ $this, 'enqueue_licensing_page_scripts' ] );
			add_action( 'admin_body_class', [ $this, 'filter_body_class' ] );
		} elseif ( $this->is_sensei_extensions_page() ) {
			add_action( 'admin_print_scripts', [ $this, 'enqueue_extensions_page_scripts' ] );
		}

		add_action( 'admin_print_styles', [ $this, 'enqueue_styles' ] );
	}

	/**
	 * Tells if the current page is the plugin setup page.
	 *
	 * @return bool
	 */
	public function is_sensei_licensing_page(): bool {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Arguments used for comparison.
		if ( isset( $_GET['page'] ) && self::SETUP_WIZARD_PAGE_SLUG === $_GET['page'] ) {
			return true;
		}
		return false;
	}

	/**
	 * Tells if the current page is the Sensei extensions page.
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
	 * Returns the plugin setup url.
	 */
	public static function get_setup_url(): string {
		return admin_url( 'admin.php?page=' . self::SETUP_WIZARD_PAGE_SLUG );
	}

	/**
	 * Redirect to setup wizard.
	 */
	protected function redirect_to_setup_wizard() {
		wp_safe_redirect( self::get_setup_url() );
		exit;
	}

	/**
	 * Register the Setup Wizard admin page via a hidden submenu.
	 */
	public function register_wizard_page() {
		if ( ! self::is_sensei_activated() ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Not using for remote file.
			$sensei_svg = file_get_contents( dirname( __FILE__ ) . '/assets/images/sensei.svg' );
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
		}

		add_submenu_page(
			'sensei-pro',
			$this->setup_context->locales['page_title'],
			$this->setup_context->locales['menu_title'],
			'administrator',
			self::SETUP_WIZARD_PAGE_SLUG,
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
	 * Adds the "Activate License" action link to Senseei Pro
	 * plugin in the WP plugins page. Doesn't do anything if the license
	 * is already installed.
	 *
	 * @param array $actions The plugin actions in the plugins page.
	 * @return array Return back the actions array with additional Activate License link.
	 */
	public function add_activate_license_action( array $actions ) {
		$license_status = License_Manager::get_license_status( $this->setup_context->plugin_slug );
		if ( ! $license_status['is_valid'] ) {
			$title                       = __( 'Activate License', 'sensei-pro' );
			$url                         = self::get_setup_url();
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
			self::SETUP_WIZARD_PAGE_SLUG,
			"{$this->setup_context->plugin_url}/assets/dist/sensei-pro-setup/{$script_name}.js",
			[ 'wp-components', 'wp-api-fetch' ],
			$this->setup_context->plugin_version,
			true
		);
	}


	/**
	 * Enqueue licensing page scripts.
	 */
	public function enqueue_licensing_page_scripts() {
		$this->enqueue_script( 'licensing_page' );
		$this->enqueue_initial_state_script();
	}

	/**
	 * Enqueue extensions page scripts.
	 */
	public function enqueue_extensions_page_scripts() {
		$this->enqueue_script( 'extensions_page' );
		$this->enqueue_initial_state_script();
	}

	/**
	 * Enqueue initial state script.
	 */
	public function enqueue_initial_state_script() {
		$license_data = License_Manager::get_license_status( $this->setup_context->plugin_slug );
		$inline_data  = [
			'senseiInstalled'   => self::is_sensei_installed(),
			'senseiActivated'   => self::is_sensei_activated(),
			'senseiActivateUrl' => self::get_sensei_activate_url(),
			'licenseKey'        => $license_data['license_key'],
			'licenseDomain'     => $license_data['domain'],
			'licenseActivated'  => (bool) $license_data['is_valid'],
		];
		wp_add_inline_script(
			self::SETUP_WIZARD_PAGE_SLUG,
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
			self::SETUP_WIZARD_PAGE_SLUG,
			"{$this->setup_context->plugin_url}/assets/dist/sensei-pro-setup/style.css",
			[ 'wp-components' ],
			$this->setup_context->plugin_version
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
		$controller = new Rest_Api( $this->setup_context );
		$controller->register_routes();
	}
}
