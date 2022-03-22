<?php
/**
 * File containing the class \Sensei_WC_Paid_Courses\Background_Jobs\Membership_Plan_Calculation_Job.
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
 * The Membership_Plan_Calculation_Job is responsible for running jobs of
 * enrolment calculations when membership plan is updated.
 *
 * @since 2.0.0
 */
class Membership_Plan_Calculation_Job implements Completable_Job {
	const NAME                             = 'sensei_wc_paid_courses_calculate_membership_enrolments';
	const PLAN_ENROLMENT_VERSION_META_NAME = 'sensei_wc_paid_courses_calculation_version';
	const MAXIMUM_BATCH_SIZE               = 20;
	const MAXIMUM_CALCS_PER_BATCH          = 500;
	const MAXIMUM_COURSES_AS_ARGS          = 20;

	/**
	 * Membership plan ID to recalculate.
	 *
	 * @var int
	 */
	private $plan_id;

	/**
	 * Course IDs to recalculate.
	 * If $course_ids is null, it means that all courses should be recalculated.
	 *
	 * @var int[]|null
	 */
	private $course_ids;

	/**
	 * Calculation version.
	 *
	 * @var string
	 */
	private $calculation_version;

	/**
	 * Number of memberships for each job run.
	 *
	 * @var int
	 */
	private $batch_size;

	/**
	 * Flag if the there are more batches to run.
	 *
	 * @var bool
	 */
	private $is_complete;

	/**
	 * Membership_Plan_Calculation_Job constructor.
	 *
	 * @param array $args Arguments to run for the job.
	 */
	public function __construct( $args ) {
		$this->plan_id             = isset( $args['plan_id'] ) ? intval( $args['plan_id'] ) : null;
		$this->course_ids          = isset( $args['course_ids'] ) ? $args['course_ids'] : null;
		$this->calculation_version = isset( $args['calculation_version'] ) ? $args['calculation_version'] : null;
		$this->batch_size          = isset( $args['batch_size'] ) ? intval( $args['batch_size'] ) : self::MAXIMUM_BATCH_SIZE;
	}

	/**
	 * Initialize necessary hooks.
	 */
	public static function init() {
		add_action( self::NAME, [ __CLASS__, 'on_job_hook' ] );
	}

	/**
	 * Self scheduling job. Hooked into the job action.
	 *
	 * @access private
	 *
	 * @param array $args Arguments for the job.
	 */
	public static function on_job_hook( $args ) {
		$job = new self( $args );
		Scheduler::instance()->handle_self_scheduling_job( $job );
	}

	/**
	 * Start plan recalculation.
	 *
	 * @param int   $plan_id    Plan ID.
	 * @param int[] $course_ids Course IDs.
	 */
	public static function start( $plan_id, $course_ids ) {
		$rescued_course_ids = self::rescue_and_cancel_pending_courses( $plan_id );

		if ( $rescued_course_ids ) {
			$course_ids = array_unique(
				array_merge( $course_ids, $rescued_course_ids )
			);
		}

		$args = [
			'plan_id'             => $plan_id,
			'calculation_version' => md5( uniqid() ),
		];

		if (
			count( $course_ids ) <= self::MAXIMUM_COURSES_AS_ARGS
			&& null !== $rescued_course_ids
		) {
			$args['course_ids'] = $course_ids;
		} else {
			$total_courses      = count( $course_ids );
			$args['batch_size'] = self::get_dynamic_batch_size( $total_courses );
		}

		$job = new self( $args );
		Scheduler::instance()->schedule_single_job( $job );
	}

	/**
	 * Get dynamic batch size to limit a maximum calcs per batch. This number can be exceeded
	 * if the number of courses is greater than the maximum. In this case, each batch will
	 * run the total number of courses.
	 *
	 * @param int $total_courses Total number of courses to calculate.
	 *
	 * @return int $dynamic_batch_size
	 */
	private static function get_dynamic_batch_size( $total_courses ) {
		$dynamic_batch_size = self::MAXIMUM_CALCS_PER_BATCH / $total_courses;
		$dynamic_batch_size = round( $dynamic_batch_size );
		$dynamic_batch_size = min( self::MAXIMUM_BATCH_SIZE, $dynamic_batch_size );
		$dynamic_batch_size = max( 1, $dynamic_batch_size );

		return $dynamic_batch_size;
	}

	/**
	 * Rescue and cancel pending plan recalculation actions.
	 *
	 * @param int $plan_id Plan ID to search as group.
	 *
	 * @return array|null $course_ids Pending course IDs. Returns `null` if any pending job has no
	 *                                `course_ids` (needs recalculation for all courses)
	 */
	private static function rescue_and_cancel_pending_courses( $plan_id ) {
		$course_ids      = [];
		$pending_actions = Scheduler::instance()->get_pending_actions(
			[
				'hook'  => self::NAME,
				'group' => $plan_id,
			]
		);

		foreach ( $pending_actions as $pending_action ) {
			if ( ! isset( $pending_action->get_args()[0]['course_ids'] ) ) {
				$course_ids = null;
				break;
			}

			$course_ids = array_merge( $course_ids, $pending_action->get_args()[0]['course_ids'] );
		}

		$course_ids = is_array( $course_ids ) ? array_unique( $course_ids ) : null;

		// Cancel pending actions.
		if ( ! empty( $pending_actions ) ) {
			Scheduler::instance()->unschedule_all_actions( self::NAME, null, $plan_id );
		}

		return $course_ids;
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
		$memberships_query = new \WP_Query( $this->get_query_args() );
		$this->is_complete = intval( $memberships_query->found_posts ) <= $this->batch_size;

		foreach ( $memberships_query->posts as $membership_post ) {
			$membership = wc_memberships_get_user_membership( $membership_post );

			if ( ! $membership ) {
				continue;
			}

			$user_id = $membership->get_user_id();

			foreach ( $this->get_course_ids() as $course_id ) {
				Course_Enrolment_Providers::trigger_course_enrolment_check( $user_id, $course_id );
			}

			update_post_meta( $membership->get_id(), self::PLAN_ENROLMENT_VERSION_META_NAME, $this->calculation_version );
		}
	}

	/**
	 * Get course ids to calculate.
	 *
	 * @return int[] $course_ids.
	 */
	private function get_course_ids() {
		if ( null !== $this->course_ids ) {
			return $this->course_ids;
		}

		return get_posts(
			[
				'post_type'   => 'course',
				'post_status' => 'publish',
				'fields'      => 'ids',
				'numberposts' => -1,
			]
		);
	}

	/**
	 * After the job runs, check to see if it needs to be re-queued for the next batch.
	 *
	 * @return bool
	 */
	public function is_complete() {
		return $this->is_complete;
	}

	/**
	 * Get the arguments to run with the job.
	 *
	 * @return array
	 */
	public function get_args() {
		$args = [
			'plan_id'             => $this->plan_id,
			'calculation_version' => $this->calculation_version,
		];

		if ( self::MAXIMUM_BATCH_SIZE !== $this->batch_size ) {
			$args['batch_size'] = $this->batch_size;
		}

		if ( is_array( $this->course_ids ) ) {
			$args['course_ids'] = $this->course_ids;
		}

		return $args;
	}

	/**
	 * Get the group name. No need to prefix with `sensei-wc-paid-courses`.
	 *
	 * @return string
	 */
	public function get_group() {
		return $this->plan_id;
	}

	/**
	 * Get the query arguments for the user query.
	 *
	 * @return array
	 */
	private function get_query_args() {
		$meta_query = [
			'relation' => 'OR',
			[
				'key'     => self::PLAN_ENROLMENT_VERSION_META_NAME,
				'value'   => $this->calculation_version,
				'compare' => '!=',
			],
			[
				'key'     => self::PLAN_ENROLMENT_VERSION_META_NAME,
				'compare' => 'NOT EXISTS',
			],
		];

		$active_statuses = array_map(
			function( $status ) {
				return 'wcm-' . $status;
			},
			wc_memberships()->get_user_memberships_instance()->get_active_access_membership_statuses()
		);

		$post_args = [
			'posts_per_page' => $this->batch_size,
			'meta_query'     => $meta_query,
			'post_status'    => $active_statuses,
			'post_type'      => 'wc_user_membership',
			'post_parent'    => $this->plan_id,
		];

		return $post_args;
	}
}
