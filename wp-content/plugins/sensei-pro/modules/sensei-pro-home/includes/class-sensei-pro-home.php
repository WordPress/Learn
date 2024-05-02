<?php
/**
 * Sensei Pro Home.
 *
 * @package senseilms-home
 * @since   1.23.0
 */

namespace Sensei_Pro_Home;

use Sensei_Pro_Home\Tasks\Sensei_Pro_Home_Task_Create_First_Product;
use Sensei_Pro_Home\Tasks\Task_Sell_Course_With_WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sensei Pro Home main class.
 */
class Sensei_Pro_Home {
	/**
	 * Class instance.
	 *
	 * @var Sensei_Pro_Home
	 */
	private static $instance;

	/**
	 * Retrieve the Sensei_Pro_Home instance.
	 */
	public static function instance(): Sensei_Pro_Home {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Class constructor.
	 */
	private function __construct() {
		// Silence is golden.
	}

	/**
	 * Initializes the class and adds all filters and actions.
	 */
	public static function init() {
		$instance = self::instance();

		if ( ! defined( 'SENSEI_PRO_PLUGIN_DIR_URL' ) ) {
			// We do not want to run this on Sensei Interactive Blocks.
			return;
		}

		Task_Sell_Course_With_WooCommerce::instance()->init();
		Sensei_Pro_Home_Task_Create_First_Product::instance()->init();
	}
}
