<?php
/**
 * File containing the class \Sensei_Pro\Access_Control.
 *
 * @package student-groups
 * @since   1.4.0
 */

namespace Sensei_Pro_Student_Groups;

use Exception;
use Sensei_Course_Enrolment;
use Sensei_Pro\Course_Helper;
use Sensei_Pro_Student_Groups\Enrolment\Groups_Provider;
use Sensei_Pro_Student_Groups\Models\Access_Period;
use Sensei_Pro_Student_Groups\Models\Group_Course;
use Sensei_Pro_Student_Groups\Repositories\Group_Course_Repository;
use WP_Post;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Student groups access control class.
 *
 * Responsible for the group-course access logic.
 *
 * @since 1.4.0
 */
class Access_Control {
	/**
	 * Group course repository.
	 *
	 * @var Group_Course_Repository
	 */
	private $group_course_repository;

	/**
	 * Constructor.
	 *
	 * @since 1.4.0
	 *
	 * @param Group_Course_Repository $group_course_repository
	 */
	public function __construct( Group_Course_Repository $group_course_repository ) {
		$this->group_course_repository = $group_course_repository;
	}

	/**
	 * Initializes the class and adds all filters and actions.
	 *
	 * @since 1.4.0
	 */
	public function init() {
		add_action( 'template_redirect', [ $this, 'show_access_notice' ] );
		add_filter( 'sensei_can_access_course_content', [ $this, 'check_course_content_access' ], 10, 3 );
	}

	/**
	 * Show a notice if the user has no access or the access will expire soon.
	 *
	 * @since  1.4.0
	 * @access private
	 */
	public function show_access_notice() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$user_id   = get_current_user_id();
		$course_id = Course_Helper::get_course_id_for_current_page();

		if (
			! $course_id
			|| ! $this->has_groups_provider_enrolment_only( $user_id, $course_id )
		) {
			return;
		}

		$access_periods        = $this->get_enrolment_access_periods( $user_id, $course_id );
		$closest_access_period = $this->get_closest_access_period( $access_periods );

		if ( ! $closest_access_period ) {
			return;
		}

		if ( $this->is_access_blocked( $user_id, $course_id ) ) {
			$message = $this->get_no_access_message( $closest_access_period );
		} else {
			$message = $this->get_access_expiration_reminder_message( $closest_access_period );
		}

		if ( $message ) {
			Sensei()->notices->add_notice(
				$message,
				'clock'
			);
		}
	}

	/**
	 * Get the no access message.
	 *
	 * @since 1.4.0
	 *
	 * @param Access_Period $access_period
	 *
	 * @return string
	 */
	private function get_no_access_message( Access_Period $access_period ): string {
		$date_format = get_option( 'date_format' );

		if ( $access_period->is_future() ) {
			return sprintf(
				// translators: Placeholder is the date.
				__( 'This course will become available on %s.', 'sensei-pro' ),
				$access_period->get_start_date()->format( $date_format )
			);
		} else {
			return sprintf(
				// translators: Placeholder is the date.
				__( 'Your access expired on %s.', 'sensei-pro' ),
				$access_period->get_end_date()->format( $date_format )
			);
		}
	}

	/**
	 * Get the access expiration reminder message.
	 *
	 * @since 1.4.0
	 *
	 * @param Access_Period $access_period
	 *
	 * @return string
	 */
	private function get_access_expiration_reminder_message( Access_Period $access_period ): string {
		if ( ! $access_period->has_end_date() ) {
			return '';
		}

		$days_remaining = current_datetime()->diff( $access_period->get_end_date() )->days;

		return sprintf(
			// translators: Placeholder is the number of days.
			_n(
				'Your access expires in %d day.',
				'Your access expires in %d days.',
				$days_remaining,
				'sensei-pro'
			),
			$days_remaining
		);
	}

	/**
	 * Check if the user can access the course content based on the group-course access period.
	 *
	 * @since  1.4.0
	 * @access private
	 *
	 * @param bool $can_view_course_content
	 * @param int  $course_id
	 * @param int  $user_id
	 *
	 * @return bool
	 */
	public function check_course_content_access( bool $can_view_course_content, int $course_id, int $user_id ): bool {
		// Skip if the user can't view the content already or there is no user.
		if ( ! $can_view_course_content || ! $user_id ) {
			return $can_view_course_content;
		}

		return ! $this->is_access_blocked( $user_id, $course_id );
	}

	/**
	 * Check if the user course access is blocked by the group-course access period.
	 *
	 * @since 1.4.0
	 *
	 * @param int $user_id
	 * @param int $course_id
	 *
	 * @return bool
	 */
	public function is_access_blocked( int $user_id, int $course_id ): bool {
		$cache_key   = "student_groups_is_access_blocked_{$user_id}_{$course_id}";
		$cache_found = false;
		$blocked     = wp_cache_get( $cache_key, 'sensei_pro', false, $cache_found );

		if ( $cache_found ) {
			return $blocked;
		}

		$blocked = ( function () use ( $user_id, $course_id ) {
			// Power users always have access.
			if ( sensei_all_access( $user_id ) ) {
				return false;
			}

			// Make sure to check access if the only enrolment is by the groups provider.
			if ( ! $this->has_groups_provider_enrolment_only( $user_id, $course_id ) ) {
				return false;
			}

			// Make sure the group courses exist.
			$group_courses = $this->get_enrolment_group_courses( $user_id, $course_id );
			if ( ! $group_courses ) {
				return false;
			}

			// If any group-course provides access, don't block.
			foreach ( $group_courses as $group_course ) {
				if ( $group_course->get_access_period()->is_active() ) {
					return false;
				}
			}

			return true;
		} )();

		wp_cache_set( $cache_key, $blocked, 'sensei_pro' );

		return $blocked;
	}

	/**
	 * Check if the user's enrolments are by the groups provider only.
	 *
	 * @since 1.4.0
	 *
	 * @param int $user_id
	 * @param int $course_id
	 *
	 * @return bool
	 */
	private function has_groups_provider_enrolment_only( int $user_id, int $course_id ): bool {
		$course_enrolment = Sensei_Course_Enrolment::get_course_instance( $course_id );

		// TODO: Remove the falsy @throws docblock from the called method and delete this try/catch block.
		try {
			$enrolment_check_results = $course_enrolment->get_enrolment_check_results( $user_id );
		} catch ( Exception $e ) {
			return false;
		}

		if ( ! $enrolment_check_results ) {
			return false;
		}

		$provider_results         = $enrolment_check_results->get_provider_results();
		$providers_with_enrolment = array_filter( $provider_results );

		return count( (array) $providers_with_enrolment ) === 1
			&& isset( $providers_with_enrolment[ Groups_Provider::instance()->get_id() ] );
	}

	/**
	 * Get the group courses for the enrolment by the groups provider.
	 *
	 * @since 1.4.0
	 *
	 * @param int $user_id
	 * @param int $course_id
	 *
	 * @return Group_Course[]
	 */
	private function get_enrolment_group_courses( int $user_id, int $course_id ): array {
		$groups = Groups_Provider::instance()->get_enrolment_groups( $user_id, $course_id );

		$group_courses = [];
		foreach ( $groups as $group_id ) {
			$group_course = $this->group_course_repository->find_by_group_and_course( $group_id, $course_id );

			if ( $group_course ) {
				$group_courses[] = $group_course;
			}
		}

		return $group_courses;
	}

	/**
	 * Get the group courses for the enrolment by the groups provider.
	 *
	 * @since 1.4.0
	 *
	 * @param int $user_id
	 * @param int $course_id
	 *
	 * @return Access_Period[]
	 */
	private function get_enrolment_access_periods( int $user_id, int $course_id ): array {
		$group_courses = $this->get_enrolment_group_courses( $user_id, $course_id );

		return array_map(
			function ( $group_course ) {
				return $group_course->get_access_period();
			},
			$group_courses
		);
	}

	/**
	 * Get the most relevant enrolment group-course access period based on the access period status.
	 *
	 * @param Access_Period[] $access_periods
	 *
	 * @return Access_Period|null
	 */
	private function get_closest_access_period( array $access_periods ) {
		if ( ! $access_periods ) {
			return null;
		}

		return array_reduce(
			$access_periods,
			function ( Access_Period $carry_access_period = null, Access_Period $access_period ) {
				if ( ! $carry_access_period ) {
					return $access_period;
				}

				if (
					( $access_period->is_active() || $access_period->has_ended() )
					&& $access_period->get_end_date() > $carry_access_period->get_end_date()
				) {
					return $access_period;
				} elseif (
					$access_period->is_future()
					&& $access_period->get_start_date() < $carry_access_period->get_start_date()
				) {
					return $access_period;
				}

				return $carry_access_period;
			}
		);
	}
}
