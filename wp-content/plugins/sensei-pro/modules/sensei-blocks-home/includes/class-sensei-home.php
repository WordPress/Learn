<?php
/**
 * File containing the class Sensei_Interactive_Blocks_Sensei_Home\Sensei_Home.
 *
 * @package sensei-blocks-home
 * @since   1.8.0
 */

namespace Sensei_Interactive_Blocks_Sensei_Home;

use Sensei_Home_News_Provider;
use Sensei_Home_Notices;
use Sensei_Home_Notices_Provider;
use Sensei_Home_Remote_Data_API;
use Sensei_Interactive_Blocks_Sensei_Home\Providers\Help;
use Sensei_Pro_Interactive_Blocks\Assets_Provider;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Handles functionality related to Sensei Home.
 *
 * @since 1.8.0
 */
class Sensei_Home {
	const MODULE_NAME = 'sensei-blocks-home';
	const SCREEN_ID   = 'toplevel_page_sensei';
	const MENU_CAP    = 'administrator';

	private const SHARED_CLASSES_FILE_MAP = [
		'Sensei_Home_News_Provider'    => __DIR__ . '/shared/class-sensei-home-news-provider.php',
		'Sensei_Home_Remote_Data_API'  => __DIR__ . '/shared/class-sensei-home-remote-data-api.php',
		'Sensei_Home_Notices'          => __DIR__ . '/shared/notices/class-sensei-home-notices.php',
		'Sensei_Home_Notices_Provider' => __DIR__ . '/shared/notices/class-sensei-home-notices-provider.php',
	];

	/**
	 * Sensei Home Help provider.
	 *
	 * @var Help
	 */
	private $help_provider;

	/**
	 * Sensei Home News provider.
	 *
	 * @var Sensei_Home_News_Provider
	 */
	private $news_provider;

	/**
	 * Sensei Notices provider.
	 *
	 * @var Sensei_Home_Notices_Provider
	 */
	private $notices_provider;

	/**
	 * Script and stylesheet loading.
	 *
	 * @var Assets_Provider
	 */
	private $assets_provider;

	/**
	 * Constructor for class.
	 *
	 * @param Assets_Provider $assets_provider The assets provider.
	 */
	public function __construct( Assets_Provider $assets_provider ) {
		$this->assets_provider = $assets_provider;
	}

	/**
	 * Initializes the class and adds all filters and actions related to Sensei Home.
	 *
	 * @since 1.8.0
	 */
	public function init() {
		add_action( 'init', [ $this, 'setup' ] );
		add_action( 'admin_menu', [ $this, 'add_admin_menu_item' ] );
		add_filter( 'sensei_pro_wizard_setup_url', [ $this, 'get_home_url' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		$this->force_display_submenu();
	}

	/**
	 * Initialize the providers and add the REST API endpoint. This runs late so that the classes are available.
	 *
	 * @internal
	 */
	public function setup() {
		$this->load_files();

		$remote_data_api = new Sensei_Home_Remote_Data_API( 'sensei-interactive-blocks', SENSEI_IB_VERSION );
		( new Sensei_Home_Notices( $remote_data_api, self::SCREEN_ID ) )->init();

		$this->help_provider    = new Help();
		$this->news_provider    = new Sensei_Home_News_Provider( $remote_data_api );
		$this->notices_provider = new Sensei_Home_Notices_Provider( null, self::SCREEN_ID );

		$rest_api = new REST_API(
			$this->help_provider,
			$this->news_provider,
			$this->notices_provider
		);

		$rest_api->init();
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since  1.8.0
	 */
	public function enqueue_scripts() {
		$screen = get_current_screen();

		if ( ! $screen ) {
			return;
		}

		if ( self::SCREEN_ID === $screen->id ) {
			$this->assets_provider->register( 'sensei-home', 'sensei-home.js', [], true );
			$this->assets_provider->register( 'sensei-home-style', 'sensei-home-style.css' );
			$this->assets_provider->enqueue_script( 'sensei-home' );
			$this->assets_provider->enqueue_style( 'sensei-home-style' );
			$this->assets_provider->preload_data( [ '/sensei-internal/v1/home' ] );
			$this->localize_script();
		}
	}

	/**
	 * Localize Home script.
	 *
	 * @since 1.8.0
	 */
	private function localize_script() {
		$data                        = [];
		$data['dismissNoticesNonce'] = null;
		wp_localize_script(
			'sensei-home',
			'sensei_home',
			$data
		);
	}

	/**
	 * Load the files needed for Sensei Home.
	 *
	 * @internal
	 */
	private function load_files() {
		// When Sensei LMS isn't active, we need to load the necessary classes from this module.
		foreach ( self::SHARED_CLASSES_FILE_MAP as $class_name => $file_path ) {
			if ( class_exists( $class_name ) ) {
				continue;
			}

			require_once $file_path;
		}
	}

	/**
	 * Get notices count.
	 *
	 * @return int Notices count.
	 */
	private function get_notices_count() {
		return $this->notices_provider->get_badge_count();
	}

	/**
	 * Adds the menu item for the Home page.
	 *
	 * @since  1.8.0
	 *
	 * @access private
	 */
	public function add_admin_menu_item() {
		$notices_html  = '';
		$notices_count = $this->get_notices_count();

		if ( $notices_count > 0 ) {
			$notices_html = ' <span class="awaiting-mod">' . (int) $notices_count . '</span>';
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Not using for remote file.
		$sensei_svg = file_get_contents( SENSEI_IB_PLUGIN_DIR_PATH . '/assets/dist/sensei-pro-setup/images/sensei.svg' );
		add_menu_page(
			'Sensei LMS',
			'Sensei LMS',
			self::MENU_CAP,
			'sensei',
			'',
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Not obfuscating any sensitive data.
			'data:image/svg+xml;base64,' . base64_encode( $sensei_svg ),
			'50'
		);

		add_submenu_page(
			'sensei',
			esc_html__( 'Sensei Home', 'sensei-pro' ),
			esc_html__( 'Blocks', 'sensei-pro' ) . $notices_html,
			self::MENU_CAP,
			'sensei',
			[ $this, 'render' ]
		);
	}

	/**
	 * It forces WordPress to display the submenu even having one single item.
	 */
	private function force_display_submenu() {
		add_action(
			'admin_enqueue_scripts',
			function() {
				$custom_css = '#' . self::SCREEN_ID . ' .wp-submenu li:last-child { display: none; }';
				wp_add_inline_style( 'admin-menu', $custom_css );
			}
		);

		add_action(
			'admin_menu',
			function() {
				add_submenu_page(
					'sensei',
					'',
					'',
					self::MENU_CAP,
					'',
					'',
					99999999
				);
			}
		);
	}

	/**
	 * Get the URL for the Sensei Home page.
	 *
	 * @internal
	 * @return string
	 */
	public function get_home_url(): string {
		return admin_url( 'admin.php?page=sensei' );
	}

	/**
	 * Renders Sensei Home.
	 *
	 * @since  1.8.0
	 * @access private
	 */
	public function render() {
		require __DIR__ . '/views/html-admin-page-home.php';
	}
}
