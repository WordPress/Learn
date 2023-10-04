<?php
/**
 * File containing the Student_No_Progress_Recurring_Job class.
 *
 * @package sensei-wc-paid-courses
 */

namespace Sensei_WC_Paid_Courses\Background_Jobs;

use Sensei_Pro\Background_Jobs\Scheduler;
use Sensei_Pro\Background_Jobs\Cron_Job;
use WP_Comment_Query;
use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Student_No_Progress_Recurring_Job
 *
 * @since 1.12.0
 */
class Student_No_Progress_Recurring_Job implements Cron_Job {

	const NAME = 'sensei_wc_paid_courses_student_no_progress_recurring';

	/**
	 * Number of days without a progress.
	 *
	 * @var int
	 */
	private $days_without_progress;

	/**
	 * Course_Expiration_Notification_Job constructor.
	 *
	 * @param array $args Arguments to run for the job.
	 */
	public function __construct( $args ) {
		$this->days_without_progress = isset( $args['days_without_progress'] ) ? intval( $args['days_without_progress'] ) : 1;
	}

	/**
	 * Initialize necessary hooks.
	 */
	public static function init() {
		add_action( self::NAME, [ __CLASS__, 'on_job_hook' ] );
		add_action( 'init', [ __CLASS__, 'schedule_recurring_jobs' ] );
	}

	/**
	 * Course without progress scheduling job. Hooked into the job action.
	 *
	 * @access private
	 *
	 * @param array $args Arguments for the job.
	 */
	public static function on_job_hook( $args ) {
		$job = new Student_No_Progress_Job( $args );
		Scheduler::instance()->schedule_single_job( $job );
	}

	/**
	 * Schedule recurring no progress job.
	 *
	 * @access private
	 */
	public static function schedule_recurring_jobs() {
		$no_progress_periods = self::get_no_progress_periods();
		foreach ( $no_progress_periods as $days ) {
			$job = new self( [ 'days_without_progress' => $days ] );
			Scheduler::instance()->schedule_cron_job( $job );
		}
	}

	/**
	 * Get periods without progress we are interested about.
	 *
	 * @return int[]
	 */
	public static function get_no_progress_periods() {
		/**
		 * No progress periods in days.
		 * If you filter this, remember to remove the no progress jobs you don't want anymore.
		 * It can be done through the tool "Scheduled Actions" in WP-admin.
		 *
		 * @since 1.12.0
		 *
		 * @hook sensei_wc_paid_courses_course_no_progress_periods
		 *
		 * @param {int[] $no_progress_days_notifications The periods of no progress we are interested about.
		 *
		 * @return {int[]} The no progress days to send notification.
		 */
		return apply_filters(
			'sensei_wc_paid_courses_course_no_progress_periods',
			[ 3, 7, 28 ]
		);
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
		return [ 'days_without_progress' => $this->days_without_progress ];
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
		 * Schedule to run the no progress recurring job.
		 * The default value is daily at midnight.
		 *
		 * @since 1.12.0
		 *
		 * @param {string} $schedule              A cron-link schedule string.
		 * @param {int}    $days_without_progress Number of days without progress.
		 *
		 * @return {string} A cron-link schedule string.
		 */
		return apply_filters(
			'sensei_wc_paid_courses_course_no_progress_recurring_job_schedule',
			'0 0 * * *',
			$this->days_without_progress
		);
	}
}
