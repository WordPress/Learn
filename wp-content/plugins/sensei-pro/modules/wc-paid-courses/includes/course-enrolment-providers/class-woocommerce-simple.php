<?php
/**
 * File containing the class \Sensei_WC_Paid_Courses\Course_Enrolment_Providers\WooCommerce_Simple.
 *
 * @package sensei-wc-paid-courses
 * @since   2.0.0
 */

namespace Sensei_WC_Paid_Courses\Course_Enrolment_Providers;

use Sensei_WC_Paid_Courses\Course_Enrolment_Providers;
use Sensei_WC_Paid_Courses\Courses;

/**
 * Course enrolment provider for courses where enrolment is controlled by a simple or variable product.
 *
 * @since 2.0.0
 */
class WooCommerce_Simple
	implements \Sensei_Course_Enrolment_Provider_Interface, \Sensei_Course_Enrolment_Provider_Debug_Interface {
	/**
	 * Singleton instance.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Track the course IDs and customer ID when deleting an order.
	 *
	 * @var array[]
	 */
	private $delete_order_data = [];

	/**
	 * Provides singleton instance.
	 *
	 * @return self
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Class constructor. Private so it can only be initialized internally.
	 */
	private function __construct() {}

	/**
	 * Adds the actions related to simple products.
	 */
	public function init() {
		// Setting the priority to `5` so that this more lightweight check runs before subscriptions and memberships.
		\add_filter( 'sensei_is_legacy_enrolled', [ $this, 'maybe_allow_legacy_manual_enrolment' ], 5, 3 );

		// Order lifecycle hooks to listen to.
		\add_action( 'woocommerce_order_status_changed', [ $this, 'maybe_trigger_order_change' ], 10, 4 );
		\add_action( 'trashed_post', [ $this, 'trash_post' ] );
		\add_action( 'untrashed_post', [ $this, 'untrash_post' ] );

		// Adds support for deleting directly or for disabled `EMPTY_TRASH_DAYS`.
		\add_action( 'before_delete_post', [ $this, 'before_delete_order' ], 1 );
		\add_action( 'deleted_post', [ $this, 'after_delete_order' ] );
	}

	/**
	 * Gets the unique identifier of this enrolment provider.
	 *
	 * @return int
	 */
	public function get_id() {
		return 'wc-simple';
	}

	/**
	 * Gets the descriptive name of the provider.
	 *
	 * @return string
	 */
	public function get_name() {
		return \esc_html__( 'WooCommerce Simple Products', 'sensei-pro' );
	}

	/**
	 * Check if this course enrolment provider manages enrolment for a particular course.
	 *
	 * @param int $course_id Course post ID.
	 *
	 * @return bool
	 */
	public function handles_enrolment( $course_id ) {
		$products = $this->get_course_simple_products( $course_id );

		return ! empty( $products );
	}

	/**
	 * Check if this course enrolment provider is enroling a user to a course.
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course post ID.
	 *
	 * @return bool  `true` if this provider enrols the student and `false` if not.
	 */
	public function is_enrolled( $user_id, $course_id ) {
		$products    = $this->get_course_simple_products( $course_id );
		$product_ids = array_map(
			function( \WC_Product $product ) {
				return $product->get_id();
			},
			$products
		);

		return $this->has_active_order_with_product( $user_id, $product_ids );
	}

	/**
	 * Trigger change when an order is trashed.
	 *
	 * @access private
	 *
	 * @param int $order_id Order post ID.
	 */
	public function trash_post( $order_id ) {
		$this->maybe_trigger_order_trash( $order_id, true );
	}

	/**
	 * Trigger change when an order is restored.
	 *
	 * @access private
	 *
	 * @param int $order_id Order post ID.
	 */
	public function untrash_post( $order_id ) {
		$this->maybe_trigger_order_trash( $order_id, false );
	}

	/**
	 * Trigger change when an order is trashed or restored.
	 *
	 * @param int  $order_id   Order post ID.
	 * @param bool $is_trashed If order trashed.
	 */
	private function maybe_trigger_order_trash( $order_id, $is_trashed ) {
		if ( ! in_array( \get_post_type( $order_id ), \wc_get_order_types( 'view-orders' ), true ) ) {
			return;
		}

		$order      = \wc_get_order( $order_id );
		$course_ids = $this->get_order_courses( $order );
		if ( empty( $course_ids ) ) {
			return;
		}

		$user_id = $order->get_customer_id();
		foreach ( $course_ids as $course_id ) {
			if ( ! $is_trashed ) {
				$this->restore_learner( $user_id, $course_id );
			}
			Course_Enrolment_Providers::trigger_course_enrolment_check( $user_id, $course_id );
		}
	}

	/**
	 * Trigger change when an order is trashed or restored.
	 *
	 * @deprecated 2.1.0 Use wc_get_product instead.
	 *
	 * @access private
	 *
	 * @param int $order_id Order post ID.
	 */
	public function maybe_trigger_order_trash_change( $order_id ) {
		_deprecated_function( __METHOD__, '2.1.0' );

		$this->maybe_trigger_order_trash( $order_id, get_post_status( $order_id ) === 'trash' );
	}

	/**
	 * Trigger enrolment recalculation when orders change status. This only triggers for courses
	 * attached to simple products in orders.
	 *
	 * @access private
	 *
	 * @param int       $order_id    Order post ID.
	 * @param string    $status_from Old order status slug.
	 * @param string    $status_to   New order status slug.
	 * @param \WC_Order $order       Order object.
	 */
	public function maybe_trigger_order_change( $order_id, $status_from, $status_to, \WC_Order $order ) {
		if ( $status_from === $status_to ) {
			return;
		}

		$paid_order_statuses = \wc_get_is_paid_statuses();
		$was_paid_status     = in_array( $status_from, $paid_order_statuses, true );
		$is_paid_status      = in_array( $status_to, $paid_order_statuses, true );

		if (
			// If neither status is a providing status, then they still don't have enrolment from this provider.
			( ! $was_paid_status && ! $is_paid_status )
			// If it is coming from and to a status that provides enrolment, then we don't need to recalculate.
			|| ( $was_paid_status && $is_paid_status )
		) {
			return;
		}

		$course_ids = $this->get_order_courses( $order );
		if ( empty( $course_ids ) ) {
			return;
		}

		$user_id = $order->get_customer_id();
		foreach ( $course_ids as $course_id ) {
			// If it is changing to a status that provides enrolment, then the learner's enrolment is restored.
			if ( $is_paid_status ) {
				$this->restore_learner( $user_id, $course_id );
			}

			Course_Enrolment_Providers::trigger_course_enrolment_check( $user_id, $course_id );
		}
	}

	/**
	 * Runs before deleting a post. For orders, we want to track the courses from that order.
	 *
	 * @access private
	 *
	 * @param int $order_id Order post ID.
	 */
	public function before_delete_order( $order_id ) {
		if (
			! in_array( \get_post_type( $order_id ), \wc_get_order_types( 'view-orders' ), true )
			|| 'trash' === \get_post_status( $order_id )
		) {
			// Bail if this isn't an order being deleted or if the post status was trash before deleting.
			return;
		}

		$order = \wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		$course_ids = $this->get_order_courses( $order );
		if ( empty( $course_ids ) ) {
			return;
		}

		$this->delete_order_data[ $order_id ] = [
			'user_id'    => $order->get_customer_id(),
			'course_ids' => $course_ids,
		];
	}

	/**
	 * Runs after deleting a post. If it was an order, we want to reset the course enrolment for this user.
	 *
	 * @access private
	 *
	 * @param int $order_id Order post ID.
	 */
	public function after_delete_order( $order_id ) {
		if ( ! isset( $this->delete_order_data[ $order_id ] ) ) {
			return;
		}

		$order_data = $this->delete_order_data[ $order_id ];

		foreach ( $order_data['course_ids'] as $course_id ) {
			Course_Enrolment_Providers::trigger_course_enrolment_check( $order_data['user_id'], $course_id );
		}

		unset( $this->delete_order_data[ $order_id ] );
	}

	/**
	 * Prevent learners who have progress and enrolment from a simple product order from being granted manual enrolment.
	 *
	 * Legacy behavior deleted course progress on cancellation and refund so if they have course progress but
	 * do not have an active order, they must have been granted that access afterward or by another provider
	 * which will handle it on their own.
	 *
	 * @param bool $is_legacy_enrolled If manual enrolment should be provided. Starts as true if they had course progress.
	 * @param int  $user_id            User ID.
	 * @param int  $course_id          Course post ID.
	 *
	 * @return bool
	 */
	public function maybe_allow_legacy_manual_enrolment( $is_legacy_enrolled, $user_id, $course_id ) {
		// No need to check if they are already not being given manual enrolment or this provider doesn't handle enrolment.
		if ( ! $is_legacy_enrolled || ! $this->handles_enrolment( $course_id ) ) {
			return $is_legacy_enrolled;
		}

		// If this provider is currently providing enrolment, do not give them manual enrolment.
		if ( $this->is_enrolled( $user_id, $course_id ) ) {
			return false;
		}

		return $is_legacy_enrolled;
	}

	/**
	 * Get the courses IDs that are provided by an order.
	 *
	 * @param \WC_Order $order
	 * @return int[]
	 */
	private function get_order_courses( \WC_Order $order ) {
		$course_ids = [];

		// Run through each product ordered.
		foreach ( $order->get_items() as $item ) {
			$product_id = $item->get_variation_id();
			if ( ! $product_id ) {
				$product_id = $item->get_product_id();
			}

			$product = \wc_get_product( $product_id );
			if ( ! $product || ! $this->is_product_simple( $product ) ) {
				continue;
			}

			$item_courses    = Courses::get_product_courses( $product->get_id() );
			$item_course_ids = \wp_list_pluck( $item_courses, 'ID' );

			$course_ids = array_merge( $item_course_ids, $course_ids );
		}

		return array_unique( $course_ids );
	}

	/**
	 * Check if a product is handled by this provider.
	 *
	 * @param \WC_Product $product Product to check.
	 *
	 * @return bool
	 */
	private function is_product_simple( \WC_Product $product ) {
		return in_array( $product->get_type(), [ 'simple', 'variable', 'variation' ], true );
	}

	/**
	 * Checks to see if a user has an active order with that product.
	 *
	 * @param int   $user_id     User ID.
	 * @param int[] $product_ids Product post IDs to filter for.
	 *
	 * @return bool
	 */
	private function has_active_order_with_product( $user_id, $product_ids ) {
		$order_query_args = [
			'status' => \wc_get_is_paid_statuses(),
		];
		$all_orders       = $this->get_all_orders( $user_id, $order_query_args );

		foreach ( $all_orders as $order ) {
			if ( $this->order_has_product( $order, $product_ids ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get the orders with a product.
	 *
	 * @param int   $user_id          User ID.
	 * @param int[] $product_ids      Product post IDs to filter for.
	 * @param array $order_query_args Query args to pass to order query.
	 *
	 * @return \WC_Order[]
	 */
	private function get_orders_with_product( $user_id, $product_ids, $order_query_args = [] ) {
		$all_orders = $this->get_all_orders( $user_id, $order_query_args );

		$orders = [];
		foreach ( $all_orders as $order ) {
			if ( $this->order_has_product( $order, $product_ids ) ) {
				$orders[] = $order;
			}
		}

		return $orders;
	}

	/**
	 * Get all the orders for a user.
	 *
	 * @param int   $user_id    User ID.
	 * @param array $query_args Query args for orders.
	 *
	 * @return \WC_Order[]
	 */
	private function get_all_orders( $user_id, $query_args = [] ) {
		$default_query_args = [
			'status' => 'any',
			'limit'  => -1,
		];

		$query_args                = array_merge( $default_query_args, $query_args );
		$query_args['customer_id'] = $user_id;

		return \wc_get_orders( $query_args );
	}

	/**
	 * Check if the order has a product.
	 *
	 * @param \WC_Order $order       Order object.
	 * @param int[]     $product_ids Product post IDs.
	 *
	 * @return bool
	 */
	private function order_has_product( \WC_Order $order, $product_ids ) {
		foreach ( $order->get_items() as $item ) {
			if (
				in_array( $item->get_product_id(), $product_ids, true )
				|| (
					method_exists( $item, 'get_variation_id' )
					&& in_array( $item->get_variation_id(), $product_ids, true )
				)
			) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get a list of simple/variation products associated with a course.
	 *
	 * @param int $course_id Course post ID.
	 *
	 * @return \WC_Product[]
	 */
	private function get_course_simple_products( $course_id ) {
		$course_products = Courses::get_course_products( $course_id );

		// Remove all products that aren't simple or variable.
		foreach ( $course_products as $index => $product ) {
			if ( ! $this->is_product_simple( $product ) ) {
				unset( $course_products[ $index ] );
			}
		}

		return $course_products;
	}

	/**
	 * Provide debugging information about a user's enrolment in a course.
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course post ID.
	 *
	 * @return string[] Array of human readable debug messages. Allowed HTML tags: a[href]; strong; em; span[style,class]
	 */
	public function debug( $user_id, $course_id ) {
		$messages = [];

		if ( ! $this->handles_enrolment( $course_id ) ) {
			return $messages;
		}

		$products = $this->get_course_simple_products( $course_id );

		$product_ids = array_map(
			function( \WC_Product $product ) {
				return $product->get_id();
			},
			$products
		);

		$order_query_args = [
			'status' => 'any',
		];
		$orders           = $this->get_orders_with_product( $user_id, $product_ids, $order_query_args );
		foreach ( $orders as $order ) {
			if ( in_array( $order->get_status(), \wc_get_is_paid_statuses(), true ) ) {
				$messages[ 'active-order-' . $order->get_id() ] = sprintf(
					// translators: %1$s is URL to edit/view the order; #%2$s is the order number.
					__( '<a href="%1$s">Order #%2$s</a> has a paid status and is providing enrollment.', 'sensei-pro' ),
					$order->get_edit_order_url(),
					$order->get_id()
				);
			} else {
				$messages[ 'inactive-order-' . $order->get_id() ] = sprintf(
					// translators: %1$s is URL to edit/view the order; #%2$s is the order number.
					__( '<a href="%1$s">Order #%2$s</a> contains a product that would provide enrollment for this course but it does not have a paid status.', 'sensei-pro' ),
					$order->get_edit_order_url(),
					$order->get_id()
				);
			}
		}

		if ( empty( $orders ) ) {
			$messages['no-orders'] = __( 'Learner does not have any orders for products that would provide enrollment for this course.', 'sensei-pro' );
		}

		return $messages;
	}

	/**
	 * Restores a learner's enrolment if it has been previously removed.
	 *
	 * @param int $user_id   The user id.
	 * @param int $course_id The course id.
	 */
	private function restore_learner( $user_id, $course_id ) {
		if ( ! Course_Enrolment_Providers::is_learner_removal_enabled() ) {
			return;
		}

		$enrolment = \Sensei_Course_Enrolment::get_course_instance( $course_id );
		if (
			$this->handles_enrolment( $course_id ) &&
			$enrolment->is_learner_removed( $user_id ) &&
			$this->is_enrolled( $user_id, $course_id )
		) {
			$enrolment->restore_learner( $user_id );
		}
	}

	/**
	 * Gets the version of the enrolment provider logic. If this changes, enrolment will be recalculated.
	 *
	 * This version should be bumped to the next stable plugin version whenever this provider is modified.
	 *
	 * @return int|string
	 */
	public function get_version() {
		return '2.0.0';
	}
}
