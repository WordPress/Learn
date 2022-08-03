<?php
/**
 * File containing the class \Sensei_Pro_Student_Groups\Models\Group_Student.
 *
 * @package student-groups
 */

namespace Sensei_Pro_Student_Groups\Models;

/**
 * Class for representing a student in a group.
 */
class Group_Student {

	/**
	 * Relation ID.
	 *
	 * @var int
	 */
	private $id;

	/**
	 * The group ID.
	 *
	 * @var int
	 */
	private $group_id;

	/**
	 * The student ID.
	 *
	 * @var int
	 */
	private $student_id;

	/**
	 * Created date.
	 *
	 * @var \DateTime
	 */
	private $created_at;

	/**
	 * Group_Student constructor.
	 *
	 * @param int       $id Relation ID.
	 * @param int       $group_id The group ID.
	 * @param int       $student_id The student ID.
	 * @param \DateTime $created_at Created date.
	 */
	public function __construct( int $id, int $group_id, int $student_id, \DateTime $created_at ) {
		$this->id         = $id;
		$this->group_id   = $group_id;
		$this->student_id = $student_id;
		$this->created_at = $created_at;
	}

	/**
	 * Returns group and student relation ID.
	 *
	 * @return int
	 */
	public function get_id(): int {
		return $this->id;
	}

	/**
	 * Returns the group ID.
	 *
	 * @return int
	 */
	public function get_group_id(): int {
		return $this->group_id;
	}

	/**
	 * Returns the student ID.
	 *
	 * @return int
	 */
	public function get_student_id(): int {
		return $this->student_id;
	}

	/**
	 * Returns the created date.
	 *
	 * @return \DateTime
	 */
	public function get_created_at(): \DateTime {
		return $this->created_at;
	}
}
