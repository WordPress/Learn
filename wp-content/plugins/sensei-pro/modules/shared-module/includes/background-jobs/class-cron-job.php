<?php
/**
 * File containing the interface Sensei_Pro\Background_Jobs\Cron_Job.
 *
 * @package sensei-pro
 */

namespace Sensei_Pro\Background_Jobs;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Interface for cron jobs.
 */
interface Cron_Job extends Job {
	/**
	 * Get the cron schedule. A cron-link schedule string.
	 *
	 * @return string
	 */
	public function get_schedule();
}
