<?php
/**
 * File containing the Group_Bulk_Actions class.
 *
 * @package student-groups
 */

namespace Sensei_Pro_Student_Groups\Students;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add and handle group related bulk actions for Students.
 */
class Group_Bulk_Actions {

	/**
	 * Group_Bulk_Actions instance.
	 *
	 * @var Group_Bulk_Actions
	 */
	private static $instance;

	/**
	 * Creates instance of Group_Bulk_Actions.
	 *
	 * @return Group_Bulk_Actions
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Group_Bulk_Actions constructor.
	 */
	private function __construct() {}

	/**
	 * Initialize hooks.
	 */
	public function init() {
		add_action( 'sensei_learners_admin_get_known_bulk_actions', [ $this, 'register_bulk_actions' ], 10, 1 );
	}

	/**
	 * Register bulk actions.
	 *
	 * @param array $known_bulk_actions Known bulk actions.
	 * @return array Returns the known bulk actions including the new ones.
	 */
	public function register_bulk_actions( array $known_bulk_actions ) {
		$known_bulk_actions['addToGroup']      = __( 'Add to Group', 'sensei-pro' );
		$known_bulk_actions['removeFromGroup'] = __( 'Remove from Group', 'sensei-pro' );

		return $known_bulk_actions;
	}
}
