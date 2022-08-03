<?php
/**
 * File containing the class \Sensei_Pro_Student_Groups\Repositories\Group_Student_Repository.
 *
 * @package student-groups
 * @since   1.4.0
 */

namespace Sensei_Pro_Student_Groups\Repositories;

use Sensei_Pro_Student_Groups\Models\Group_Student;
use WP_Post;
use WP_User;

/**
 * Class for mapping group and student relations.
 *
 * @since 1.4.0
 */
class Group_Student_Repository {
	/**
	 * WordPress database object.
	 *
	 * @var \wpdb
	 */
	private $wpdb;

	/**
	 * Relation table name.
	 *
	 * @var string
	 */
	private $table_name;

	/**
	 * Group_Student_Repository constructor.
	 *
	 * @param \wpdb $wpdb WordPress database object.
	 */
	public function __construct( \wpdb $wpdb ) {
		$this->wpdb       = $wpdb;
		$this->table_name = $wpdb->prefix . 'sensei_pro_groups_students';
	}

	/**
	 * Create a group student relation.
	 *
	 * @param WP_Post $group
	 * @param WP_User $user
	 *
	 * @return Group_Student
	 * @throws \RuntimeException If the relation could not be created.
	 */
	public function create( WP_Post $group, WP_User $user ): Group_Student {
		$created_at = new \DateTime();

		$result = $this->wpdb->insert(
			$this->table_name,
			[
				'group_id'   => $group->ID,
				'student_id' => $user->ID,
				'created_at' => wp_date( 'Y-m-d H:i:s', $created_at->getTimestamp() ),
			],
			[
				'%d',
				'%d',
				'%s',
			]
		);

		if ( ! $result ) {
			throw new \RuntimeException( 'Failed to create group student relation.' );
		}

		/**
		 * Fires after student have been added to the group.
		 *
		 * @since 1.4.0
		 *
		 * @param int $group_id    Group ID.
		 * @param int $student_id  Added student IDs.
		 */
		do_action( 'sensei_pro_student_groups_group_student_added', $group->ID, $user->ID );

		return new Group_Student(
			$this->wpdb->insert_id,
			$group->ID,
			$user->ID,
			$created_at
		);
	}

	/**
	 * Delete a group student relation.
	 *
	 * @since 1.4.0
	 *
	 * @param WP_Post $group Group for which the relation will be deleted.
	 * @param WP_User $user  Student for whom the relation will be deleted.
	 *
	 * @return int|boolean Number of rows affected.
	 */
	public function delete( WP_Post $group, WP_User $user ) {
		$result = $this->wpdb->delete(
			$this->table_name,
			[
				'group_id'   => $group->ID,
				'student_id' => $user->ID,
			]
		);
		if ( $result ) {
			/**
			 * Fires after one or more students have been removed from the group.
			 *
			 * @since 1.4.1
			 *
			 * @param int   $group_id      Group ID.
			 * @param int[] $student_ids Removed student IDs.
			 */
			do_action( 'sensei_pro_student_groups_group_students_removed', $group->ID, [ $user->ID ] );
		}
		return $result;
	}

	/**
	 * Batch delete the multiple students from a group.
	 *
	 * @since 1.4.0
	 *
	 * @param int   $group_id Group id from which the students will be removed.
	 * @param int[] $user_ids Ids of the students to be removed.
	 *
	 * @return int|boolean Number of rows affected or false on error.
	 */
	public function batch_delete( int $group_id, array $user_ids ) : int {
		if ( empty( $user_ids ) ) {
			return 0;
		};

		$result = $this->wpdb->query(
		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
			$this->wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
				'DELETE FROM %1s WHERE group_id = %d AND student_id IN (%1s)',
				$this->table_name,
				$group_id,
				implode( ',', $user_ids )
			)
		);

		if ( $result ) {
			/**
			 * Described in \Sensei_Pro_Student_Groups\Repositories\Group_Student_Repository::delete
			 */
			do_action( 'sensei_pro_student_groups_group_students_removed', $group_id, $user_ids );
		}
		return $result;
	}

	/**
	 * Delete all group student relations of a group.
	 *
	 * @since 1.4.0
	 *
	 * @param int $group_id Id of the group.
	 *
	 * @return int Number of rows affected.
	 */
	public function delete_all_relations_for_group( int $group_id ) : int {
		$query = $this->wpdb->prepare(
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
			'SELECT student_id FROM %1s WHERE group_id = %d',
			$this->table_name,
			$group_id
		);
		$user_ids = $this->wpdb->get_col( $query );

		$result = $this->wpdb->delete(
			$this->table_name,
			[
				'group_id' => $group_id,
			],
			[
				'%d',
			]
		);

		if ( $result ) {
			/**
			 * Described in \Sensei_Pro_Student_Groups\Repositories\Group_Student_Repository::delete
			 */
			do_action( 'sensei_pro_student_groups_group_students_removed', $group_id, $user_ids );
		}
		return $result;
	}

	/**
	 * Find a relation for group and student.
	 *
	 * @param WP_Post $group
	 * @param WP_User $user
	 *
	 * @return Group_Student|null
	 */
	public function find_by_group_and_user( WP_Post $group, WP_User $user ) {
		$query = $this->wpdb->prepare(
		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
			'SELECT * FROM ' . $this->table_name . ' WHERE group_id = %d AND student_id = %d',
			$group->ID,
			$user->ID
		);

		$result = $this->wpdb->get_row( $query );
		if ( empty( $result ) ) {
			return null;
		}

		return new Group_Student(
			(int) $result->id,
			(int) $result->group_id,
			(int) $result->student_id,
			new \DateTime( $result->created_at )
		);
	}

	/**
	 * Find students in the group.
	 *
	 * @param int $group_id group id.
	 *
	 * @return array $student_ids list of students.
	 */
	public function find_group_students( int $group_id ) {
		$query  = $this->wpdb->prepare(
			'SELECT student_id FROM ' . $this->table_name . ' WHERE group_id = %d',
			$group_id
		);
		$result = $this->wpdb->get_results( $query );

		if ( empty( $result ) ) {
			return [];
		}

		return array_column( $result, 'student_id' );
	}

	/**
	 * Find all groups that student belongs to.
	 *
	 * @param int $student_id The id of the student.
	 *
	 * @return array $group_ids List of group ids.
	 */
	public function get_student_groups( int $student_id ) {
		$query = $this->wpdb->prepare(
			'SELECT group_id FROM ' . $this->table_name . ' WHERE student_id = %d',
			$student_id
		);

		$result = $this->wpdb->get_results( $query );

		if ( empty( $result ) ) {
			return [];
		}

		return array_column( $result, 'group_id' );
	}

	/**
	 * Get count of students in a group.
	 *
	 * @param int $group_id ID of the group.
	 *
	 * @return int Count of students.
	 */
	public function get_count_for_group( int $group_id ) : int {
		$query = $this->wpdb->prepare(
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
			'SELECT COUNT( student_id ) FROM %1s WHERE group_id = %d',
			$this->table_name,
			$group_id
		);
		return (int) $this->wpdb->get_var( $query );
	}
}
