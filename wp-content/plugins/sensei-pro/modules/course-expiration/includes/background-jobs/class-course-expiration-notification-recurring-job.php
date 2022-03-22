<?php
/**
 * File containing the class \Sensei_Pro_Course_Expiration\Background_Jobs\Course_Expiration_Notification_Recurring_Job.
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
 * The Course_Expiration_Notification_Recurring_Job is responsible for starting the
 * expiration notification background job.
 *
 * @since 1.0.1
 */
class Course_Expiration_Notification_Recurring_Job implements Cron_Job {
	const NAME = 'sensei_wc_paid_courses_course_expiration_notification_recurring';

	/**
	 * Remaining days until expiration.
	 *
	 * @var int
	 */
	private $remaining_days;

	/**
	 * Course_Expiration_Notification_Job constructor.
	 *
	 * @param array $args Arguments to run for the job.
	 */
	public function __construct( $args ) {
		$this->remaining_days = isset( $args['remaining_days'] ) ? intval( $args['remaining_days'] ) : 1;
	}

	/**
	 * Initialize necessary hooks.
	 */
	public static function init() {
		add_action( self::NAME, [ __CLASS__, 'on_job_hook' ] );
		add_action( 'init', [ __CLASS__, 'schedule_recurring_jobs' ] );
	}

	/**
	 * Course expiration scheduling job. Hooked into the job action.
	 *
	 * @access private
	 *
	 * @param array $args Arguments for the job.
	 */
	public static function on_job_hook( $args ) {
		$job = new Course_Expiration_Notification_Job( $args );
		Scheduler::instance()->schedule_single_job( $job );
	}

	/**
	 * Schedule recurring expiration notification jobs.
	 *
	 * @access private
	 */
	public static function schedule_recurring_jobs() {
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

		/**
		 * Remaining days notifications.
		 * If you filter this, remember to remove the notification jobs you don't want anymore.
		 * It can be done through the tool "Scheduled Actions" in WP-admin.
		 *
		 * @since 2.6.0
		 * @hook sensei_wc_paid_courses_expiration_remaining_days_notifications
		 *
		 * @param {int[]} $remaining_days_notifications The remaining days that the user will receive notification.
		 *
		 * @return {int[]} The remaining days to send notification.
		 */
		$remaining_days_notifications = apply_filters( 'sensei_wc_paid_courses_expiration_remaining_days_notifications', [ 0, 3, 7 ] );

		foreach ( $remaining_days_notifications as $days ) {
			$job = new self( [ 'remaining_days' => $days ] );
			Scheduler::instance()->schedule_cron_job( $job );
		}
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
		return [ 'remaining_days' => $this->remaining_days ];
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
		 * Schedule to run the course expiration notification recurring job.
		 * The default value is daily at midnight.
		 *
		 * @since 2.6.0
		 * @hook sensei_wc_paid_courses_expiration_notification_job_recurring_schedule
		 *
		 * @param {string} $schedule       A cron-link schedule string.
		 * @param {int}    $remaining_days The remaining days used for this notification.
		 *
		 * @return {string} A cron-link schedule string.
		 */
		return apply_filters( 'sensei_wc_paid_courses_expiration_notification_job_recurring_schedule', '0 0 * * *', $this->remaining_days );
	}
}
