<?php
/**
 * Loads the SenseiLMS Home module.
 *
 * @package senseilms-home
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'SENSEI_PRO_PLUGIN_DIR_URL' ) ) {
	// We do not want to run this filter on Sensei Interactive Blocks, or when WordPress is not loaded.
	exit;
}

/**
 * Function that adds the task "Sell your Course with WooCommerce" to the task list returned by Sensei Home.
 *
 * @param array $tasks The original tasks array to modify.
 *
 * @return array The tasks array with the "sell-course-with-woocommerce" included.
 */
function sensei_pro_add_sell_course_with_woocommerce_task( $tasks ) {
	$courses_with_products = get_posts(
		[
			'post_type'        => 'course',
			'meta_key'         => '_course_woocommerce_product',
			'meta_compare_key' => 'EXISTS',
			'post_status'      => [ 'publish', 'draft' ],
			'posts_per_page'   => 1,
		]
	);
	$task_id               = 'sell-course-with-woocommerce';
	$tasks[ $task_id ]     = [
		'id'       => $task_id,
		'title'    => __( 'Sell your course with WooCommerce', 'sensei-pro' ),
		'priority' => 400,
		'url'      => 'https://senseilms.com/documentation/getting-started-with-woocommerce-paid-courses/#link',
		'done'     => ! empty( $courses_with_products ),
	];
	return $tasks;
}

add_filter( 'sensei_home_tasks', 'sensei_pro_add_sell_course_with_woocommerce_task' );
