<?php
/**
 * File containing the class \Sensei_WC_Paid_Courses\Admin\Products.
 *
 * @package sensei-wc-paid-courses
 * @since   2.4.0
 */

namespace Sensei_WC_Paid_Courses\Admin;

use WC_Product_Factory;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for admin functionality related to WooCommerce products.
 *
 * @class Sensei_WC_Paid_Courses\Admin\Products
 */
class Products {
	/**
	 * Instance of class.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Products constructor. Prevents other instances from being created outside of `Products::instance()`.
	 */
	private function __construct() {}

	/**
	 * Initializes the class and adds all filters and actions related to WP admin.
	 */
	public function init() {
		add_action( 'wp_ajax_sensei_wc_paid_courses_get_courses', [ $this, 'ajax_get_courses' ] );
		add_action( 'woocommerce_process_product_meta', [ $this, 'save_product_meta' ] );
		add_action( 'woocommerce_save_product_variation', [ $this, 'save_product_meta' ], 10, 2 );
		add_action( 'woocommerce_product_options_general_product_data', [ $this, 'html_add_course_field_simple' ] );
		add_action( 'woocommerce_product_after_variable_attributes', [ $this, 'html_add_course_field_variation' ], 10, 3 );
	}

	/**
	 * Save the course product meta for simple and subscription products.
	 *
	 * @access private
	 *
	 * @param int        $post_id The post ID for the product.
	 * @param string|int $index   The variation index on the form. For other products, defaults to 'simple'.
	 */
	public function save_product_meta( $post_id, $index = 'simple' ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is checked in WooCommerce.
		$new_product_type = 'simple' === $index && ! empty( $_POST['product-type'] ) ? sanitize_title( wp_unslash( $_POST['product-type'] ) ) : null;
		$product_type     = $new_product_type ?? WC_Product_Factory::get_product_type( $post_id );

		$simple_product_types = [ 'simple', 'subscription' ];
		$is_simple            = in_array( $product_type, $simple_product_types, true ) && 'simple' === $index;
		$is_variable          = 'variation' === $product_type && is_numeric( $index );

		if ( ! $is_simple && ! $is_variable ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is checked in WooCommerce.
		$course_ids         = isset( $_POST['sensei_course_ids'][ $index ] ) ? array_unique( array_map( 'absint', $_POST['sensei_course_ids'][ $index ] ) ) : [];
		$current_course_ids = $this->get_product_course_ids( $post_id );

		$remove_course_ids = array_diff( $current_course_ids, $course_ids );
		$add_course_ids    = array_diff( $course_ids, $current_course_ids );

		if ( empty( $remove_course_ids ) && empty( $add_course_ids ) ) {
			return;
		}

		foreach ( $add_course_ids as $course_id ) {
			if ( 'course' !== get_post_type( $course_id ) ) {
				continue;
			}

			add_post_meta( $course_id, \Sensei_WC_Paid_Courses\Courses::META_COURSE_PRODUCT, $post_id );
		}

		foreach ( $remove_course_ids as $course_id ) {
			delete_post_meta( $course_id, \Sensei_WC_Paid_Courses\Courses::META_COURSE_PRODUCT, $post_id );
		}

		if ( 'variation' === $product_type ) {
			$product_parent_id = wc_get_product( $post_id )->get_parent_id();
			$product_type      = WC_Product_Factory::get_product_type( $product_parent_id ) . '_' . $product_type;
		}

		sensei_log_event(
			'product_course_update',
			[
				'product_id'     => $post_id,
				'product_type'   => $product_type,
				'course_count'   => count( $this->get_product_course_ids( $post_id ) ),
				'product_status' => get_post_status( $post_id ),
			]
		);
	}

	/**
	 * Adds the course field to the WooCommerce product editor.
	 *
	 * @access private
	 */
	public function html_add_course_field_simple() {
		global $post;

		$post_id            = $post->ID;
		$product_type       = 'simple';
		$field_index        = 'simple';
		$current_course_ids = $this->get_product_course_ids( $post_id );

		include __DIR__ . '/views/html-product-course-field.php';
	}

	/**
	 * Adds the course field to the WooCommerce product editor.
	 *
	 * @param int      $loop           Position in the loop.
	 * @param array    $variation_data Variation data.
	 * @param \WP_Post $variation      Post data.
	 *
	 * @access private
	 */
	public function html_add_course_field_variation( $loop, $variation_data, $variation ) {
		$post_id            = $variation ? $variation->ID : null;
		$product_type       = 'variation';
		$field_index        = $loop;
		$current_course_ids = $this->get_product_course_ids( $post_id );

		include __DIR__ . '/views/html-product-course-field.php';
	}

	/**
	 * Get the courses from an AJAX request.
	 *
	 * @access private
	 */
	public function ajax_get_courses() {
		check_ajax_referer( 'search-products', 'security' );

		if ( ! current_user_can( 'edit_products' ) || ! current_user_can( 'edit_courses' ) ) {
			wp_die( '', '', [ 'response' => 403 ] );
		}

		if ( empty( $term ) && isset( $_GET['term'] ) ) {
			$term = (string) sanitize_text_field( wp_unslash( $_GET['term'] ) );
		}

		if ( empty( $term ) ) {
			wp_die();
		}

		if ( ! empty( $_GET['limit'] ) ) {
			$limit = absint( $_GET['limit'] );
		} else {
			$limit = 30;
		}

		$include_ids = ! empty( $_GET['include'] ) ? array_map( 'absint', (array) wp_unslash( $_GET['include'] ) ) : [];
		$exclude_ids = ! empty( $_GET['exclude'] ) ? array_map( 'absint', (array) wp_unslash( $_GET['exclude'] ) ) : [];

		$args = [
			'post_type'      => 'course',
			's'              => $term,
			'posts_per_page' => $limit,
		];

		if ( ! empty( $include_ids ) ) {
			$args['post__in'] = $include_ids;
		}

		if ( ! empty( $exclude_ids ) ) {
			$args['post__not_in'] = $exclude_ids;
		}

		$courses = get_posts( $args );
		$results = [];
		foreach ( $courses as $course ) {
			$results[ $course->ID ] = rawurldecode( wp_strip_all_tags( $course->post_title ) );
		}

		wp_send_json( $results );
	}

	/**
	 * Get the course IDs for a product.
	 *
	 * @param int $post_id The product post ID.
	 *
	 * @return int[] The course post IDs.
	 */
	private function get_product_course_ids( $post_id ) {
		if ( empty( $post_id ) ) {
			return [];
		}

		$args           = \Sensei_WC_Paid_Courses\Courses::get_product_courses_query_args( (int) $post_id );
		$args['fields'] = 'ids';
		$course_ids     = get_posts( $args );

		return $course_ids;
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
}
