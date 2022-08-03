<?php
/**
 * File containing the class \Sensei_Pro_Student_Groups\Enrolment\Groups_Provider.
 *
 * @package student-groups
 */

namespace Sensei_Pro_Student_Groups\Enrolment;

use Sensei_Course_Enrolment;
use Sensei_Course_Enrolment_Manager;
use Sensei_Course_Enrolment_Provider_Interface;

/**
 * Class Groups_Provider.
 *
 * @since 1.4.0
 */
class Groups_Provider implements Sensei_Course_Enrolment_Provider_Interface {
	const DATA_KEY_ENROLMENT_GROUPS_STATE = 'enrolment_groups_status';

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
	 * The enrolment provider ID.
	 *
	 * @return string
	 */
	public function get_id() {
		return 'student-groups';
	}

	/**
	 * Returns the name of the enrolment provider.
	 *
	 * @return string
	 */
	public function get_name() {
		return esc_html__( 'Student Groups', 'sensei-pro' );
	}

	/**
	 * Check if this course enrolment provider manages enrolment for a particular course.
	 *
	 * @param int $course_id Course post ID.
	 *
	 * @return bool
	 */
	public function handles_enrolment( $course_id ) {
		return true;
	}

	/**
	 * Gets the version of the enrolment provider logic. If this changes, enrolment will be recalculated.
	 *
	 * @return int|string
	 */
	public function get_version() {
		return '1.0.0';
	}

	/**
	 * Check if this course enrolment provider is enrolling a user to a course.
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course post ID.
	 *
	 * @return bool  `true` if this provider enrols the student and `false` if not.
	 */
	public function is_enrolled( $user_id, $course_id ) {
		$enrolment_state = $this->get_enrolment_state( $user_id, $course_id );

		return ! empty( $enrolment_state );
	}

	/**
	 * Enrols a learner in a course and saves group ID in the state.
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course post ID.
	 * @param int $group_id  Group ID.
	 *
	 * @return bool
	 */
	public function enrol_learner( $user_id, $course_id, $group_id ) {
		$enrolment_state = $this->get_enrolment_state( $user_id, $course_id );
		// If the group exists in the state, then the learner is already enrolled.
		if ( ! empty( $enrolment_state ) && isset( $enrolment_state[ $group_id ] ) ) {
			return true;
		}

		// Otherwise, add information about the group to the state.
		$enrolment_state[ $group_id ] = wp_date( 'Y-m-d H:i:s' );
		$this->set_enrolment_state( $user_id, $course_id, $enrolment_state );

		// Restore the learner if they were previously removed from the course.
		$this->restore_learner_if_removed( $user_id, $course_id );

		// Trigger enrolment check to create actual enrolment if needed.
		Sensei_Course_Enrolment_Manager::trigger_course_enrolment_check( $user_id, $course_id );

		if ( ! $this->is_enrolled( $user_id, $course_id ) ) {
			return false;
		}

		/**
		 * Fire action when a learner is provided with student groups enrolment.
		 *
		 * @since 1.4.0
		 *
		 * @param int $user_id   User ID.
		 * @param int $course_id Course post ID.
		 * @param int $group_id  Group ID.
		 */
		do_action( 'sensei_pro_student_groups_enrolment_learner_enrolled', $user_id, $course_id, $group_id );

		return true;
	}

	/**
	 * Withdraw enrolment for a learner in a course via a given group.
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course post ID.
	 * @param int $group_id  Group ID.
	 *
	 * @return bool
	 */
	public function withdraw_learner( $user_id, $course_id, $group_id ) {
		$enrolment_state = $this->get_enrolment_state( $user_id, $course_id );
		if ( empty( $enrolment_state ) || ! isset( $enrolment_state[ $group_id ] ) ) {
			return true;
		}

		// Remove the group from the state.
		unset( $enrolment_state[ $group_id ] );
		$this->set_enrolment_state( $user_id, $course_id, $enrolment_state );

		// Trigger enrolment check to remove actual enrolment if needed.
		\Sensei_Course_Enrolment_Manager::trigger_course_enrolment_check( $user_id, $course_id );

		$enrolment_state = $this->get_enrolment_state( $user_id, $course_id );
		if ( ! empty( $enrolment_state ) && isset( $enrolment_state[ $group_id ] ) ) {
			return false;
		}

		/**
		 * Fire action when a learner's student groups enrolment is withdrawn by group.
		 *
		 * @since 1.4.0
		 *
		 * @param int $user_id   User ID.
		 * @param int $course_id Course post ID.
		 * @param int $group_id  Group ID.
		 */
		do_action( 'sensei_pro_student_groups_enrolment_learner_withdrawn_by_group', $user_id, $course_id, $group_id );

		return true;
	}

	/**
	 * Reset enrolment state for a learner in a course.
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course post ID.
	 *
	 * @return bool
	 */
	public function reset_enrolment_state( $user_id, $course_id ) {
		$enrolment_state = $this->get_enrolment_state( $user_id, $course_id );
		if ( empty( $enrolment_state ) ) {
			return true;
		}

		// Set empty state.
		$this->set_enrolment_state( $user_id, $course_id, [] );

		// Trigger enrolment check to remove actual enrolment if needed.
		\Sensei_Course_Enrolment_Manager::trigger_course_enrolment_check( $user_id, $course_id );

		$enrolment_state = $this->get_enrolment_state( $user_id, $course_id );
		if ( ! empty( $enrolment_state ) ) {
			return false;
		}

		/**
		 * Fire action when the enrolment state is reset.
		 *
		 * @since 1.4.0
		 *
		 * @param int $user_id   User ID.
		 * @param int $course_id Course post ID.
		 */
		do_action( 'sensei_pro_student_groups_reset_enrolment_state', $user_id, $course_id );

		return true;
	}

	/**
	 * Set the enrolment state for the current provider.
	 *
	 * @param int   $user_id         User ID.
	 * @param int   $course_id       Course post ID.
	 * @param array $enrolment_state Enrolment state for groups to set for the user and course.
	 */
	private function set_enrolment_state( $user_id, $course_id, $enrolment_state ) {
		$course_enrolment = Sensei_Course_Enrolment::get_course_instance( $course_id );
		$provider_state   = $course_enrolment->get_provider_state( $this, $user_id );

		$provider_state->set_stored_value( self::DATA_KEY_ENROLMENT_GROUPS_STATE, $enrolment_state );
		$provider_state->save();
	}

	/**
	 * Get the enrolment state for the current provider.
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course post ID.
	 *
	 * @return array
	 */
	private function get_enrolment_state( $user_id, $course_id ) {
		$course_enrolment = Sensei_Course_Enrolment::get_course_instance( $course_id );
		$provider_state   = $course_enrolment->get_provider_state( $this, $user_id );

		$enrolment_state = $provider_state->get_stored_value( self::DATA_KEY_ENROLMENT_GROUPS_STATE );

		// Check if the initial enrolment state hasn't been set yet.
		if ( null === $enrolment_state ) {
			$enrolment_state = [];
			$this->set_enrolment_state( $user_id, $course_id, $enrolment_state );
		}

		return $enrolment_state;
	}

	/**
	 * Get the enrolment groups for the current provider.
	 *
	 * @since 1.4.0
	 *
	 * @param int $user_id
	 * @param int $course_id
	 *
	 * @return array The group IDs.
	 */
	public function get_enrolment_groups( $user_id, $course_id ): array {
		$enrolment_state = $this->get_enrolment_state( $user_id, $course_id );

		if ( ! $enrolment_state ) {
			return [];
		}

		$group_ids = array_keys( $enrolment_state );

		return $group_ids;
	}

	/**
	 * Restores a learner's enrolment if it has been previously removed.
	 *
	 * @param int $user_id   The user id.
	 * @param int $course_id The course id.
	 */
	private function restore_learner_if_removed( $user_id, $course_id ) {
		if ( ! $this->is_learner_removal_enabled() ) {
			return;
		}

		$enrolment = \Sensei_Course_Enrolment::get_course_instance( $course_id );
		if (
			$this->handles_enrolment( $course_id ) &&
			$enrolment->is_learner_removed( $user_id ) &&
			$this->is_enrolled( $user_id, $course_id )
		) {
			$enrolment->restore_learner( $user_id );
		}
	}

	/**
	 * Returns true of learner removal is enabled.
	 *
	 * @return bool
	 */
	private function is_learner_removal_enabled() {
		return method_exists( 'Sensei_Course_Enrolment', 'remove_learner' );
	}
}
