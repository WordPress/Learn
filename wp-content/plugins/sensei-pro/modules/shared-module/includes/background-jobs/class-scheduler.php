<?php
/**
 * File containing the class \Sensei_Pro\Background_Jobs\Scheduler.
 *
 * @package sensei-pro
 * @since   1.0.1
 */

namespace Sensei_Pro\Background_Jobs;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Handles scheduling of background jobs.
 *
 * @since 1.0.1
 */
class Scheduler {
	const ACTION_SCHEDULER_GROUP_PREFIX = 'sensei-wc-paid-courses-';

	/**
	 * Singleton instance.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Provides singleton instance.
	 *
	 * @return self
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Class constructor. Private so it can only be initialized internally.
	 */
	private function __construct() {}

	/**
	 * Handle the scheduling of a job that might need to be rescheduled.
	 *
	 * @param Completable_Job $job                 Job object.
	 * @param callable|null   $completion_callback Optional callback to call upon completion of a job.
	 */
	public function handle_self_scheduling_job( Completable_Job $job, $completion_callback = null ) {
		// Ensure the job is still scheduled.
		$this->schedule_single_job( $job, true );

		$job->run();

		if ( $job->is_complete() ) {
			$this->cancel_scheduled_job( $job );

			if ( is_callable( $completion_callback ) ) {
				call_user_func( $completion_callback );
			}
		}
	}

	/**
	 * Handle the scheduling of a job that might need to be rescheduled after a run.
	 * It's useful for cases where the job changes the arguments.
	 *
	 * @since 1.0.1
	 *
	 * @param Completable_Job $job                 Job object.
	 * @param callable|null   $completion_callback Optional callback to call upon completion of a job.
	 */
	public function handle_self_scheduling_job_after_run( Completable_Job $job, $completion_callback = null ) {
		$job->run();

		if ( $job->is_complete() ) {
			if ( is_callable( $completion_callback ) ) {
				call_user_func( $completion_callback );
			}
		} else {
			$this->schedule_single_job( $job, true );
		}
	}

	/**
	 * Schedule a single job to run as soon as possible.
	 *
	 * @param Completable_Job $job                Job to schedule.
	 * @param bool            $reschedule_running If true, reschedule if it is currently running.
	 */
	public function schedule_single_job( Completable_Job $job, $reschedule_running = false ) {
		$name  = $job->get_name();
		$args  = [ $job->get_args() ];
		$group = $this->get_job_group( $job );

		$next_scheduled_action = \as_next_scheduled_action( $name, $args, $group );

		if (
			! $next_scheduled_action // Not scheduled.
			|| ( // Currently running.
				$reschedule_running
				&& true === $next_scheduled_action
			)
		) {
			\as_schedule_single_action( time(), $name, $args, $group );
		}
	}

	/**
	 * Schedule a cron job.
	 *
	 * @param Cron_Job $job               Job to schedule.
	 * @param bool     $apply_wp_timezone Whether it should apply the WordPress timezone.
	 */
	public function schedule_cron_job( Cron_Job $job, bool $apply_wp_timezone = true ) {
		$schedule = $job->get_schedule();
		$name     = $job->get_name();
		$args     = [ $job->get_args() ];
		$group    = $this->get_job_group( $job );

		// Apply timezone to schedule.
		if ( $apply_wp_timezone ) {
			$schedule = $this->apply_wp_timezone_to_cron_schedule( $schedule );
		}

		$pending_actions  = $this->get_pending_actions(
			[
				'hook'  => $name,
				'args'  => $args,
				'group' => $group,
			]
		);
		$current_schedule = null;

		// Cancel current job if pending with different schedule.
		if ( ! empty( $pending_actions ) ) {
			$current_schedule = $pending_actions[0]->get_schedule()->get_recurrence();

			if ( $schedule !== $current_schedule ) {
				$this->cancel_scheduled_job( $job );
			}
		}

		if ( empty( $pending_actions ) || $schedule !== $current_schedule ) {
			\as_schedule_cron_action( time(), $schedule, $name, $args, $group );
		}
	}

	/**
	 * Apply WP timezone to a cron schedule.
	 *
	 * @param string $schedule A cron-link schedule string.
	 *
	 * @return string Schedule with timezone applied.
	 */
	private function apply_wp_timezone_to_cron_schedule( $schedule ) {
		$gmt_offset = get_option( 'gmt_offset' );

		if ( false === $gmt_offset ) {
			return $schedule;
		}

		$schedule_parts    = explode( ' ', $schedule );
		$minutes           = $gmt_offset * 60;
		$time              = current_datetime()->setTime( $schedule_parts[1], $schedule_parts[0], 0 )->modify( "-{$minutes} minutes" );
		$hour              = (int) $time->format( 'G' );
		$minute            = (int) $time->format( 'i' );
		$schedule_parts[0] = $minute;
		$schedule_parts[1] = $hour;

		return implode( ' ', $schedule_parts );
	}

	/**
	 * An abstraction for the `as_unschedule_all_actions` function.
	 *
	 * @param string $hook  The hook that the job will trigger.
	 * @param array  $args  Args that would have been passed to the job.
	 * @param string $group Group name (without the prefix).
	 */
	public function unschedule_all_actions( $hook, $args, $group ) {
		\as_unschedule_all_actions( $hook, $args, $this->get_group_full_name( $group ) );
	}

	/**
	 * Cancel a scheduled job.
	 *
	 * @param Job $job Job to schedule.
	 */
	public function cancel_scheduled_job( Job $job ) {
		$name = $job->get_name();
		$args = [ $job->get_args() ];

		$this->unschedule_all_actions( $name, $args, $job->get_group() );
	}

	/**
	 * Stops all jobs that this class is responsible for.
	 */
	public function cancel_all_jobs() {
		$pending_actions = $this->get_pending_actions();
		foreach ( $pending_actions as $action ) {
			$this->unschedule_all_actions( $action->get_hook(), $action->get_args(), $action->get_group() );
		}
	}

	/**
	 * Get the pending ActionScheduler actions for this plugin.
	 *
	 * @param array $args Query args to pass along to \as_get_scheduled_actions.
	 *
	 * @return \ActionScheduler_Action[]
	 */
	public function get_pending_actions( $args = [] ) {
		$args['status']   = \ActionScheduler_Store::STATUS_PENDING;
		$args['per_page'] = -1;

		if ( isset( $args['group'] ) ) {
			$args['group'] = $this->get_group_full_name( $args['group'] );
		}

		/**
		 * Pending job actions.
		 *
		 * @var \ActionScheduler_Action[] $pending_actions
		 */
		$pending_actions = \as_get_scheduled_actions( $args );
		$group_prefix    = self::ACTION_SCHEDULER_GROUP_PREFIX;
		foreach ( $pending_actions as $index => $action ) {
			if ( 0 !== strpos( $action->get_group(), $group_prefix ) ) {
				unset( $pending_actions[ $index ] );
			}
		}

		return array_values( $pending_actions );
	}

	/**
	 * Get the prefixed job group.
	 *
	 * @param Job $job Job object.
	 *
	 * @return string
	 */
	private function get_job_group( Job $job ) {
		return $this->get_group_full_name( $job->get_group() );
	}

	/**
	 * Generate the full group name.
	 *
	 * @param string $group Group name.
	 *
	 * @return string
	 */
	private function get_group_full_name( $group ) {
		if ( 0 === strpos( $group, self::ACTION_SCHEDULER_GROUP_PREFIX ) ) {
			return $group;
		}

		return self::ACTION_SCHEDULER_GROUP_PREFIX . $group;
	}
}
