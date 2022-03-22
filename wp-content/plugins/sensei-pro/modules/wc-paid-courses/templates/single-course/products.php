<?php
/**
 * The template for displaying products on the single course page.
 *
 * Override this template by copying it to yourtheme/sensei-wc-paid-courses/single-course/products.php.
 *
 * @author  Automattic
 * @package Sensei WooCommerce Paid Courses\Templates
 * @version 1.1.0
 */

use Sensei_WC_Paid_Courses\Sensei_WC_Paid_Courses;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fires before outputting the opening div that contains the products.
 *
 * @since 1.1.0
 *
 * @param int $course_id Course ID.
 */
do_action( 'sensei_wc_paid_courses_single_course_before_products', $course_id );
?>

<div class="course-products">

<?php
/**
 * Fires before the products loop.
 *
 * @since 1.1.0
 *
 * @param int $course_id Course ID.
 */
do_action( 'sensei_wc_paid_courses_single_course_before_products_loop', $course_id );

foreach ( $products as $product ) {
	// Load the template part and pass in the necessary arguments.
	Sensei_WC_Paid_Courses::get_template_part(
		'single-course/content',
		'product',
		[
			'course_id' => $course_id,
			'product'   => $product,
		]
	);
}

/**
 * Fires after the products loop.
 *
 * @since 1.1.0
 *
 * @param int $course_id Course ID.
 */
do_action( 'sensei_wc_paid_courses_single_course_after_products_loop', $course_id );
?>

</div>

<?php
/**
 * Fires after outputting the closing div that contains the products.
 *
 * @since 1.1.0
 *
 * @param int $course_id Course ID.
 */
do_action( 'sensei_wc_paid_courses_single_course_after_products', $course_id );
