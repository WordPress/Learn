<?php
/**
 * File for \Sensei_Pro_Block_Visibility\Types\Schedule class.
 *
 * @package sensei-pro
 * @since 1.5.0
 */

namespace Sensei_Pro_Block_Visibility\Types;

use DateInterval;
use DateTime;
use Sensei_Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class that handles the "Visible withing certain timeframe" visibility type.
 */
class Schedule extends \Sensei_Pro_Block_Visibility\Types\Type {
	/**
	 * Name
	 */
	public function name(): string {
		return 'SCHEDULE';
	}

	/**
	 * Label
	 */
	public function label(): string {
		return __( 'Specific date', 'sensei-pro' );
	}

	/**
	 * Badgle label
	 */
	public function badge_label(): string {
		return __( 'Specific date', 'sensei-pro' );
	}

	/**
	 * Retrieves the description.
	 */
	public function description(): string {
		return __( 'Block is visible only for selected period of time.', 'sensei-pro' );
	}

	/**
	 * Retrieves the date when the current user started the current course.
	 *
	 * @return string|null
	 */
	public function get_course_start_date() {
		$course_id = Sensei_Utils::get_current_course();
		$user_id   = get_current_user_id();

		// If user is not logged in then there is no course start date.
		if ( ! $user_id ) {
			return null;
		}

		// Get the activity/comment data.
		$activity = \Sensei_Utils::user_course_status( $course_id, $user_id );

		if ( ! $activity ) {
			return null;
		}

		if ( isset( $activity->comment_ID ) && intval( $activity->comment_ID ) > 0 ) {
			$course_start_date = get_comment_meta( $activity->comment_ID, 'start', true );
		}

		// Make sure there is a start date attached the users sensei_course_status comment data on the course.
		if ( empty( $course_start_date ) ) {
			return null;
		}

		return $course_start_date;
	}

	/**
	 * Tells if the block is visible or not.
	 *
	 * @param array $visibility_settings The sensei visibility settings.
	 */
	public function is_visible( array $visibility_settings ): bool {
		$current_time = time();

		// Check if start date is satisfied.
		if ( isset( $visibility_settings['startDate'] ) ) {
			$start_date = round( $visibility_settings['startDate'] / 1000 );
			if ( $current_time < $start_date ) {
				return false;
			}
		}

		// Check if end date is satisfied.
		if ( isset( $visibility_settings['endDate'] ) ) {
			$end_date = round( $visibility_settings['endDate'] / 1000 );
			if ( $current_time > $end_date ) {
				return false;
			}
		}

		// Check if the days after course start is satisfied.
		if ( isset( $visibility_settings['daysAfterCourseStart'] ) ) {
			// Get time that is required to pass after course start.
			$days_after_course_start = $visibility_settings['daysAfterCourseStart'];
			$time_after_course_start = new DateInterval( "P{$days_after_course_start}D" );

			// Get the course start date.
			$course_start_date = $this->get_course_start_date();

			// If there is no course start date then not visible.
			if ( ! $course_start_date ) {
				return false;
			}

			$course_start_date            = new DateTime( $course_start_date, wp_timezone() );
			$time_when_block_can_be_shown = $course_start_date->add( $time_after_course_start );
			if ( $current_time < $time_when_block_can_be_shown->getTimestamp() ) {
				return false;
			}
		}

		return true;
	}
}
