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
	}

	/**
	 * Return total of items returned by the query
	 *
	 * @access public
	 * @since 1.4.0
	 *
	 * @return integer
	 */
	public function get_total_items():int {
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
}
