<?php
/**
 * File containing the Student_No_Progress_Job class.
 *
 * @package sensei-wc-paid-courses
 */

namespace Sensei_WC_Paid_Courses\Background_Jobs;

use Sensei_Pro\Background_Jobs\Completable_Job;
use Sensei_Pro\Background_Jobs\Scheduler;

/**
 * Class Student_No_Progress_Job
 *
 * @since 1.12.0
 */
class Student_No_Progress_Job implements Completable_Job {

	const NAME               = 'sensei_wc_paid_courses_student_no_progress';
	const DEFAULT_BATCH_SIZE = 50;

	/**
	 * Number of expirations for each job run.
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
	 * Last sent item.
	 *
	 * @var int
	 */
	private $last_item;

	/**
	 * Number of days without any progress.
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
		$this->batch_size            = isset( $args['batch_size'] ) ? intval( $args['batch_size'] ) : self::DEFAULT_BATCH_SIZE;
		$this->last_item             = isset( $args['last_item'] ) ? intval( $args['last_item'] ) : 0;
		$this->days_without_progress = isset( $args['days_without_progress'] ) ? intval( $args['days_without_progress'] ) : 3;
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
		Scheduler::instance()->handle_self_scheduling_job_after_run( $job );
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
	 * Find users that don't have progress in courses/lessons for X days and do action with them.
	 */
	public function run() {
		global $wpdb;

		$current_date = current_datetime()->setTime( 0, 0, 0 );
		$interval_to  = $this->days_without_progress - 1;
		$from_date    = $current_date->modify( "-{$this->days_without_progress} day" )->format( 'Y-m-d H:i:s' );
		$to_date      = $current_date->modify( "-{$interval_to} day" )->format( 'Y-m-d H:i:s' );

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$results           = $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT MAX(c.comment_ID) as id, MAX(c.comment_date_gmt) as comment_date_gmt, c.user_id, m.meta_value course_id
				FROM {$wpdb->comments} c
				INNER JOIN {$wpdb->commentmeta} cm
					ON cm.comment_id = c.comment_ID AND cm.meta_key = 'start' AND cm.meta_value BETWEEN %s AND %s
				INNER JOIN {$wpdb->postmeta} m
					ON m.post_id = c.comment_post_ID AND m.meta_key = '_lesson_course'
				LEFT JOIN (
					SELECT MAX(c.comment_date_gmt) as max_comment_date_gmt, c.user_id, m.meta_value course_id
					FROM {$wpdb->comments} c
					INNER JOIN {$wpdb->postmeta} m
						ON m.post_id = c.comment_post_ID AND m.meta_key = '_lesson_course'
					WHERE
						c.comment_type = 'sensei_lesson_status' AND
						c.comment_approved != 'in-progress'
					GROUP BY c.user_id, m.meta_value
				) newer_progress
				ON
					c.user_id = newer_progress.user_id AND
					m.meta_value = newer_progress.course_id AND
					comment_date_gmt < newer_progress.max_comment_date_gmt
				WHERE
					c.comment_type = 'sensei_lesson_status' AND
					c.comment_approved = 'in-progress' AND
					newer_progress.max_comment_date_gmt IS NULL AND
					c.comment_ID > %d
				GROUP BY c.user_id, m.meta_value
				ORDER BY id ASC
				LIMIT %d",
				$from_date,
				$to_date,
				$this->last_item,
				$this->batch_size
			)
		);
		$this->is_complete = count( $results ) < $this->batch_size;
		foreach ( $results as $item ) {
			$student_id = $item->user_id;
			$course_id  = $item->course_id;

			/**
			 * Action to run when a student has no progress in a course for X days.
			 *
			 * @since 1.12.0
			 *
			 * @hook sensei_wc_paid_courses_student_no_progress_reminder
			 *
			 * @param {int} $course_id Course ID.
			 * @param {int} $student_id Student ID.
			 * @param {int} $days_without_progress Number of days without progress.
			 */
			do_action( 'sensei_wc_paid_courses_student_no_progress_reminder', $course_id, $student_id, $this->days_without_progress );

			$this->last_item = $item->id;
		}
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
			'last_item'             => $this->last_item,
			'days_without_progress' => $this->days_without_progress,
		];

		if ( self::DEFAULT_BATCH_SIZE !== $this->batch_size ) {
			$args['batch_size'] = $this->batch_size;
		}

		return $args;
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
