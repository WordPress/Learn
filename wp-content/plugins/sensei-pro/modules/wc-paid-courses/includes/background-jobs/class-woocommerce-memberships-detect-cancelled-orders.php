<?php
/**
 * File containing the class \Sensei_WC_Paid_Courses\Background_Jobs\Scheduler.
 *
 * @package sensei-wc-paid-courses
 * @since   2.0.0
 */

namespace Sensei_WC_Paid_Courses\Background_Jobs;

use Sensei_WC_Paid_Courses\Course_Enrolment_Providers;
use Sensei_Pro\Background_Jobs\Completable_Job;
use Sensei_Pro\Background_Jobs\Scheduler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Detects active memberships with cancelled orders.
 *
 * @since 2.0.0
 */
class WooCommerce_Memberships_Detect_Cancelled_Orders implements Completable_Job {
	const NAME                              = 'sensei_wc_paid_courses_memberships_detect_cancelled_orders';
	const TRACKED_MEMBERSHIP_RESULTS_OPTION = 'sensei-wc-paid-courses-memberships-cancelled-orders';

	/**
	 * Initialize necessary hooks.
	 */
	public static function init() {
		// Only needed when coming from a legacy instance and now in a Sensei 3.x environment.
		if (
			! \get_option( 'sensei_enrolment_legacy' )
			|| Course_Enrolment_Providers::use_legacy_enrolment_method()
		) {
			return;
		}

		\add_action( 'init', [ __CLASS__, 'maybe_schedule_self' ] );

		\add_action(
			self::NAME,
			function() {
				Scheduler::instance()->handle_self_scheduling_job( new self() );
			}
		);
	}

	/**
	 * Maybe enqueue this job if necessary.
	 *
	 * @access private
	 */
	public static function maybe_schedule_self() {
		$option_check = \get_option( self::TRACKED_MEMBERSHIP_RESULTS_OPTION, false );

		if ( $option_check ) {
			return;
		}

		$results_value = [
			'time'   => gmdate( 'Y-m-d H:i:s' ),
			'status' => 'pending',
		];
		\update_option( self::TRACKED_MEMBERSHIP_RESULTS_OPTION, \wp_json_encode( $results_value ) );

		Scheduler::instance()->schedule_single_job( new self() );
	}

	/**
	 * Get the action name for the scheduled job.
	 *
	 * @return string
	 */
	public function get_name() {
		return self::NAME;
	}

	/**
	 * Run the job.
	 */
	public function run() {
		$membership_ids = $this->get_active_membership_ids_with_cancelled_orders();

		$results_value = [
			'time'   => gmdate( 'Y-m-d H:i:s' ),
			'status' => 'complete',
			'ids'    => $membership_ids,
		];

		\update_option( self::TRACKED_MEMBERSHIP_RESULTS_OPTION, \wp_json_encode( $results_value ) );
	}

	/**
	 * After the job has been completed, check to see if it needs to be re-queued for the next batch.
	 *
	 * This job doesn't run in batches so assume it is complete.
	 *
	 * @return bool
	 */
	public function is_complete() {
		return true;
	}

	/**
	 * Get the membership plans IDs that restrict based on course or course taxonomy.
	 *
	 * @return int[]
	 */
	private function get_plan_ids() {
		$plans = \wc_memberships_get_membership_plans(
			[
				'meta_query' => [
					[
						'key'   => '_access_method',
						'value' => 'purchase',
					],
				],
			]
		);

		$plan_ids = [];
		foreach ( $plans as $plan ) {
			// Get the course restriction rules for the plan.
			$rules = $plan->get_rules( 'content_restriction' );

			foreach ( $rules as $rule ) {
				// Check if this is a Course restriction rule.
				if (
					( 'post_type' === $rule->get_content_type() && 'course' === $rule->get_content_type_name() )
					|| ( 'taxonomy' === $rule->get_content_type() && 'course-category' === $rule->get_content_type_name() )
				) {
					$plan_ids[] = $plan->get_id();
					break;
				}
			}
		}

		return $plan_ids;
	}

	/**
	 * Get the active membership IDs for memberships tied to cancelled orders.
	 *
	 * @return int[]
	 */
	private function get_active_membership_ids_with_cancelled_orders() {
		global $wpdb;

		$plan_ids = $this->get_plan_ids();
		if ( empty( $plan_ids ) ) {
			return [];
		}

		$plan_ids_str  = implode( ',', $plan_ids );
		$sql_statement = "
			SELECT p1.`ID` FROM {$wpdb->posts} p1
				LEFT JOIN {$wpdb->postmeta} pm ON ( p1.ID = pm.`post_id` AND pm.`meta_key`='_order_id' )
				LEFT JOIN {$wpdb->posts} p2 ON ( pm.`meta_value` = p2.ID )
				WHERE p1.`post_type`='wc_user_membership'
					AND p1.`post_status`='wcm-active'
					AND p2.`post_status`='wc-cancelled'
					AND p1.`post_parent` IN ( {$plan_ids_str} )
		";

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared -- Async call with lots of joins. No vars that can be prepared.
		$results_raw = $wpdb->get_col( $sql_statement );

		return array_map( 'intval', $results_raw );
	}

	/**
	 * Get the arguments to run with the job.
	 *
	 * @return array
	 */
	public function get_args() {
		return [];
	}

	/**
	 * Get the group name. No need to prefix with `sensei-wc-paid-listings`.
	 *
	 * @return string
	 */
	public function get_group() {
		return 'default';
	}
}
