<?php
/**
 * Sensei WooCommerce Integration
 *
 * @package sensei-wc-paid-courses
 */

use Sensei_WC_Paid_Courses\Courses;

// @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Legacy classname.

/**
 * Sensei_WC_Subscriptions class. Adds integration between Sensei and
 * WooCommerce Subscriptions.
 *
 * @deprecated 2.0.0 Functionality has been moved into the enrolment provider.
 *
 * @since 1.0.0
 */
class Sensei_WC_Subscriptions {

	/**
	 * Set to `true` in order to disable caching. This will reduce performance,
	 * but is required in some environments, such as phpunit tests.
	 *
	 * @var bool $disable_caching
	 */
	public static $disable_caching = false;

	/**
	 * The default subscription types we can use.
	 *
	 * @var array $default_subscription_types
	 */
	private static $default_subscription_types = [
		'subscription',
		'subscription_variation',
		'variable-subscription',
	];

	/**
	 * Check if WooCommerce Subscriptions is active.
	 *
	 * @deprecated 2.0.0
	 *
	 * @return bool
	 */
	public static function is_wc_subscriptions_active() {
		_deprecated_function( __METHOD__, '2.0.0', '\Sensei_WC_Paid_Courses\Course_Enrolment_Providers\WooCommerce_Subscriptions::is_active' );

		return class_exists( 'WC_Subscriptions_Core_Plugin' ) || class_exists( 'WC_Subscriptions' );
	}

	/**
	 * Load WC Subscriptions integration hooks if WC Subscriptions is active
	 *
	 * @deprecated 2.0.0
	 *
	 * @return void
	 */
	public static function load_wc_subscriptions_integration_hooks() {
		_deprecated_function( __METHOD__, '2.0.0' );

		if ( false === self::is_wc_subscriptions_active() ) {
			return;
		}

		add_action( 'woocommerce_subscription_status_pending_to_active', [ __CLASS__, 'activate_subscription' ], 50, 3 );
		// filter the user permission of the subscription is not valid.
		add_filter( 'sensei_access_permissions', [ __CLASS__, 'get_subscription_permission' ], 10, 2 );
		// block user from accessing course when subscription is not valid.
		add_filter( 'sensei_user_started_course', [ __CLASS__, 'get_subscription_user_started_course' ], 10, 3 );
	}

	/**
	 * Check if user bought a subscription for the course but has since
	 * cancelled it.
	 *
	 * @deprecated 2.0.0
	 *
	 * @param int $course_id The Course ID.
	 * @param int $user_id   The User ID.
	 *
	 * @return bool
	 */
	public static function has_user_bought_subscription_but_cancelled( $course_id, $user_id ) {
		_deprecated_function( __METHOD__, '2.0.0' );

		if ( ! self::is_wc_subscriptions_active() ) {

			return false;

		}

		$product_ids = Sensei_WC::get_course_product_ids( $course_id );

		if ( empty( $product_ids ) ) {

			return false;

		}

		$cancelled_subscriptions = 0;

		foreach ( $product_ids as $product_id ) {

			$product = wc_get_product( $product_id );

			if ( ! ( $product instanceof \WC_Product ) ) {
				continue;
			}

			if ( ! in_array( $product->get_type(), self::get_subscription_types(), true ) ) {
				continue;
			}

			$user_subscription_active = wcs_user_has_subscription( $user_id, $product_id, 'active' );

			if ( $user_subscription_active ) {
				return false;
			}

			$user_subscription_cancelled = wcs_user_has_subscription( $user_id, $product_id, 'cancelled' );

			if ( $user_subscription_cancelled && ! self::is_user_eligible_for_access( $user_id, $product_id, $course_id ) ) {

				$cancelled_subscriptions++;

			}
		}

		// no active or cancelled subs.
		if ( 0 === $cancelled_subscriptions ) {

			return false;

		}

		if ( $cancelled_subscriptions > 0 ) {

			return true;

		}

		// assume the user was refunded, so technically it is ok to display a buy product.
		return true;
	}

	/**
	 * Responds to when a subscription product is purchased
	 *
	 * @deprecated 2.0.0
	 *
	 * @since Sensei 1.2.0
	 * @since Sensei 1.9.0 move to class Sensei_WC
	 * @since Sensei 1.9.12 move to class Sensei_WC_Subscriptions
	 *
	 * @param WC_Order $order The order containing the subscription.
	 *
	 * @return void
	 */
	public static function activate_subscription( $order ) {
		_deprecated_function( __METHOD__, '2.0.0' );

		$order_user         = get_user_by( 'id', $order->get_customer_id() );
		$user['ID']         = $order_user->ID;
		$user['user_login'] = $order_user->user_login;
		$user['user_email'] = $order_user->user_email;
		$user['user_url']   = $order_user->user_url;

		// Run through each product ordered.
		if ( ! count( $order->get_items() ) > 0 ) {

			return;

		}

		foreach ( $order->get_items() as $item ) {

			$item_id = Sensei_WC_Utils::get_item_id_from_item( $item );

			$product_type = WC_Product_Factory::get_product_type( $item_id );

			if ( Sensei_WC_Utils::is_wc_item_variation( $item ) ) {

				$product_type = 'subscription_variation';

			}

			// Get courses that use the WC product.
			$courses = [];

			if ( in_array( $product_type, self::get_subscription_types(), true ) ) {

				$courses = Courses::_back_compat_get_product_courses( $item_id );

			}

			// Loop and add the user to the course.
			foreach ( $courses as $course_item ) {

				Sensei_Utils::user_start_course( intval( $user['ID'] ), $course_item->ID );

			}
		}

	}

	/**
	 * Determine if the user has and active subscription to give them access
	 * to the requested resource.
	 *
	 * @deprecated 2.0.0
	 *
	 * @since Sensei 1.9.12
	 *
	 * @param boolean $user_access_permission The permission to check for.
	 * @param integer $user_id                The User ID.
	 * @return boolean $user_access_permission
	 */
	public static function get_subscription_permission( $user_access_permission, $user_id ) {
		_deprecated_function( __METHOD__, '2.0.0' );

		global $post;

		// ignore the current case if the following conditions are met.
		if ( ! class_exists( 'WC_Subscriptions' ) || empty( $user_id )
			|| ! in_array( $post->post_type, [ 'course', 'lesson', 'quiz' ], true )
			|| ! wcs_user_has_subscription( $user_id ) ) {

			return $user_access_permission;

		}

		// at this user has a subscription
		// is the subscription on the the current course?
		if ( 'course' === $post->post_type ) {

			$course_id = $post->ID;

		} elseif ( 'lesson' === $post->post_type ) {

			$course_id = Sensei()->lesson->get_course_id( $post->ID );

		} else {

			$lesson_id = Sensei()->quiz->get_lesson_id( $post->ID );
			$course_id = Sensei()->lesson->get_course_id( $lesson_id );

		}

		// if the course has no subscription WooCommerce product attached to return the permissions as is.
		$product_ids              = Sensei_WC::get_course_product_ids( $course_id );
		$subscription_product_ids = [];

		foreach ( $product_ids as $product_id ) {

			$product = wc_get_product( $product_id );

			if ( ! ( $product instanceof \WC_Product ) ) {
				continue;
			}

			if ( in_array( $product->get_type(), self::get_subscription_types(), true ) ) {

				$subscription_product_ids[] = $product_id;

			}
		}

		if ( empty( $subscription_product_ids ) ) {

			return $user_access_permission;

		}

		$user_has_access_through_a_subscription = false;

		foreach ( $subscription_product_ids as $subscription_product_id ) {
			if ( self::is_user_eligible_for_access( $user_id, $subscription_product_id, $course_id ) ) {

				$user_has_access_through_a_subscription = true;
				break;
			}
		}

		if ( $user_has_access_through_a_subscription ) {

			$user_access_permission = true;

		} else {

			$user_access_permission = false;
			// do not show the WC permissions message.
			remove_filter( 'sensei_the_no_permissions_message', [ 'Sensei_WC', 'alter_no_permissions_message' ], 20 );
			Sensei()->permissions_message['title']   = __( 'No active subscription', 'sensei-pro' );
			Sensei()->permissions_message['message'] = __( 'Sorry, you do not have an access to this content without an active subscription.', 'sensei-pro' );

		}

		return $user_access_permission;

	}

	/**
	 * Filter whether the user has started the course based on whether they have
	 * purchased a subscription.
	 *
	 * @deprecated 2.0.0
	 *
	 * @since Sensei 1.9.12
	 *
	 * @param bool $has_user_started_course Whether the user has started the course.
	 * @param int  $course_id               The Course ID.
	 * @param int  $user_id                 The User ID.
	 *
	 * @return bool $has_user_started_course
	 */
	public static function get_subscription_user_started_course( $has_user_started_course, $course_id, $user_id ) {
		_deprecated_function( __METHOD__, '2.0.0' );

		// avoid changing the filter value in the following cases.
		if ( empty( $course_id ) || empty( $user_id ) || ! is_user_logged_in() || is_admin()
			|| isset( $_POST['payment_method'] ) || isset( $_POST['order_status'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

			return $has_user_started_course;

		}

		// cached user course access for this process instance
		// also using temp cached data so we don't output the message again.
		global $sensei_wc_subscription_access_store;

		if ( ! is_array( $sensei_wc_subscription_access_store ) ) {
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Legacy caching variable.
			$sensei_wc_subscription_access_store = [];
		}

		$user_data_index_key = $course_id . '_' . $user_id;
		if ( ! self::$disable_caching && isset( $sensei_wc_subscription_access_store[ $user_data_index_key ] ) ) {
			return $sensei_wc_subscription_access_store[ $user_data_index_key ];
		}

		// if the course has no subscription WooCommerce products attached to return the permissions as is.
		$product_ids = Sensei_WC::get_course_product_ids( $course_id );

		if ( empty( $product_ids ) ) {
			return $has_user_started_course;
		}

		foreach ( $product_ids as $product_id ) {

			$product = wc_get_product( $product_id );

			if ( ! ( $product instanceof \WC_Product ) ) {
				continue;
			}

			if ( ! is_object( $product ) || ! in_array( $product->get_type(), self::get_subscription_types(), true ) ) {

				continue;

			}

			if ( self::is_user_eligible_for_access( $user_id, $product_id, $course_id ) ) {

				if ( ! $has_user_started_course ) {
					// Enroll in course.
					\Sensei_Utils::start_user_on_course( $user_id, $course_id );
				}

				$has_user_started_course = true;
				break;

			} else {

				$has_user_started_course = false;

			}
		}

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Legacy caching variable.
		$sensei_wc_subscription_access_store[ $user_data_index_key ] = $has_user_started_course;

		return $has_user_started_course;
	}

	/**
	 * Compare the user's subscriptions end date with the date
	 * the user was added to the course. If the user was added after
	 * the subscription ended they were manually added and this will return
	 * true.
	 *
	 * Important to note that all subscriptions for the user is compared.
	 *
	 * @deprecated 2.0.0
	 *
	 * @since Sensei 1.9.0
	 *
	 * @param int $user_id    The User ID.
	 * @param int $product_id The Product ID.
	 * @param int $course_id  The Course ID.
	 *
	 * @return bool
	 */
	public static function was_user_added_without_subscription( $user_id, $product_id, $course_id ) {
		_deprecated_function( __METHOD__, '2.0.0' );

		$was_user_added_without_subscription = false;

		// if user is not on the course they were not added.
		remove_filter( 'sensei_user_started_course', [ __CLASS__, 'get_subscription_user_started_course' ], 10 );
		if ( ! Sensei_Utils::user_started_course( $course_id, $user_id ) ) {

			return false;

		}

		// If the user has an active order for a non-subscription product, return `true`.
		if ( self::user_has_non_subscription_product_for_course( $user_id, $course_id ) ) {
			return true;
		}

		$product_ids           = Sensei_WC::get_course_product_ids( $course_id );
		$user_has_subscription = false;

		foreach ( $product_ids as $product_id ) {

			if ( wcs_user_has_subscription( $user_id, $product_id ) ) {

				$user_has_subscription = true;
				break;

			}
		}

		// if user doesn't have a subscription and is taking the course
		// they were added manually.
		if ( ! $user_has_subscription
			&& Sensei_Utils::user_started_course( $course_id, get_current_user_id() ) ) {

			return true;

		}

		add_filter( 'sensei_user_started_course', [ 'Sensei_WC_Subscriptions', 'get_subscription_user_started_course' ], 10, 3 );

		$course_status = Sensei_Utils::user_course_status( $course_id, $user_id );

		// comparing dates setup data.
		$course_start_date = date_create( $course_status->comment_date );
		$subscriptions     = wcs_get_users_subscriptions( $user_id );

		// comparing every subscription.
		foreach ( $subscriptions as $subscription ) {

			// for the following statuses we know the user was not added
			// manually.
			$status = $subscription->get_status();
			if ( in_array( $status, [ 'pending-canceled', 'active', 'on-hold', 'pending' ], true ) ) {

				continue;

			}

			$current_subscription_start_date = date_create( $subscription->get_date_modified() );

			// is the last updated subscription date newer than course start date.
			if ( $current_subscription_start_date > $course_start_date ) {

				return false;

			}
		}

		return $was_user_added_without_subscription;
	}

	/**
	 * Determine whether the user has an active order for a non-subscription
	 * product attached to the course.
	 *
	 * @deprecated 2.0.0
	 *
	 * @param int $user_id   The User ID.
	 * @param int $course_id The Course ID.
	 * @return bool
	 */
	private static function user_has_non_subscription_product_for_course( $user_id, $course_id ) {
		_deprecated_function( __METHOD__, '2.0.0' );

		$order_ids = Sensei_WC::get_learner_course_active_order_id( $user_id, $course_id, true, true );

		foreach ( $order_ids as $order_id ) {
			$order = wc_get_order( $order_id );
			$items = $order->get_items();

			foreach ( $items as $item ) {
				$product_id = Sensei_WC_Utils::get_item_id_from_item( $item, true );
				$product    = wc_get_product( $product_id );

				if ( ! ( $product instanceof \WC_Product ) ) {
					continue;
				}

				$product_type = $product->get_type();

				if ( ! in_array( $product_type, [ 'variable-subscription', 'subscription' ], true ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Get all the valid subscription types.
	 *
	 * @deprecated 2.0.0 Use \Sensei_WC_Paid_Courses\Course_Enrolment_Providers\WooCommerce_Subscriptions::get_subscription_types instead.
	 * @since Sensei 1.9.0
	 *
	 * @return array
	 */
	public static function get_subscription_types() {
		_deprecated_function( __METHOD__, '2.0.0', '\Sensei_WC_Paid_Courses\Course_Enrolment_Providers\WooCommerce_Subscriptions::get_subscription_types' );

		/**
		 * Filter is documented in `includes/course-enrolment-providers/class-woocommerce-subscriptions.php `.
		 */
		return apply_filters( 'sensei_wc_paid_courses_subscriptions_get_subscription_types', self::$default_subscription_types );

	}

	/**
	 * Give access if user has active subscription on the product otherwise restrict it.
	 * also check if the user was added to the course directly after the subscription started.
	 *
	 * @deprecated 2.0.0
	 *
	 * @param int $user_id    The User ID.
	 * @param int $product_id The Product ID.
	 * @param int $course_id  The Course ID.
	 *
	 * @return bool
	 */
	private static function is_user_eligible_for_access( $user_id, $product_id, $course_id ) {
		_deprecated_function( __METHOD__, '2.0.0' );

		return wcs_user_has_subscription( $user_id, $product_id, 'active' )
		|| wcs_user_has_subscription( $user_id, $product_id, 'pending-cancel' )
		|| self::was_user_added_without_subscription( $user_id, $product_id, $course_id );
	}
}
