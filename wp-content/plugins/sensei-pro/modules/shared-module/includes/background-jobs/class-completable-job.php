<?php
/**
 * File containing the interface Sensei_Pro\Background_Jobs\Completable_Job.
 *
 * @package sensei-pro
 */

namespace Sensei_Pro\Background_Jobs;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface for completable jobs.
 */
interface Completable_Job extends Job {
	/**
	 * Run the job.
	 */
	public function run();

	/**
	 * After the job runs, check to see if it needs to be re-queued for the next batch.
	 *
	 * @return bool
	 */
	public function is_complete();
}
