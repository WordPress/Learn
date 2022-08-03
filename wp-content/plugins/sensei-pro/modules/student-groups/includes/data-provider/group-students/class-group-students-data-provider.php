<?php
/**
 * File containing the class Sensei_Pro_Student_Groups\Data_Provider\Group_Students.
 *
 * @package student-groups
 * @since   1.4.0
 */

namespace Sensei_Pro_Student_Groups\Data_Provider\Group_Students;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Sensei_Pro_Student_Groups\Repositories\Group_Student_Repository;
use WP_User_Query;

/**
 * Groups_Data_Provider
 *
 * @since 1.4.0
 */
class Group_Students_Data_Provider {

	/**
	 * Group student repository.
	 *
	 * @var Group_Student_Repository
	 */
	private $group_student_repository;

	/**
	 * Class Constructor
	 *
	 * @param Group_Student_Repository $group_student_repository group students repository.
	 */
	public function __construct( Group_Student_Repository $group_student_repository ) {
		$this->group_student_repository = $group_student_repository;
	}

	/**
	 * Get the list of all students in a group.
	 *
	 * @access public
	 * @since   1.4.0
	 *
	 * @param array $filters Filters to apply to the data.
	 * @param int   $group_id group id.
	 * @return Group_Students_Result
	 */
	public function get_items( array $filters, int $group_id ): Group_Students_Result {
		if ( isset( $filters['search'] ) ) {
			$filters['s'] = $filters['search'];
		}

		$student_ids = $this->group_student_repository->find_group_students( $group_id );

		// if there are no students return empty result object.
		if ( empty( $student_ids ) ) {
			return new Group_Students_Result();
		}
		$filters['include'] = $student_ids;

		add_action( 'pre_user_query', [ $this, 'modify_user_query_for_custom_fields_orders_filters' ] );
		$user_search = new WP_User_Query( $filters );
		remove_action( 'pre_user_query', [ $this, 'modify_user_query_for_custom_fields_orders_filters' ] );

		return new Group_Students_Result( $user_search );
	}

	/**
	 * Modify the query to fetch students for group-students table.
	 *
	 * @since  1.4.0
	 * @access private
	 *
	 * @param WP_User_Query $query The user query.
	 */
	public function modify_user_query_for_custom_fields_orders_filters( WP_User_Query $query ) {
		global $wpdb;

		// Add last activity column in the result.
		$query->query_fields .= ", (
			SELECT MAX({$wpdb->comments}.comment_date_gmt)
			FROM {$wpdb->comments}
			WHERE {$wpdb->comments}.user_id = {$wpdb->users}.ID
			AND {$wpdb->comments}.comment_approved IN ('complete', 'passed', 'graded')
			AND {$wpdb->comments}.comment_type = 'sensei_lesson_status'
		) AS last_activity_date";

		// Order by last activity when needed.
		if ( $query->query_vars['orderby'] && 'last_activity_date' === $query->query_vars['orderby'] ) {
			$query->query_orderby = $wpdb->prepare(
				'ORDER BY %1s %1s', // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder -- not needed.
				$query->query_vars['orderby'],
				$query->query_vars['order']
			);
		}

		// Filter by course ID when needed.
		if ( isset( $query->query_vars['filter_by_course_id'] ) && $query->query_vars['filter_by_course_id'] ) {
			$query->query_from .= $wpdb->prepare(
				" INNER JOIN `{$wpdb->comments}` AS `cf`
				ON {$wpdb->users}.ID = `cf`.`user_id`
				AND `cf`.`comment_type` = 'sensei_course_status'
				AND `cf`.comment_post_ID = %d
				AND `cf`.comment_approved IS NOT NULL
				",
				$query->query_vars['filter_by_course_id']
			);
		}
	}
}
