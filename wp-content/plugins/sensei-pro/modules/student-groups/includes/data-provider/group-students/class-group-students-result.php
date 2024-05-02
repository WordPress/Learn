<?php
/**
 * File containing the class Sensei_Pro_Student_Groups\Data_Provider\Groups\Group_Students_Result.
 *
 * @package student-groups
 * @since   1.4.0
 */

namespace Sensei_Pro_Student_Groups\Data_Provider\Group_Students;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_User_Query;

/**
 * Group_Students_Result class.
 * Class responsible to Wrap a WP_Query, making some attributes more friendly to access
 */
class Group_Students_Result {
	/**
	 * Used for storing the total number of items available
	 *
	 * @access private
	 * @var int $total_items
	 */
	private $total_items = 0;

	/**
	 * Used for storing the WP_Posts objects converted to Array
	 *
	 * @access private
	 * @var array $items
	 */
	private $items = [];

	/**
	 * Constructor
	 *
	 * @access public
	 * @since 1.4.0
	 *
	 * @param WP_User_Query | null $query wp user query.
	 */
	public function __construct( WP_User_Query $query = null ) {
		if ( ! $query ) {
			return;
		}
		$this->total_items = count( $query->get_results() );
		$to_array          = function ( $value ) {
			return $value->to_array();
		};

		$this->items = array_map( $to_array, $query->get_results() );

		$this->hydrate_last_activity_date();
	}

	/**
	 * Return total of items returned by the query
	 *
	 * @access public
	 * @since 1.4.0
	 *
	 * @return integer
	 */
	public function get_total_items(): int {
		return $this->total_items;
	}

	/**
	 * Return the query items converted to an array
	 *
	 * @access public
	 * @since 1.4.0
	 *
	 * @return array
	 */
	public function get_items(): array {
		return $this->items;
	}

	/**
	 * Hydrate the last activity date for each student.
	 */
	private function hydrate_last_activity_date() {
		global $wpdb;

		if ( empty( $this->items ) ) {
			return;
		}

		$query_params = wp_list_pluck( $this->items, 'ID' );
		$placeholders = array_fill( 0, count( $query_params ), '%d' );
		$placeholders = implode( ', ', $placeholders );

		$sql = "
			SELECT {$wpdb->comments}.user_id, MAX({$wpdb->comments}.comment_date_gmt) AS last_activity_date
			FROM {$wpdb->comments}
			WHERE {$wpdb->comments}.user_id in ({$placeholders})
			AND {$wpdb->comments}.comment_approved IN ('complete', 'passed', 'graded')
			AND {$wpdb->comments}.comment_type = 'sensei_lesson_status'
			GROUP BY {$wpdb->comments}.user_id";

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$results = $wpdb->get_results(
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->prepare( $sql, $query_params )
		);

		$last_activity_dates = wp_list_pluck( $results, 'last_activity_date', 'user_id' );

		foreach ( $this->items as $key => $student ) {
			$this->items[ $key ]['last_activity_date'] = $last_activity_dates[ $student['ID'] ] ?? null;
		}
	}
}
