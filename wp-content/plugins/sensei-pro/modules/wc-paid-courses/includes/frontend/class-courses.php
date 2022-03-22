<?php
/**
 * File containing the class \Sensei_WC_Paid_Courses\Frontend\Courses.
 *
 * @package sensei-wc-paid-courses
 * @since   1.0.0
 */

namespace Sensei_WC_Paid_Courses\Frontend;

use Sensei_WC;
use Sensei_WC_Subscriptions;
use Sensei_WC_Paid_Courses\Sensei_WC_Paid_Courses;
use Sensei_WC_Paid_Courses\Course_Enrolment_Providers;
use Sensei_WC_Paid_Courses\Course_Enrolment_Providers\WooCommerce_Memberships;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for admin functionality related to courses.
 *
 * @class Sensei_WC_Paid_Courses\Admin\Courses
 */
final class Courses {
	/**
	 * Instance of class.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Courses constructor. Prevents other instances from being created outside of `Courses::instance()`.
	 */
	private function __construct() {}

	/**
	 * Initializes the class and adds all filters and actions related to WP admin.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		add_action( 'sensei_output_course_enrolment_actions', [ $this, 'maybe_override_course_enrolment_actions' ], 9 );

		// By default, show the course price after meta input.
		$this->add_action_output_course_price();

		// Hide the course price on My Courses listing.
		add_action( 'sensei_my_courses_before', [ $this, 'remove_action_output_course_price' ] );
		add_action( 'sensei_my_courses_after', [ $this, 'add_action_output_course_price' ] );

		if ( Course_Enrolment_Providers::use_legacy_enrolment_method() ) {
			// Filter out subscription courses from course query if user has cancelled their subscription.
			add_filter( 'sensei_setup_course_query_should_filter_course_by_status', [ $this, 'should_filter_subscription_course' ], 10, 3 );
		}

		// Frontend styling.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );
	}

	/**
	 * Enqueues styles on the frontend.
	 *
	 * @since 1.1.0
	 */
	public function enqueue_styles() {
		Sensei_WC_Paid_Courses::instance()->assets->enqueue(
			'sensei-wcpc-courses',
			'css/sensei-wcpc-courses.css'
		);
	}

	/**
	 * Show a purchase button for courses attached to a single product,
	 * or show different pricing options for courses attached to multiple products.
	 *
	 * @access private
	 *
	 * @since 1.0.0
	 */
	public function maybe_override_course_enrolment_actions() {
		global $post;

		$course_id = $post->ID;

		if ( ! Sensei_WC::is_course_purchasable( $course_id, true ) ) {
			return;
		}

		if (
			class_exists( 'Sensei_WC_Paid_Courses\Course_Enrolment_Providers\WooCommerce_Memberships' )
			&& WooCommerce_Memberships::does_user_have_membership( get_current_user_id(), $course_id )
		) {
			return;
		}

		// Do not display the default enrolment actions.
		remove_action( 'sensei_output_course_enrolment_actions', [ 'Sensei_Course', 'output_course_enrolment_actions' ] );

		if ( ! is_user_logged_in() ) {
			$message = sprintf(
				// translators: Placeholder is a link to log in.
				__( 'Or %1$s to access your purchased courses', 'sensei-pro' ),
				'<a href="' . sensei_user_login_url() . '">' . __( 'log in', 'sensei-pro' ) . '</a>'
			);

			Sensei()->notices->add_notice( $message, 'info' );
		}

		$purchasable_products = Sensei_WC::get_purchasable_products( $course_id );

		if ( 0 === count( $purchasable_products ) ) {
			return;
		}

		// Show purchase button if there is only one purchasable product.
		if ( 1 === count( $purchasable_products ) ) {
			Sensei_WC::the_add_to_cart_button_html( $course_id, $purchasable_products[0] );

			return;
		}

		// Show different pricing options when there are multiple purchasable products.
		Sensei_WC_Paid_Courses::get_template(
			'single-course/products.php',
			[
				'course_id' => $course_id,
				'products'  => $purchasable_products,
			]
		);
	}

	/**
	 * Outputs the course price.
	 *
	 * @param int $course_id Course ID.
	 */
	public function output_course_price( $course_id ) {
		$product_ids = Sensei_WC::get_course_product_ids( $course_id );

		if ( ! $product_ids ) {
			return;
		}

		echo '<ul class="course-products">';

		foreach ( $product_ids as $product_id ) {
			$product = Sensei_WC::get_product_object( $product_id );

			if (
				! ( $product instanceof \WC_Product ) ||
				! $product->is_purchasable() ||
				! $product->is_in_stock() ||
				Sensei_WC::is_product_in_cart( $product_id )
			) {
				continue;
			}

			echo '<li class="course-product">' .
				'<span class="course-product-title">' .
					wp_kses_post( $product->get_name() ) .
				'</span>' .
				' - ' .
				'<span class="course-product-price price">' .
					wp_kses_post( $product->get_price_html() ) .
				'</span>' .
			'</li>';
		}

		echo '</ul>';
	}

	/**
	 * Removes the action to output course price.
	 *
	 * @since 1.0.0
	 */
	public function remove_action_output_course_price() {
		remove_action( 'sensei_course_content_inside_before', [ $this, 'output_course_price' ], 20 );
	}

	/**
	 * Adds the action to output course price.
	 *
	 * @since 1.0.0
	 */
	public function add_action_output_course_price() {
		add_action( 'sensei_course_content_inside_before', [ $this, 'output_course_price' ], 20 );
	}

	/**
	 * Determine whether the course should be filtered from the course list
	 * based on whether the subscription is still valid.
	 *
	 * @since 1.0.0
	 * @deprecated 2.0.0
	 *
	 * @param bool       $should_filter Whether the course should be filtered out.
	 * @param WP_Comment $course_status The current course status record.
	 * @param int        $user_id       The user ID.
	 * @return bool
	 */
	public function should_filter_subscription_course( $should_filter, $course_status, $user_id ) {
		_deprecated_function( __METHOD__, '2.0.0' );

		// Determine whether the user has cancelled their subscription.
		$cancelled = Sensei_WC_Subscriptions::has_user_bought_subscription_but_cancelled(
			$course_status->comment_post_ID,
			$user_id
		);

		// If cancelled, filter this course from the query.
		if ( $cancelled ) {
			return true;
		}

		return $should_filter;
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
