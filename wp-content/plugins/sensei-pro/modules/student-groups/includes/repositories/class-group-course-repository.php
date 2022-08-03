<?php
/**
 * File containing the class \Sensei_Pro_Student_Groups\Repositories\Group_Course_Repository.
 *
 * @package student-groups
 * @since   1.4.0
 */

namespace Sensei_Pro_Student_Groups\Repositories;

use DateTime;
use RuntimeException;
use Sensei_Pro_Student_Groups\Models\Access_Period;
use Sensei_Pro_Student_Groups\Models\Group_Course;
use WP_Post;
use wpdb;

/**
 * Class for containing db operations regarding group and course relations.
 *
 * @since 1.4.0
 */
class Group_Course_Repository {
	/**
	 * WordPress database object.
	 *
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * Relation table name.
	 *
	 * @var string
	 */
	private $table_name;

	/**
	 * Group_Course_Repository constructor.
	 *
	 * @param wpdb $wpdb WordPress database object.
	 */
	public function __construct( wpdb $wpdb ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $wpdb->prefix . 'sensei_pro_groups_courses';
	}

	/**
	 * Find all group course relations for the given group.
	 *
	 * @param int $group_id
	 *
	 * @return array Return an array of Group_Course objects.
	 */
	public function find_by_group( int $group_id ): array {
		$query = $this->wpdb->prepare(
		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
			'SELECT * FROM ' . $this->table_name . ' WHERE group_id = %d',
			$group_id
		);

		$result = $this->wpdb->get_results( $query );

		if ( ! $result || ! is_array( $result ) || count( $result ) < 1 ) {
			return [];
		}

		return array_map(
			function ( $group_course ) {
				return new Group_Course(
					(int) $group_course->id,
					(int) $group_course->group_id,
					(int) $group_course->course_id,
					new Access_Period(
						$group_course->access_period_start
							? new DateTime( $group_course->access_period_start )
							: null,
						$group_course->access_period_end
							? new DateTime( $group_course->access_period_end )
							: null
					),
					new DateTime( $group_course->created_at )
				);
			},
			$result
		);
	}

	/**
	 * Create a group student relation.
	 *
	 * @param WP_Post     $group
	 * @param WP_Post     $course
	 * @param string|null $access_period_start
	 * @param string|null $access_period_end
	 *
	 * @return Group_Course
	 * @throws RuntimeException If the relation could not be created.
	 */
	public function create(
		WP_Post $group,
		WP_Post $course,
		$access_period_start,
		$access_period_end
	): Group_Course {
		$created_at               = new DateTime();
		$access_period_start_date = null;
		if ( ! is_null( $access_period_start ) ) {
			$access_period_start_date = new DateTime( $access_period_start . ' 00:00:00' );
		}
		$access_period_end_date = null;
		if ( ! is_null( $access_period_end ) ) {
			$access_period_end_date = new DateTime( $access_period_end . ' 23:59:59' );
		};

		$insert_data = [
			'group_id'            => $group->ID,
			'course_id'           => $course->ID,
			'access_period_start' => ! is_null( $access_period_start_date )
				? wp_date( 'Y-m-d H:i:s', $access_period_start_date->getTimestamp() )
				: null,
			'access_period_end'   => ! is_null( $access_period_end_date )
				? wp_date( 'Y-m-d H:i:s', $access_period_end_date->getTimestamp() )
				: null,
			'created_at'          => wp_date( 'Y-m-d H:i:s', $created_at->getTimestamp() ),
		];

		$result = $this->wpdb->insert(
			$this->table_name,
			$insert_data,
			[
				'%d',
				'%d',
				is_null( $insert_data['access_period_start'] ) ? null : '%s',
				is_null( $insert_data['access_period_end'] ) ? null : '%s',
				'%s',
			]
		);

		if ( ! $result ) {
			throw new RuntimeException( 'Failed to create group course relation.' );
		}

		/**
		 * Fires after a course has been added to a group.
		 *
		 * @since 1.4.0
		 *
		 * @param int $group_id  Group ID.
		 * @param int $course_id Course ID.
		 */
		do_action( 'sensei_pro_student_groups_group_course_added', $group->ID, $course->ID );

		$access_period = new Access_Period(
			$access_period_start_date,
			$access_period_end_date
		);

		return new Group_Course(
			$this->wpdb->insert_id,
			$group->ID,
			$course->ID,
			$access_period,
			$created_at
		);
	}

	/**
	 * Update a group course relation.
	 *
	 * @param Group_Course $group_course
	 * @param string|null  $access_period_start
	 * @param string|null  $access_period_end
	 *
	 * @return Group_Course
	 * @throws RuntimeException If the relation could not be updated.
	 */
	public function update(
		Group_Course $group_course,
		?string $access_period_start,
		?string $access_period_end
	): Group_Course {
		$access_period_start_date = null;
		if ( ! is_null( $access_period_start ) ) {
			$access_period_start_date = new DateTime( $access_period_start . ' 00:00:00' );
		}
		$access_period_end_date = null;
		if ( ! is_null( $access_period_end ) ) {
			$access_period_end_date = new DateTime( $access_period_end . ' 23:59:59' );
		};

		$update_data = [
			'access_period_start' => ! is_null( $access_period_start_date )
				? wp_date( 'Y-m-d H:i:s', $access_period_start_date->getTimestamp() )
				: null,
			'access_period_end'   => ! is_null( $access_period_end_date )
				? wp_date( 'Y-m-d H:i:s', $access_period_end_date->getTimestamp() )
				: null,
		];

		$result = $this->wpdb->update(
			$this->table_name,
			$update_data,
			[
				'group_id'  => $group_course->get_group_id(),
				'course_id' => $group_course->get_course_id(),
			],
			[
				is_null( $update_data['access_period_start'] ) ? null : '%s',
				is_null( $update_data['access_period_end'] ) ? null : '%s',
			]
		);

		if ( ! $result ) {
			throw new RuntimeException( 'Failed to update group course relation.' );
		}

		$access_period = new Access_Period(
			$access_period_start_date,
			$access_period_end_date
		);

		return new Group_Course(
			$this->wpdb->insert_id,
			$group_course->get_group_id(),
			$group_course->get_course_id(),
			$access_period,
			$group_course->get_created_at()
		);
	}

	/**
	 * Delete a group-course relation.
	 *
	 * @param WP_Post $group  The group.
	 * @param WP_Post $course The course.
	 */
	public function delete( WP_Post $group, WP_Post $course ) {
		$this->wpdb->delete(
			$this->table_name,
			[
				'group_id'  => $group->ID,
				'course_id' => $course->ID,
			],
			[
				'%d',
				'%d',
			]
		);

		/**
		 * Fires after a group course has been deleted.
		 *
		 * @since 1.4.1
		 *
		 * @param int   $group_id   The group ID.
		 * @param array $course_ids Removed course IDs.
		 */
		do_action( 'sensei_pro_student_groups_group_courses_removed', $group->ID, [ $course->ID ] );
	}

	/**
	 * Delete all group-course relations for a group.
	 *
	 * @param int $group_id ID of the group.
	 */
	public function delete_all_relations_for_group( int $group_id ): void {
		$query = $this->wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
			'SELECT course_id FROM %1s WHERE group_id = %d',
			$this->table_name,
			$group_id
		);
		$course_ids = $this->wpdb->get_col( $query );

		$this->wpdb->delete(
			$this->table_name,
			[
				'group_id' => $group_id,
			],
			[
				'%d',
			]
		);

		/**
		 * Described in \Sensei_Pro_Student_Groups\Repositories\Group_Course_Repository::delete
		 */
		do_action( 'sensei_pro_student_groups_group_courses_removed', $group_id, $course_ids );
	}

	/**
	 * Delete all group-course relations for a course.
	 *
	 * @param int $course_id ID of the course.
	 */
	public function delete_all_by_course( int $course_id ) {
		$query = $this->wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
			'SELECT group_id FROM %1s WHERE course_id = %d',
			$this->table_name,
			$course_id
		);
		$group_ids = $this->wpdb->get_col( $query );

		$this->wpdb->delete(
			$this->table_name,
			[
				'course_id' => $course_id,
			],
			[
				'%d',
			]
		);

		foreach ( $group_ids as $group_id ) {
			/**
			 * Described in \Sensei_Pro_Student_Groups\Repositories\Group_Course_Repository::delete
			 */
			do_action( 'sensei_pro_student_groups_group_courses_removed', $group_id, [ $course_id ] );
		}
	}

	/**
	 * Find a relation for group and course.
	 *
	 * @param int $group_id
	 * @param int $course_id
	 *
	 * @return Group_Course|null
	 */
	public function find_by_group_and_course( int $group_id, int $course_id ) {
		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
		$query = $this->wpdb->prepare(
			'SELECT * FROM ' . $this->table_name . ' WHERE group_id = %d AND course_id = %d',
			$group_id,
			$course_id
		);

		$result = $this->wpdb->get_row( $query );
		if ( empty( $result ) ) {
			return null;
		}

		$access_period = new Access_Period(
			$result->access_period_start ? new DateTime( $result->access_period_start ) : null,
			$result->access_period_end ? new DateTime( $result->access_period_end ) : null
		);

		return new Group_Course(
			(int) $result->id,
			(int) $result->group_id,
			(int) $result->course_id,
			$access_period,
			new DateTime( $result->created_at )
		);
	}

	/**
	 * Get count of courses in a group.
	 *
	 * @param int $group_id ID of the group.
	 *
	 * @return int Count of courses.
	 */
	public function get_count_for_group( int $group_id ): int {
		$query = $this->wpdb->prepare(
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
			'SELECT COUNT( course_id ) FROM %1s WHERE group_id = %d',
			$this->table_name,
			$group_id
		);
		return (int) $this->wpdb->get_var( $query );
	}
}
