<?php
/**
 * File containing the class \Sensei_Pro\Shared_Module.
 *
 * @package sensei-pro
 * @since   1.0.1
 */

namespace Sensei_Pro;

/**
 * Shared Module class.
 *
 * @since 1.0.1
 */
class Shared_Module {
	const MODULE_NAME = 'shared-module';

	/**
	 * Instance of class.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Module directory.
	 *
	 * @var string
	 */
	private $module_dir;

	/**
	 * Vendor directory.
	 *
	 * @var string
	 */
	private $vendor_path;

	/**
	 * Script and stylesheet loading.
	 *
	 * @var \Sensei_Assets|\Sensei_Pro_Interactive_Blocks\Assets_Provider
	 */
	private $asset_provider;

	/**
	 * Shared_Module constructor.
	 */
	private function __construct() {
		$this->module_dir = dirname( __DIR__ );
	}

	/**
	 * Fetches an instance of the class.
	 *
	 * @return self
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initializes the class and adds all filters and actions.
	 *
	 * @param \Sensei_Assets|\Sensei_Pro_Interactive_Blocks\Assets_Provider $asset_provider The class which loads the assets.
	 * @param string                                                        $vendor_path    The path to the vendor directory.
	 */
	public static function init( $asset_provider, string $vendor_path ) {
		$instance                 = self::instance();
		$instance->asset_provider = $asset_provider;
		$instance->vendor_path    = $vendor_path;
		$instance->include_dependencies();
		Language_Packs::instance()->init( [ 'sensei-pro' => SENSEI_PRO_VERSION ] );

		add_action( 'admin_enqueue_scripts', [ $instance, 'register_assets' ], 5 );
		add_action( 'wp_enqueue_scripts', [ $instance, 'register_assets' ], 5 );
	}

	/**
	 * Include dependencies.
	 *
	 * @access private
	 */
	private function include_dependencies() {
		// Action scheduler.
		if (
			! class_exists( 'ActionScheduler_Versions' )
			|| ! function_exists( 'as_unschedule_all_actions' )
			|| ! function_exists( 'as_next_scheduled_action' )
			|| ! function_exists( 'as_schedule_single_action' )
		) {
			$as_plugin_file = $this->vendor_path . 'woocommerce/action-scheduler/action-scheduler.php';
			require_once $as_plugin_file;
			require_once $this->vendor_path . 'woocommerce/action-scheduler/classes/abstracts/ActionScheduler.php';
			\ActionScheduler::init( $as_plugin_file );
		}
		// Background jobs.
		include_once $this->module_dir . '/includes/background-jobs/class-job.php';
		include_once $this->module_dir . '/includes/background-jobs/class-cron-job.php';
		include_once $this->module_dir . '/includes/background-jobs/class-completable-job.php';
		include_once $this->module_dir . '/includes/background-jobs/class-scheduler.php';
		include_once $this->module_dir . '/includes/class-language-packs.php';
	}

	/**
	 * Register shared assets.
	 *
	 * @access private
	 */
	public function register_assets() {
		$this->asset_provider->register( 'sensei-pro-shared-module-sensei-fontawesome', 'sensei-fontawesome.css' );
	}
}
