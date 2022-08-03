<?php
/**
 * File containing the class \Sensei_Pro_Student_Groups\Enrolment\Enrolment_Handler.
 *
 * @package student-groups
 */

namespace Sensei_Pro_Student_Groups\Enrolment;

use Sensei_Pro_Student_Groups\Repositories\Group_Course_Repository;
use Sensei_Pro_Student_Groups\Repositories\Group_Student_Repository;

/**
 * Class for handling enrolment.
 *
 * @since 1.4.0
 */
class Enrolment_Handler {
	/**
	 * Group course repository.
	 *
	 * @var Group_Course_Repository
	 */
	private $group_course_repository;

	/**
	 * Group student repository.
	 *
	 * @var Group_Student_Repository
	 */
	private $group_student_repository;

	/**
	 * Student groups enrolment provider.
	 *
	 * @var Groups_Provider
	 */
	private $enrolment_provider;

	/**
	 * Enrolment_Handler constructor.
	 *
	 * @param Group_Course_Repository  $group_course_repository  Group course repository.
	 * @param Group_Student_Repository $group_student_repository Group student repository.
	 * @param Groups_Provider          $enrolment_provider       Student groups enrolment provider.
	 */
	public function __construct(
		Group_Course_Repository $group_course_repository,
		Group_Student_Repository $group_student_repository,
		Groups_Provider $enrolment_provider
	) {
		$this->group_course_repository  = $group_course_repository;
		$this->group_student_repository = $group_student_repository;
		$this->enrolment_provider       = $enrolment_provider;
	}

	/**
	 * Enroll students into added course.
	 *
	 * @param int $group_id
	 * @param int $course_id
	 */
	public function enroll_group_students_in_course( $group_id, $course_id ) {
		$group_students = $this->group_student_repository->find_group_students( $group_id );
		foreach ( $group_students as $student_id ) {
			$this->enrolment_provider->enrol_learner( $student_id, $course_id, $group_id );
		}
	}

	/**
	 * Enroll added student to group courses.
	 *
	 * @param int $group_id
	 * @param int $student_id
	 */
	public function enroll_student_in_group_courses( $group_id, $student_id ) {
		$group_courses = $this->group_course_repository->find_by_group( $group_id );
		foreach ( $group_courses as $group_course ) {
			$this->enrolment_provider->enrol_learner( $student_id, $group_course->get_course_id(), $group_id );
		}
	}

	/**
	 * Remove students from group courses if students are removed from group.
	 *
	 * @param int   $group_id
	 * @param int[] $student_ids
	 */
	public function remove_students_from_group_courses( $group_id, $student_ids ) {
		// phpcs:ignore WordPress.Security.NonceVerification
		$remove_enrolments = isset( $_GET['remove_enrolments'] ) && 'false' !== $_GET['remove_enrolments'] && boolval( $_GET['remove_enrolments'] );

		if ( ! $remove_enrolments ) {
			return;
		}
		$group_courses = $this->group_course_repository->find_by_group( $group_id );
		foreach ( $group_courses as $group_course ) {
			foreach ( $student_ids as $student_id ) {
				$this->remove_enrolment( $student_id, $group_course->get_course_id(), false );
			}
		}
	}

	/**
	 * Remove the student from the course
	 *
	 * @param int  $user_id     User ID.
	 * @param int  $course_id   Course ID.
	 * @param bool $is_enrolled New enrolment status.
	 */
	public function remove_enrolment( $user_id, $course_id, $is_enrolled ) {

		if ( $is_enrolled ) {
			return;
		}

		if ( ! $this->enrolment_provider->is_enrolled( $user_id, $course_id ) ) {
			return;
		}

		$this->enrolment_provider->reset_enrolment_state( $user_id, $course_id );
	}

	/**
	 * Remove group students from courses.
	 *
	 * @param int   $group_id
	 * @param array $course_ids
	 */
	public function remove_enrolment_in_courses_for_group( $group_id, $course_ids ) {
		// phpcs:ignore WordPress.Security.NonceVerification,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$remove_enrolments = isset( $_GET['remove_enrolments'] ) && 'false' !== $_GET['remove_enrolments'] && boolval( $_GET['remove_enrolments'] );
		if ( ! $remove_enrolments ) {
			return;
		}

		$group_students = $this->group_student_repository->find_group_students( $group_id );
		foreach ( $group_students as $student_id ) {
			foreach ( $course_ids as $course_id ) {
				$this->enrolment_provider->withdraw_learner( $student_id, $course_id, $group_id );
			}
		}
	}
}
