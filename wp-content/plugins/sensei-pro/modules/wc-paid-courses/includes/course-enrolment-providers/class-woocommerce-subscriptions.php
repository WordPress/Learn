<?php
/**
 * File containing the class \Sensei_WC_Paid_Courses\Course_Enrolment_Providers\WooCommerce_Subscriptions.
 *
 * @package sensei-wc-paid-courses
 * @since   2.0.0
 */

namespace Sensei_WC_Paid_Courses\Course_Enrolment_Providers;

use Sensei_WC_Paid_Courses\Course_Enrolment_Providers;
use Sensei_WC_Paid_Courses\Courses;

/**
 * Course enrolment provider for courses where enrolment is controlled by a subscription.
 *
 * @since 2.0.0
 */
class WooCommerce_Subscriptions
	implements \Sensei_Course_Enrolment_Provider_Interface, \Sensei_Course_Enrolment_Provider_Debug_Interface {
	/**
	 * Singleton instance.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Cache of the product IDs for different courses.
	 *
	 * @var array
	 */
	private $course_product_ids = [];

	/**
	 * Track the course IDs and customer ID when deleting a subscription.
	 *
	 * @var array[]
	 */
	private $delete_subscription_data = [];

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
	 * Check if WooCommerce Subscriptions is active.
	 *
	 * @return bool
	 */
	public static function is_active() {
		return class_exists( 'WC_Subscriptions_Core_Plugin' ) || class_exists( 'WC_Subscriptions' );
	}

	/**
	 * Class constructor. Private so it can only be initialized internally.
	 */
	private function __construct() {}

	/**
	 * Adds the actions related to subscription products.
	 */
	public function init() {
		\add_filter( 'sensei_is_legacy_enrolled', [ $this, 'maybe_allow_legacy_manual_enrolment' ], 10, 4 );

		// Order lifecycle hooks to listen to. We don't listen for `untrashed_post` here because subscriptions are
		// cancelled when they are sent to the trash. In the UI, trashing a subscription isn't possible until they are
		// cancelled.
		\add_action( 'woocommerce_subscription_status_updated', [ $this, 'maybe_trigger_enrolment_check' ], 10, 3 );
		\add_action( 'trashed_post', [ $this, 'maybe_trigger_subscription_trash_change' ] );

		// Adds support for deleting directly or for disabled `EMPTY_TRASH_DAYS`.
		\add_action( 'before_delete_post', [ $this, 'before_delete_subscription' ], 1 );
		\add_action( 'deleted_post', [ $this, 'after_delete_subscription' ] );
	}

	/**
	 * Gets the unique identifier of this enrolment provider.
	 *
	 * @return int
	 */
	public function get_id() {
		return 'wc-subscriptions';
	}

	/**
	 * Gets the descriptive name of the provider.
	 *
	 * @return string
	 */
	public function get_name() {
		return esc_html__( 'WooCommerce Subscriptions', 'sensei-pro' );
	}

	/**
	 * Check if this course enrolment provider manages enrolment for a particular course.
	 *
	 * @param int $course_id Course post ID.
	 *
	 * @return bool
	 */
	public function handles_enrolment( $course_id ) {
		$product_ids = $this->get_course_subscription_products( $course_id );

		return ! empty( $product_ids );
	}

	/**
	 * Check if this course enrolment provider is enrolling a user to a course.
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course post ID.
	 *
	 * @return bool  `true` if this provider enrols the student and `false` if not.
	 */
	public function is_enrolled( $user_id, $course_id ) {
		$product_ids = $this->get_course_subscription_products( $course_id );
		foreach ( $product_ids as $product_id ) {
			if ( \wcs_user_has_subscription( $user_id, $product_id, $this->get_active_subscription_statuses() ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Trigger change when a subscription is trashed or restored. We'll let Subscriptions decide if the parent
	 * order cancels the subscription when that is trashed.
	 *
	 * @param int $subscription_id Subscription post ID.
	 */
	public function maybe_trigger_subscription_trash_change( $subscription_id ) {
		if ( 'shop_subscription' !== \get_post_type( $subscription_id ) ) {
			return;
		}

		$subscription = \wcs_get_subscription( $subscription_id );
		$courses      = $this->get_subscription_courses( $subscription );
		$course_ids   = wp_list_pluck( $courses, 'ID' );

		if ( empty( $course_ids ) ) {
			return;
		}

		foreach ( $course_ids as $course_id ) {
			Course_Enrolment_Providers::trigger_course_enrolment_check( $subscription->get_customer_id(), $course_id );
		}
	}

	/**
	 * Trigger enrolment recalculation when subscriptions change status.
	 *
	 * @param \WC_Subscription $subscription Subscription post ID.
	 * @param string           $status_to    New order status slug.
	 * @param string           $status_from  Old order status slug.
	 */
	public function maybe_trigger_enrolment_check( $subscription, $status_to, $status_from ) {
		if ( $status_from === $status_to ) {
			return;
		}

		$active_statuses   = $this->get_active_subscription_statuses();
		$was_active_status = in_array( $status_from, $active_statuses, true );
		$is_active_status  = in_array( $status_to, $active_statuses, true );

		if (
			// If neither status is a providing status, then they still don't have enrolment from this provider.
			( ! $was_active_status && ! $is_active_status )
			// If it is coming from and to a status that provides enrolment, then we don't need to recalculate.
			|| ( $was_active_status && $is_active_status )
		) {
			return;
		}

		$courses    = $this->get_subscription_courses( $subscription );
		$course_ids = wp_list_pluck( $courses, 'ID' );
		if ( empty( $course_ids ) ) {
			return;
		}

		$user_id = $subscription->get_customer_id();
		foreach ( $course_ids as $course_id ) {
			// If it is changing to a status that provides enrolment, then the learner's enrolment is restored.
			if ( $is_active_status ) {
				$this->restore_learner( $user_id, $course_id );
			}

			Course_Enrolment_Providers::trigger_course_enrolment_check( $user_id, $course_id );
		}
	}

	/**
	 * Runs before deleting a post. For subscriptions, we want to track the courses from that subscription.
	 *
	 * @access private
	 *
	 * @param int $subscription_id Subscription post ID.
	 */
	public function before_delete_subscription( $subscription_id ) {
		if (
			'shop_subscription' !== \get_post_type( $subscription_id )
			|| 'trash' === \get_post_status( $subscription_id )
		) {
			// Bail if this isn't a subscription being deleted or if the post status was trash before deleting.
			return;
		}

		$subscription = \wcs_get_subscription( $subscription_id );
		if ( ! $subscription ) {
			return;
		}

		$courses    = $this->get_subscription_courses( $subscription );
		$course_ids = wp_list_pluck( $courses, 'ID' );
		if ( empty( $course_ids ) ) {
			return;
		}

		$this->delete_subscription_data[ $subscription_id ] = [
			'user_id'    => $subscription->get_customer_id(),
			'course_ids' => $course_ids,
		];
	}

	/**
	 * Runs after deleting a post. If it was a subscription, we want to reset the course enrolment for this user.
	 *
	 * @access private
	 *
	 * @param int $subscription_id Subscription post ID.
	 */
	public function after_delete_subscription( $subscription_id ) {
		if ( ! isset( $this->delete_subscription_data[ $subscription_id ] ) ) {
			return;
		}

		$subscription_data = $this->delete_subscription_data[ $subscription_id ];

		foreach ( $subscription_data['course_ids'] as $course_id ) {
			Course_Enrolment_Providers::trigger_course_enrolment_check( $subscription_data['user_id'], $course_id );
		}

		unset( $this->delete_subscription_data[ $subscription_id ] );
	}

	/**
	 * Prevent learners who have progress from being granted manual enrolment if they currently have an active
	 * subscription or had one when course progress started.
	 *
	 * Legacy behavior would allow some learners to keep their course progress but block their access.
	 *
	 * @access private
	 *
	 * @param bool      $is_legacy_enrolled          If manual enrolment should be provided. Starts as true if they had course progress.
	 * @param int       $user_id                     User ID.
	 * @param int       $course_id                   Course post ID.
	 * @param int|false $course_progress_comment_id  Comment ID for the course progress record (if it exists).
	 *
	 * @return bool
	 */
	public function maybe_allow_legacy_manual_enrolment( $is_legacy_enrolled, $user_id, $course_id, $course_progress_comment_id ) {
		// No need to check if they are already not being given manual enrolment.
		if ( ! $is_legacy_enrolled || ! $this->handles_enrolment( $course_id ) ) {
			return $is_legacy_enrolled;
		}

		// If this provider is currently providing enrolment, do not give them manual enrolment and bail early.
		if ( $this->is_enrolled( $user_id, $course_id ) ) {
			return false;
		}

		// Check to see if they were in a subscription when this course was started.
		$start_date_str = \get_comment_meta( $course_progress_comment_id, 'start', true );
		if ( empty( $start_date_str ) ) {
			return $is_legacy_enrolled;
		}

		$course_start_date = new \DateTime( $start_date_str );
		$inactive_statuses = $this->get_inactive_subscription_statuses();
		$subscriptions     = $this->get_user_course_subscriptions( $user_id, $course_id, $inactive_statuses );
		foreach ( $subscriptions as $subscription ) {
			if ( $this->was_subscription_active( $subscription, $course_start_date ) ) {
				// They had an active subscription when they started the course. Do not give them
				// manual enrolment on migration.
				$is_legacy_enrolled = false;

				break;
			}
		}

		return $is_legacy_enrolled;
	}

	/**
	 * Check to see if a subscription was active at a specific time.
	 *
	 * @param \WC_Subscription $subscription Subscription to check.
	 * @param \DateTime        $check_time   Time to check and see if the subscription was active during this period.
	 *
	 * @return bool
	 */
	private function was_subscription_active( \WC_Subscription $subscription, \DateTime $check_time ) {
		$subscription_start_date_str = $subscription->get_date( 'start' );
		$subscription_end_date_str   = $this->get_subscription_end_date( $subscription );

		// This should only happen if the subscription was never actually active.
		if ( empty( $subscription_start_date_str ) ) {
			return false;
		}

		$subscription_start_date = new \DateTime( $subscription_start_date_str );
		$subscription_end_date   = new \DateTime( $subscription_end_date_str );

		// If the check time was before the start date or after the end date, consider it outside of the subscription
		// active period.
		if ( $check_time < $subscription_start_date || $check_time > $subscription_end_date ) {
			return false;
		}

		return true;
	}

	/**
	 * Get the subscription end date. Sometimes we may need to make an educated guess. Subscriptions that were put on
	 * hold or possibly never started may not have an end date set.
	 *
	 * @param \WC_Subscription $subscription Subscription to guess end date for.
	 *
	 * @return string
	 */
	private function get_subscription_end_date( \WC_Subscription $subscription ) {
		$end_date_str = $subscription->get_date( 'end' );
		if ( ! empty( $end_date_str ) ) {
			return $end_date_str;
		}

		$next_payment_date_str  = $subscription->get_date( 'next_payment' );
		$date_modified_date_str = $subscription->get_date( 'date_modified' );

		$next_payment_date  = new \DateTime( $next_payment_date_str );
		$date_modified_date = new \DateTime( $date_modified_date_str );
		$now_date           = new \DateTime();

		// If the next payment date is before the date modified, assume the subscription was modified
		// after it actually ended. In this case, use the next payment date.
		if (
			! empty( $next_payment_date_str )
			&& $next_payment_date < $now_date // Date must be in the past.
			&& $next_payment_date < $date_modified_date // Date must be less than the date modified.
		) {
			return $next_payment_date_str;
		}

		return $date_modified_date_str;
	}

	/**
	 * Get a user's subscriptions related to a course.
	 *
	 * @param int   $user_id   User ID.
	 * @param int   $course_id Course ID.
	 * @param array $statuses  Subscription statuses to include (Optional).
	 *
	 * @return \WC_Subscription[]
	 */
	private function get_user_course_subscriptions( $user_id, $course_id, $statuses = null ) {
		$subscriptions_raw  = \wcs_get_users_subscriptions( $user_id );
		$course_product_ids = $this->get_course_subscription_products( $course_id );

		if ( empty( $course_product_ids ) || empty( $subscriptions_raw ) ) {
			return [];
		}

		$subscriptions = [];
		foreach ( $subscriptions_raw as $subscription ) {
			$subscription_product_ids = $this->get_subscription_product_ids( $subscription );
			if ( empty( array_intersect( $subscription_product_ids, $course_product_ids ) ) ) {
				continue;
			}

			if ( is_array( $statuses ) && ! in_array( $subscription->get_status(), $statuses, true ) ) {
				continue;
			}

			$subscriptions[] = $subscription;
		}

		return $subscriptions;
	}

	/**
	 * Get the courses attached to a subscription.
	 *
	 * @param \WC_Subscription $subscription
	 *
	 * @return \WP_Post[]
	 */
	private function get_subscription_courses( \WC_Subscription $subscription ) {
		$product_ids = $this->get_subscription_product_ids( $subscription );

		return Courses::get_product_courses( $product_ids );
	}

	/**
	 * Get the products associated with a subscription.
	 *
	 * @param \WC_Subscription $subscription
	 *
	 * @return int[]
	 */
	private function get_subscription_product_ids( \WC_Subscription $subscription ) {
		$product_ids = [];
		foreach ( $subscription->get_items() as $item ) {
			$product_id = \wcs_get_canonical_product_id( $item );
			$product    = \wc_get_product( $product_id );
			if (
				! ( $product instanceof \WC_Product )
				|| ! in_array( $product->get_type(), $this->get_subscription_types(), true )
			) {
				continue;
			}

			$product_ids[] = $product_id;
		}

		return array_unique( $product_ids );
	}

	/**
	 * Get the subscription product IDs associated with a course.
	 *
	 * @param int $course_id Course post ID.
	 *
	 * @return int[] Product post IDs.
	 */
	private function get_course_subscription_products( $course_id ) {
		if ( ! isset( $this->course_product_ids[ $course_id ] ) ) {
			$this->course_product_ids[ $course_id ] = [];
			$products                               = Courses::get_course_products( $course_id );

			foreach ( $products as $product ) {
				if ( ! in_array( $product->get_type(), self::get_subscription_types(), true ) ) {
					continue;
				}

				$this->course_product_ids[ $course_id ][] = $product->get_id();

				// For variable subscriptions, we pass on enrolment to all variations.
				if ( $product instanceof \WC_Product_Variable_Subscription ) {
					$this->course_product_ids[ $course_id ] = array_merge( $this->course_product_ids[ $course_id ], $product->get_children() );
				}
			}

			$this->course_product_ids[ $course_id ] = array_unique( $this->course_product_ids[ $course_id ] );
		}

		return $this->course_product_ids[ $course_id ];
	}

	/**
	 * Get all the valid subscription types.
	 *
	 * @since Sensei 2.0.0 Moved from `Sensei_WC_Subscriptions`.
	 *
	 * @return array
	 */
	public function get_subscription_types() {
		$default_subscription_types = [ 'subscription', 'subscription_variation', 'variable-subscription' ];

		/**
		 * Get the subscription types for products.
		 *
		 * @since 1.0.0
		 *
		 * @param array $subscription_types Array of all product type slugs for subscription products.
		 */
		return apply_filters( 'sensei_wc_paid_courses_subscriptions_get_subscription_types', $default_subscription_types );
	}

	/**
	 * Get the active subscription statuses.
	 *
	 * @return string[]
	 */
	public function get_active_subscription_statuses() {
		$default_active_subscription_statuses = [ 'active', 'pending-cancel' ];

		/**
		 * Get the active subscription statuses.
		 *
		 * @param array $subscription_statuses Status slugs for active subscriptions.
		 */
		return apply_filters( 'sensei_wc_paid_courses_subscriptions_get_active_subscription_statuses', $default_active_subscription_statuses );
	}

	/**
	 * Get the inactive subscription statuses.
	 *
	 * @return string[]
	 */
	private function get_inactive_subscription_statuses() {
		$active_statuses  = $this->get_active_subscription_statuses();
		$all_statuses_raw = array_keys( \wcs_get_subscription_statuses() );
		$all_statuses     = array_map(
			function ( $raw_status ) {
				// Trim off the `wc-` prefix of the raw status.
				return substr( $raw_status, 3 );
			},
			$all_statuses_raw
		);

		return array_diff( $all_statuses, $active_statuses );
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

		$subscriptions = $this->get_user_course_subscriptions( $user_id, $course_id );
		foreach ( $subscriptions as $subscription ) {
			if ( in_array( $subscription->get_status(), $this->get_active_subscription_statuses(), true ) ) {
				$messages[ 'active-subscription-' . $subscription->get_id() ] = sprintf(
					// translators: %1$s is URL to edit/view the subscription; #%2$s is the subscription number.
					__( '<a href="%1$s">Subscription #%2$s</a> has an active status and is providing enrollment.', 'sensei-pro' ),
					$subscription->get_edit_order_url(),
					$subscription->get_id()
				);
			} else {
				$messages[ 'inactive-subscription-' . $subscription->get_id() ] = sprintf(
					// translators: %1$s is URL to edit/view the subscription; #%2$s is the subscription number.
					__( '<a href="%1$s">Subscription #%2$s</a> contains a product that would provide enrollment for this course but it does not have an active status.', 'sensei-pro' ),
					$subscription->get_edit_order_url(),
					$subscription->get_id()
				);
			}
		}

		if ( empty( $subscriptions ) ) {
			$messages['no-subscriptions'] = __( 'Learner does not have any subscriptions for products that would provide enrollment for this course.', 'sensei-pro' );
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
