<?php
/**
 * File containing the class \Sensei_Pro_Course_Expiration\Background_Jobs\Course_Expiration_Recurring_Job.
 *
 * @package sensei-pro
 * @since   1.0.1
 */

namespace Sensei_Pro_Course_Expiration\Background_Jobs;

use Sensei_Pro_Course_Expiration\Course_Expiration;
use Sensei_Pro\Background_Jobs\Scheduler;
use Sensei_Pro\Background_Jobs\Cron_Job;
use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Course_Expiration_Recurring_Job is responsible for start the
 * expiration background job.
 *
 * @since 1.0.1
 */
class Course_Expiration_Recurring_Job implements Cron_Job {
	const NAME = 'sensei_wc_paid_courses_expires_course_learner_enrollments_recurring';

	/**
	 * Initialize necessary hooks.
	 */
	public static function init() {
		add_action( self::NAME, [ __CLASS__, 'on_job_hook' ] );
		add_action( 'init', [ __CLASS__, 'schedule_recurring_job' ] );
	}

	/**
	 * Course expiration scheduling job. Hooked into the job action.
	 *
	 * @access private
	 *
	 * @param array $args Arguments for the job.
	 */
	public static function on_job_hook( $args ) {
		$job = new Course_Expiration_Job( $args );
		Scheduler::instance()->schedule_single_job( $job );
	}

	/**
	 * Schedule recurring expiration job.
	 *
	 * @access private
	 */
	public static function schedule_recurring_job() {
		$courses_with_expiration = new WP_Query(
			[
				'post_type'        => 'course',
				'meta_compare_key' => 'LIKE',
				'meta_key'         => Course_Expiration::EXPIRATION_TIMESTAMP_COURSE_META_PREFIX,
			]
		);

		// Skip job when not having accesses to expire.
		if ( 0 === $courses_with_expiration->found_posts ) {
			return;
		}

		Scheduler::instance()->schedule_cron_job( new self() );
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

	/**
	 * Get the cron schedule. A cron-link schedule string.
	 *
	 * @return string
	 */
	public function get_schedule() {
		/**
		 * Schedule to run the course expiration recurring job.
		 * The default value is daily at midnight.
		 *
		 * @since 2.6.0
		 * @hook sensei_wc_paid_courses_expiration_job_recurring_schedule
		 *
		 * @param {string} $schedule A cron-link schedule string.
		 *
		 * @return {string} A cron-link schedule string.
		 */
		return apply_filters( 'sensei_wc_paid_courses_expiration_job_recurring_schedule', '0 0 * * *' );
	}
}
