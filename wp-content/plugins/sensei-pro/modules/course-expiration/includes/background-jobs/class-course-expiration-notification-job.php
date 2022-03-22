<?php
/**
 * File containing the class \Sensei_Pro_Course_Expiration\Background_Jobs\Course_Expiration_Notification_Job.
 *
 * @package sensei-pro
 * @since   1.0.1
 */

namespace Sensei_Pro_Course_Expiration\Background_Jobs;

use Sensei_Pro_Course_Expiration\Course_Expiration;
use Sensei_Pro_Course_Expiration\Emails\Course_Expiration_Email;
use Sensei_Pro\Background_Jobs\Completable_Job;
use Sensei_Pro\Background_Jobs\Scheduler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Course_Expiration_Notification_Job is responsible for send notifications
 * when the access to a course is about to expire.
 *
 * @since 1.0.1
 */
class Course_Expiration_Notification_Job implements Completable_Job {
	const NAME               = 'sensei_wc_paid_courses_course_expiration_notification';
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
		$this->batch_size     = isset( $args['batch_size'] ) ? intval( $args['batch_size'] ) : self::DEFAULT_BATCH_SIZE;
		$this->last_item      = isset( $args['last_item'] ) ? intval( $args['last_item'] ) : 0;
		$this->remaining_days = isset( $args['remaining_days'] ) ? intval( $args['remaining_days'] ) : 1;
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
	 * Run the job.
	 */
	public function run() {
		global $wpdb;

		$current_date   = current_datetime()->setTime( 0, 0, 0 );
		$to_interval    = $this->remaining_days + 1;
		$from_timestamp = $current_date->modify( "{$this->remaining_days} day" )->getTimestamp();
		$to_timestamp   = $current_date->modify( "{$to_interval} day" )->getTimestamp();

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT `meta_id`, `post_id`, `meta_key` FROM {$wpdb->postmeta} WHERE `meta_key` RLIKE %s AND `meta_value` >= %s AND `meta_value` < %s AND `meta_id` > %d ORDER BY meta_id LIMIT %d",
				Course_Expiration::EXPIRATION_TIMESTAMP_COURSE_META_PREFIX,
				$from_timestamp,
				$to_timestamp,
				$this->last_item,
				$this->batch_size
			)
		);

		$this->is_complete = count( $results ) < $this->batch_size;
		$email             = new Course_Expiration_Email( $this->remaining_days );

		foreach ( $results as $item ) {
			$user_id   = intval(
				str_replace(
					Course_Expiration::EXPIRATION_TIMESTAMP_COURSE_META_PREFIX,
					'',
					$item->meta_key
				)
			);
			$course_id = intval( $item->post_id );

			// Send email.
			$email->send( $user_id, $course_id );

			$this->last_item = intval( $item->meta_id );
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
			'last_item'      => $this->last_item,
			'remaining_days' => $this->remaining_days,
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
