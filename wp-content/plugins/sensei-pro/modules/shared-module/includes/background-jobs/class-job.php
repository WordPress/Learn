<?php
/**
 * File containing the interface Sensei_Pro\Background_Jobs\Job.
 *
 * @package sensei-pro
 */

namespace Sensei_Pro\Background_Jobs;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Interface for jobs.
 */
interface Job {
	/**
	 * Get the action name for the scheduled job.
	 *
	 * @return string
	 */
	public function get_name();

	/**
	 * Get the arguments to run with the job.
	 *
	 * @return array
	 */
	public function get_args();

	/**
	 * Get the group name. No need to prefix with `sensei-wc-paid-courses`.
	 *
	 * @return string
	 */
	public function get_group();
}
