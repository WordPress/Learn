<?php
/**
 * File containing the class \Sensei_Pro_Student_Groups\Models\Group_Course.
 *
 * @package student-groups
 * @since   1.4.0
 */

namespace Sensei_Pro_Student_Groups\Models;

use DateTime;

/**
 * Class for representing a student in a group.
 */
class Group_Course {

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
	 * The course ID.
	 *
	 * @var int
	 */
	private $course_id;

	/**
	 * Access period.
	 *
	 * @var Access_Period
	 */
	private $access_period;

	/**
	 * Created date.
	 *
	 * @var DateTime
	 */
	private $created_at;

	/**
	 * Group_Course constructor.
	 *
	 * @param int           $id            Relation ID.
	 * @param int           $group_id      The group ID.
	 * @param int           $course_id     The student ID.
	 * @param Access_Period $access_period Access period.
	 * @param DateTime      $created_at    Created date.
	 */
	public function __construct(
		int $id,
		int $group_id,
		int $course_id,
		Access_Period $access_period,
		DateTime $created_at
	) {
		$this->id            = $id;
		$this->group_id      = $group_id;
		$this->course_id     = $course_id;
		$this->access_period = $access_period;
		$this->created_at    = $created_at;
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
	 * Returns the course ID.
	 *
	 * @return int
	 */
	public function get_course_id(): int {
		return $this->course_id;
	}

	/**
	 * Returns the access period.
	 *
	 * @return Access_Period
	 */
	public function get_access_period(): Access_Period {
		return $this->access_period;
	}

	/**
	 * Returns the created date.
	 *
	 * @return DateTime
	 */
	public function get_created_at(): DateTime {
		return $this->created_at;
	}
}
