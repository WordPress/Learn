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
	 * Script and stylesheet loading.
	 *
	 * @var \Sensei_Assets
	 */
	public $assets;

	/**
	 * Shared_Module constructor.
	 */
	private function __construct() {
		$this->module_dir = dirname( __DIR__ );
		$this->assets     = \Sensei_Pro\Modules\assets_loader( self::MODULE_NAME );
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
	 */
	public static function init() {
		$instance = self::instance();
		$instance->include_dependencies();
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
			$as_plugin_file = dirname( __FILE__, 4 ) . '/vendor/woocommerce/action-scheduler/action-scheduler.php';
			require_once $as_plugin_file;
			require_once dirname( __FILE__, 4 ) . '/vendor/woocommerce/action-scheduler/classes/abstracts/ActionScheduler.php';
			\ActionScheduler::init( $as_plugin_file );
		}
		// Background jobs.
		include_once $this->module_dir . '/includes/background-jobs/class-job.php';
		include_once $this->module_dir . '/includes/background-jobs/class-cron-job.php';
		include_once $this->module_dir . '/includes/background-jobs/class-completable-job.php';
		include_once $this->module_dir . '/includes/background-jobs/class-scheduler.php';
	}
}
