<?php
/**
 * File contains WP_REST_Groups_Controller class.
 *
 * @package student-groups
 * @since 1.5.0
 */

namespace Sensei_Pro_Student_Groups\Rest_Api\Controllers;

use \WP_REST_Posts_Controller;
use \WP_Error;

/**
 * Student groups controller class.
 *
 * @since 1.5.0
 */
class WP_REST_Groups_Controller extends WP_REST_Posts_Controller {
	/**
	 * Returns a forbidden error response object.
	 *
	 * @return WP_Error
	 */
	public function forbidden_response() {
		return new WP_Error(
			'forbidden',
			__( 'Request not allowed.', 'sensei-pro' ),
			[ 'status' => 403 ]
		);
	}

	/**
	 * Check if the current user can read a student-group item.
	 *
	 * @access private
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has read access for the item, WP_Error object otherwise.
	 */
	public function get_item_permissions_check( $request ) {
		$user = wp_get_current_user();

		$has_allowed_roles = array_intersect( [ 'administrator', 'teacher' ], (array) $user->roles );

		if ( count( $has_allowed_roles ) ) {
			return true;
		}

		return $this->forbidden_response();
	}

	/**
	 * Check if the current user can list a student-group items.
	 *
	 * @access private
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_items_permissions_check( $request ) {
		return $this->get_item_permissions_check( $request );
	}

	/**
	 * Check if the current user can perform a student-group creation operations.
	 *
	 * @access private
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error
	 */
	public function create_item_permissions_check( $request ) {
		return $this->forbidden_response();
	}

	/**
	 * Check if the current user can perform a student-group update operations.
	 *
	 * @access private
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error
	 */
	public function update_item_permissions_check( $request ) {
		return $this->forbidden_response();
	}

	/**
	 * Check if the current user can delete a group.
	 *
	 * @access private
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error
	 */
	public function delete_item_permissions_check( $request ) {
		return $this->forbidden_response();
	}

}
