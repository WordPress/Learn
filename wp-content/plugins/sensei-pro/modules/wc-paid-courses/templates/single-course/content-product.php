<?php
/**
 * The template for displaying a single product on the single course page.
 *
 * Override this template by copying it to yourtheme/sensei-wc-paid-courses/single-course/content-product.php.
 *
 * @author  Automattic
 * @package Sensei WooCommerce Paid Courses\Templates
 * @version 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fires before outputting the opening div that contains the product.
 *
 * @since 1.1.0
 *
 * @param int $course_id      Course ID.
 * @param WC_Product $product Product.
 */
do_action( 'sensei_wc_paid_courses_single_course_before_product', $course_id, $product );
?>

<div class="course-product">

	<?php
	/**
	 * Fires before outputting the product markup.
	 *
	 * @since 1.1.0
	 *
	 * @param int $course_id  Course ID.
	 * @param WC_Product $product Product.
	 */
	do_action( 'sensei_wc_paid_courses_single_course_before_products_loop_item', $course_id, $product );
	?>

	<h3 class="course-product-title">
		<?php echo wp_kses_post( $product->get_name() ); ?>
	</h3>

	<p class="course-product-description">
		<?php echo $product->is_type( 'variation' ) ? wp_kses_post( $product->get_description() ) : wp_kses_post( $product->get_short_description() ); ?>
	</p>

	<span class="course-product-price price">
		<?php echo wp_kses_post( $product->get_price_html() ); ?>
	</span>

	<?php
	Sensei_WC::the_add_to_cart_button_html( $course_id, $product, __( 'Add to Cart', 'sensei-pro' ) );

	/**
	 * Fires after outputting the product markup.
	 *
	 * @since 1.1.0
	 *
	 * @param int $course_id  Course ID.
	 * @param WC_Product $product Product.
	 */
	do_action( 'sensei_wc_paid_courses_single_course_after_products_loop_item', $course_id, $product );
	?>

</div>

<?php
/**
 * Fires after outputting the closing div that contains the product.
 *
 * @since 1.1.0
 *
 * @param int $course_id  Course ID.
 * @param WC_Product $product Product.
 */
do_action( 'sensei_wc_paid_courses_single_course_after_product', $course_id, $product );
