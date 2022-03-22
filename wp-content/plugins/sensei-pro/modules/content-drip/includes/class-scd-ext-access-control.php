<?php
/**
 * File containing the class Scd_Ext_Access_Control.
 *
 * @package sensei-pro
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sensei Content Drip ( scd ) Extension Access Control class
 *
 * The class controls all frontend activity relating to sensei lessons.
 *
 * @package WordPress
 * @subpackage Sensei Content Drip
 * @category Core
 * @author WooThemes
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 * - drip_message
 * - __construct
 * - is_lesson_access_blocked
 * - is_absolute_drip_type_content_blocked
 * - is_dynamic_drip_type_content_blocked
 * - get_lesson_drip_date
 */
class Scd_Ext_Access_Control {

	/**
	 * The message shown in place of lesson content
	 *
	 * @var    string
	 * @access protected
	 * @since  1.0.0
	 */
	protected $drip_message;

	/**
	 * Constructor function
	 */
	public function __construct() {
		// set a formatted  message shown to user when the content has not yet dripped.
		$this->message_format = Sensei_Content_Drip()->utils->check_for_translation(
			'This lesson will become available on [date].',
			'scd_drip_message'
		);

		// Handle lessons for which to block access through Sensei.
		add_filter( 'sensei_can_user_view_lesson', [ $this, 'can_user_view_lesson' ], 10, 3 );
	}

	/**
	 * Check if  the lesson can be made available to the the user at this point
	 * according to the drip meta data
	 *
	 * @since  1.0.0
	 * @param  int $lesson_id
	 * @return bool $content_access_blocked
	 */
	public function is_lesson_access_blocked( $lesson_id ) {
		$content_access_blocked = false;
		$lesson_course_id       = Sensei()->lesson->get_course_id( $lesson_id );
		$is_course_teacher      = $this->is_course_teacher( $lesson_course_id );

		// Return drip not active for the following conditions.
		if ( $this->is_super_admin() || $is_course_teacher || empty( $lesson_id ) || 'lesson' !== get_post_type( $lesson_id )
			|| Sensei_Utils::user_completed_lesson( $lesson_id, get_current_user_id() ) ) {
			return false;
		}

		// Check if user has started the course.
		if ( Sensei_Content_Drip::instance()->is_legacy_enrolment() ) {
			$user_started_course = Sensei_Utils::user_started_course( $lesson_course_id, get_current_user_id() );
		} else {
			$user_started_course = Sensei_Course::is_user_enrolled( $lesson_course_id );
		}

		// get the lessons drip data if any.
		$drip_type = get_post_meta( $lesson_id, '_sensei_content_drip_type', true );

		// check if the content should be dripped.
		if ( empty( $drip_type ) || 'none' === $drip_type ) {
			$content_access_blocked = false;
		} elseif ( 'absolute' === $drip_type ) {
			$content_access_blocked = $this->is_absolute_drip_type_content_blocked( $lesson_id );
		} elseif ( 'dynamic' === $drip_type ) {
			// If the user is not taking the course, block it.
			if ( $user_started_course ) {
				$content_access_blocked = $this->is_dynamic_drip_type_content_blocked( $lesson_id );
			} else {
				$content_access_blocked = true;
			}
		}

		/**
		 * Filter scd_is_drip_active
		 * Filter scd_lesson_content_access_blocked
		 *
		 * @param boolean $content_access_blocked
		 *
		 * Filter the boolean value returned. The value tells us if a drip is active on the given lesson
		 */
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Backwards compatibility
		$content_access_blocked = apply_filters( 'scd_is_drip_active', $content_access_blocked, $lesson_id );
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Backwards compatibility
		$content_access_blocked = apply_filters( 'scd_lesson_content_access_blocked', $content_access_blocked, $lesson_id );

		return $content_access_blocked;
	}

	/**
	 * Indicates whether the lesson should be blocked directly from Sensei.
	 * This is used for the Sensei filter `sensei_can_user_view_lesson`. See
	 * method `can_user_view_lesson`.
	 *
	 * This function is for handling the case where a user is not taking a
	 * course that has lessons with dripped content. If the lesson has not been
	 * dripped and the user has not started the course, it should be blocked by
	 * Sensei, rather than by Content Drip.
	 *
	 * @since  1.0.9
	 * @param  int $lesson_id
	 * @param  int $user_id
	 * @return bool true if Sensei should block access, false otherwise.
	 */
	public function sensei_should_block_lesson( $lesson_id, $user_id ) {
		$lesson_course_id = Sensei()->lesson->get_course_id( $lesson_id );

		if ( Sensei_Content_Drip::instance()->is_legacy_enrolment() ) {
			$is_enrolled = Sensei_Utils::user_started_course( $lesson_course_id, get_current_user_id() );
		} else {
			$is_enrolled = Sensei_Course::is_user_enrolled( $lesson_course_id );
		}

		// Block the lesson only if user is not enrolled and it hasn't dripped yet.
		return ! $is_enrolled && $this->is_lesson_access_blocked( $lesson_id );
	}

	/**
	 * Used in the sensei filter `sensei_can_user_view_lesson`. See method
	 * `sensei_should_block_lesson`.
	 *
	 * @since  1.0.9
	 * @param bool $can_user_view_lesson
	 * @param int  $lesson_id
	 * @param int  $user_id
	 * @return bool true if the user access should be allowed, false otherwise.
	 */
	public function can_user_view_lesson( $can_user_view_lesson, $lesson_id, $user_id ) {
		return $can_user_view_lesson && ! $this->sensei_should_block_lesson( $lesson_id, $user_id );
	}

	/**
	 * Check specifically if the absolute drip type is active on this lesson
	 * depending only on the date stored on this lesson
	 *
	 * @since  1.0.0
	 * @param  array $lesson_id
	 * @return bool $active
	 */
	public function is_absolute_drip_type_content_blocked( $lesson_id ) {
		// Setup the default drip status.
		$access_blocked = false;

		// Get the user details.
		$current_user = wp_get_current_user();
		$user_id      = $current_user->ID;

		// Convert string dates to date object.
		$lesson_drip_date = $this->get_lesson_drip_date( $lesson_id, $user_id );
		$today            = Sensei_Content_Drip()->utils->current_datetime()->setTime( 0, 0, 0 );

		if ( ! $lesson_drip_date ) {
			return $access_blocked;
		}

		/**
		 * Compare dates
		 *
		 * If lesson drip date is greater than the today
		 * the drip date ist still active and lesson content should be hidden
		 */
		if ( $lesson_drip_date->getTimestamp() > $today->getTimestamp() ) {
			$access_blocked = true;
		}

		return $access_blocked;
	}

	/**
	 * Check specifically if the dynamic drip content is active on this lesson
	 * depending only on the time span specified by the user
	 *
	 * @since  1.0.0
	 * @param  string $lesson_id
	 * @return bool $active
	 */
	public function is_dynamic_drip_type_content_blocked( $lesson_id ) {
		// Setup the default drip status.
		$access_blocked = false;

		// Get the lessons data.
		$dripped_data = Sensei_Content_Drip()->lesson_admin->get_lesson_drip_data( $lesson_id );

		// Confirm that all needed data is in place otherwise this content will be available.
		if ( empty( $dripped_data )
			|| empty( $dripped_data['_sensei_content_drip_details_date_unit_type'] )
			|| empty( $dripped_data['_sensei_content_drip_details_date_unit_amount'] ) ) {
			// default set to false.
			return $access_blocked;
		}

		// If the user is not logged in ignore this type and exit.
		if ( ! is_user_logged_in() ) {
			return $access_blocked;
		}

		// Get the user details.
		$current_user = wp_get_current_user();
		$user_id      = $current_user->ID;

		// Get the drip details array data.
		$unit_type   = $dripped_data['_sensei_content_drip_details_date_unit_type'];
		$unit_amount = $dripped_data['_sensei_content_drip_details_date_unit_amount'];

		// If the data is not correct then the drip lesson should be shown.
		if ( ! in_array( $unit_type, [ 'day', 'week', 'month' ], true ) || ! is_numeric( $unit_amount ) ) {
			return $access_blocked;
		}

		$lesson_becomes_available_date = $this->get_lesson_drip_date( $lesson_id, $user_id );
		$today                         = Sensei_Content_Drip()->utils->current_datetime()->setTime( 0, 0, 0 );

		if ( ! $lesson_becomes_available_date ) {
			return $access_blocked;
		}

		/**
		 * Compare dates
		 *
		 * If lesson_becomes_available_date is greater than the today
		 * the drip date ist still active and lesson content should be hidden
		 */
		if ( $lesson_becomes_available_date->getTimestamp() > $today->getTimestamp() ) {
			$access_blocked = true;
		}

		return $access_blocked;
	}

	/**
	 * Determine the drip type and return the date the lesson will become available
	 *
	 * @param  string $lesson_id
	 * @param  string $user_id
	 * @return DateTimeImmutable|false drip_date format yyyy-mm-dd
	 */
	public function get_lesson_drip_date( $lesson_id, $user_id = '' ) {
		// Setup the basics, drip date default return will be false on error.
		$drip_date = false;

		if ( empty( $lesson_id ) ) {
			return $drip_date;
		}

		// Get the post meta drip type.
		$drip_type = get_post_meta( $lesson_id, '_sensei_content_drip_type', true );

		// We need a user id if the drip type is dynamic.
		if ( 'dynamic' === $drip_type && empty( $user_id ) ) {
			return false;
		}

		if ( 'absolute' === $drip_type ) {
			$drip_date = Scd_Ext_Utils::date_from_datestring_or_timestamp( $lesson_id );
		} elseif ( 'dynamic' === $drip_type ) {
			// Get the drip details array data.
			$unit_type   = get_post_meta( $lesson_id, '_sensei_content_drip_details_date_unit_type', true );
			$unit_amount = get_post_meta( $lesson_id, '_sensei_content_drip_details_date_unit_amount', true );

			// Get the lesson course.
			$course_id = get_post_meta( $lesson_id, '_lesson_course', true );

			// The lesson must belong to a course for this drip type to be active.
			if ( empty( $course_id ) ) {
				return false;
			}

			// Get the activity/comment data.
			$activity = Sensei_Utils::user_course_status( $course_id, $user_id );

			if ( isset( $activity->comment_ID ) && intval( $activity->comment_ID ) > 0 ) {
				$course_start_date = get_comment_meta( $activity->comment_ID, 'start', true );
			}

			// Make sure there is a start date attached the users sensei_course_status comment data on the course.
			if ( ! empty( $course_start_date ) ) {
				$user_course_start_date_string = $course_start_date;

				// Sensei LMS stores course start date in PHP's timezone.
				$timezone = ( new DateTime() )->getTimezone();
			} elseif ( isset( $activity->comment_date_gmt ) && ! empty( $activity->comment_date_gmt ) ) {
				// This is for backwards compatibility for users who have not yet
				// updated to the new course status data format since sensei version 1.7.0.
				$user_course_start_date_string = $activity->comment_date_gmt;

				// We're using the UTC timezone from the course status comment record.
				$timezone = new DateTimeZone( 'UTC' );
			} else {
				return false;
			}

			// Create an object which the interval will be added to and add the interval.
			$user_course_start_date = new DateTimeImmutable( $user_course_start_date_string, $timezone );

			// Standardize this to the WP timezone.
			$user_course_start_date = $user_course_start_date->setTimezone( Sensei_Content_Drip()->utils->wp_timezone() );

			// Create a date interval object to determine when the lesson should become available.
			$unit_type_first_letter_uppercase = strtoupper( substr( $unit_type, 0, 1 ) );
			$interval_to_lesson_availability  = new DateInterval( 'P' . $unit_amount . $unit_type_first_letter_uppercase );

			// Add the interval to the start date to get the date this lesson should become available.
			$drip_date = $user_course_start_date->add( $interval_to_lesson_availability );
		}

		// Reset time to the beginning of the day in WP timezone.
		return $drip_date->setTime( 0, 0, 0 );
	}

	/**
	 * Checks If a User is SuperAdmin, compatible with WP >= 4.8.0
	 *
	 * @return bool
	 */
	private function is_super_admin() {
		global $wp_version;
		if ( is_multisite() && version_compare( $wp_version, '4.8', '>=' ) ) {
			// See https://make.wordpress.org/core/2017/05/22/multisite-focused-changes-in-4-8/.
			// And https://core.trac.wordpress.org/ticket/39205#comment:13.
			// `upgrade_netrowk` is the new more granular way to check for super_admins.
			return current_user_can( 'upgrade_network' );
		}

		return is_super_admin();
	}

	/**
	 * Checks If a User is teacher of a course
	 *
	 * @param  string $course_id
	 * @return bool
	 */
	private function is_course_teacher( $course_id ) {
		if ( ! class_exists( 'Sensei_Teacher' ) ) {
			return false;
		}

		$user_id = get_current_user_id();

		if ( ! Sensei_Teacher::is_a_teacher( $user_id ) ) {
			return false;
		}

		$teacher_courses_id = Sensei()->teacher->get_teacher_courses( $user_id, true );

		// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict -- $course_id is string and $teacher_courses_id is int[]
		return in_array( $course_id, $teacher_courses_id );
	}

}
