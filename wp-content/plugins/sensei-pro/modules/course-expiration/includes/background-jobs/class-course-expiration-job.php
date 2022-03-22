<?php
/**
 * File containing the class \Sensei_Pro_Course_Expiration\Background_Jobs\Course_Expiration_Job.
 *
 * @package sensei-pro
 * @since   1.0.1
 */

namespace Sensei_Pro_Course_Expiration\Background_Jobs;

use Sensei_Pro_Course_Expiration\Course_Expiration;
use Sensei_Pro\Background_Jobs\Scheduler;
use Sensei_Pro\Background_Jobs\Completable_Job;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Course_Expiration_Job is responsible for expiring learner
 * enrollment in courses when the expiration date passes.
 *
 * @since 1.0.1
 */
class Course_Expiration_Job implements Completable_Job {
	const NAME               = 'sensei_wc_paid_courses_expires_course_learner_enrollments';
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
	 * Course_Expiration_Job constructor.
	 *
	 * @param array $args Arguments to run for the job.
	 */
	public function __construct( $args ) {
		$this->batch_size = isset( $args['batch_size'] ) ? intval( $args['batch_size'] ) : self::DEFAULT_BATCH_SIZE;
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

		$current_date = current_datetime()->getTimestamp();

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT `post_id`, `meta_key` FROM {$wpdb->postmeta} WHERE `meta_key` RLIKE %s AND `meta_value` <= %s ORDER BY meta_id LIMIT %d",
				Course_Expiration::EXPIRATION_TIMESTAMP_COURSE_META_PREFIX,
				$current_date,
				$this->batch_size
			)
		);

		$this->is_complete = count( $results ) < $this->batch_size;

		foreach ( $results as $item ) {
			$user_id   = intval(
				str_replace(
					Course_Expiration::EXPIRATION_TIMESTAMP_COURSE_META_PREFIX,
					'',
					$item->meta_key
				)
			);
			$course_id = intval( $item->post_id );

			Course_Expiration::instance()->expire_access( $user_id, $course_id );
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
		$args = [];

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
