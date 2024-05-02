<?php
/**
 * Loads the Sensei LMS Home module.
 *
 * @package senseilms-home
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/includes/class-sensei-pro-home.php';
require_once __DIR__ . '/includes/tasks/class-sensei-pro-home-task-create-first-product.php';
require_once __DIR__ . '/includes/tasks/class-task-sell-course-with-woocommerce.php';

use Sensei_Pro_Home\Sensei_Pro_Home;
use Sensei_Pro_Home\Tasks\Task_Sell_Course_With_WooCommerce;

// Load the plugin after all the other plugins have loaded.
add_action( 'plugins_loaded', [ Sensei_Pro_Home::instance(), 'init' ] );

/**
 * Function that updates the task "Sell your Course with WooCommerce" in the task list returned by Sensei Home.
 *
 * @deprecated 1.23.0 Use Sensei_Pro_Home::add_sell_course_task instead.
 *
 * @param array $tasks The original tasks array to modify.
 *
 * @return array The tasks array with the "sell-course-with-woocommerce" included.
 */
function sensei_pro_add_sell_course_with_woocommerce_task( $tasks ) {
	_deprecated_function( __FUNCTION__, '1.23.0', 'Task_Sell_Course_With_WooCommerce::add_sell_course_task' );

	return Task_Sell_Course_With_WooCommerce::instance()->add_sell_course_task( $tasks );
}
