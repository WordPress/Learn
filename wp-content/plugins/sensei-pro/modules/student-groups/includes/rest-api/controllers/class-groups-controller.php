<?php
/**
 * File contains Groups_Controller class.
 *
 * @package student-groups
 * @since 1.4.0
 */

namespace Sensei_Pro_Student_Groups\Rest_Api\Controllers;

use WP_Post;
use WP_Query;
use WP_REST_Controller;
use WP_REST_Response;
use WP_REST_Server;
use WP_Error;

/**
 * Student groups controller class.
 *
 * @since 1.4.0
 */
class Groups_Controller extends WP_REST_Controller {

	/**
	 * Routes namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'sensei-pro-student-groups/v1';

	/**
	 * Routes prefix.
	 *
	 * @var string
	 */
	protected $rest_base = 'groups';

	/**
	 * Sensei_REST_API_Student_Groups_Controller constructor.
	 */
	public function __construct() {
	}

	/**
	 * Register the REST API endpoints for student groups.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			$this->rest_base,
			[
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'create_item' ],
					'permission_callback' => [ $this, 'create_item_permission_check' ],
					'args'                => $this->get_args_schema( 'create_item' ),
				],
			]
		);
		register_rest_route(
			$this->namespace,
			$this->rest_base . '/(?P<group_id>[\d]+)',
			[
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update_item' ],
					'permission_callback' => [ $this, 'update_item_permission_check' ],
					'args'                => $this->get_args_schema( 'create_item' ),
				],
				[
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete_item' ],
					'permission_callback' => [ $this, 'delete_item_permission_check' ],
				],
			]
		);
	}

	/**
	 * Create new student group.
	 *
	 * @since 1.4.0
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_item( $request ) {
		$params     = $request->get_params();
		$group_name = $params['name'];

		if ( $this->get_group_by_name( $group_name ) ) {
			return new WP_Error(
				'sensei_duplicate_group_name',
				__( 'A group with this name already exists.', 'sensei-pro' ),
				[ 'status' => 400 ]
			);
		}
		$new_post_id = wp_insert_post(
			[
				'post_title'  => $group_name,
				'post_type'   => 'group',
				'post_status' => 'publish',
			],
			true
		);

		if ( is_wp_error( $new_post_id ) ) {
			return $new_post_id;
		}

		sensei_log_event( 'group_create' );

		return new WP_REST_Response(
			[
				'id'   => $new_post_id,
				'name' => $group_name,
			],
			201
		);
	}

	/**
	 * Delete a student group.
	 *
	 * @since 1.4.0
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_item( $request ) {
		$params   = $request->get_params();
		$group_id = (int) $params['group_id'];

		$delete_result = wp_trash_post( $group_id );

		if ( ! $delete_result ) {
			return new WP_Error(
				'sensei_delete_group_fail',
				__( 'Could not delete the group.', 'sensei-pro' ),
				[ 'status' => 400 ]
			);
		}

		return new WP_REST_Response(
			[
				'id'   => $group_id,
				'name' => $delete_result->post_title,
			]
		);
	}

	/**
	 * Update student group.
	 *
	 * @since 1.4.0
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_item( $request ) {
		$params     = $request->get_params();
		$group_id   = (int) $params['group_id'];
		$group_name = $params['name'];

		$existing_group = $this->get_group_by_name( $group_name );
		if ( $existing_group && $existing_group->ID !== $group_id ) {
			return new WP_Error(
				'sensei_duplicate_group_name',
				__( 'A group with this name already exists.', 'sensei-pro' ),
				[ 'status' => 400 ]
			);
		}
		$result = wp_update_post(
			[
				'ID'         => $group_id,
				'post_title' => $group_name,
			],
			true
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return new WP_REST_Response(
			[
				'id'   => $group_id,
				'name' => $group_name,
			]
		);
	}

	/**
	 * Schema definition for endpoint arguments.
	 *
	 * @param string $key Key for the param.
	 *
	 * @return array[]
	 */
	private function get_args_schema( $key ): array {
		$schema_list = [
			'create_item' => [
				'name' => [
					'description' => 'Name of the group to be created.',
					'type'        => 'string',
					'minLength'   => 1,
					'required'    => true,
				],
			],
		];
		return $schema_list[ $key ];
	}

	/**
	 * Check if the current user can perform a student-group creation operations.
	 *
	 * @access private
	 *
	 * @return boolean
	 */
	public function create_item_permission_check() {
		return current_user_can(
			get_post_type_object( 'group' )->cap->publish_posts
		);
	}

	/**
	 * Check if the current user can perform a student-group update operations.
	 *
	 * @access private
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return boolean|WP_Error
	 */
	public function update_item_permission_check( $request ) {
		$params         = $request->get_params();
		$edit_group_cap = get_post_type_object( 'group' )->cap->edit_post;

		if ( ! get_post( $params['group_id'] ) ) {
			return new WP_Error(
				'group_not_found',
				__( 'No group found.', 'sensei-pro' ),
				[ 'status' => 404 ]
			);
		}

		return current_user_can( $edit_group_cap, $params['group_id'] );
	}

	/**
	 * Check if the current user can delete a group.
	 *
	 * @access private
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return boolean|WP_Error
	 */
	public function delete_item_permission_check( $request ) {
		$params           = $request->get_params();
		$delete_group_cap = get_post_type_object( 'group' )->cap->delete_post;
		$group            = get_post( $params['group_id'] );
		if ( ! $group || 'group' !== $group->post_type ) {
			return new WP_Error(
				'group_not_found',
				__( 'No group found.', 'sensei-pro' ),
				[ 'status' => 404 ]
			);
		}

		return current_user_can( $delete_group_cap, $params['group_id'] );
	}

	/**
	 * Get the group post by name.
	 *
	 * @param string $name The group name.
	 *
	 * @return WP_Post|null
	 */
	private function get_group_by_name( $name ): ?WP_Post {
		$query = new WP_Query(
			[
				'post_type'      => 'group',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'title'          => $name,
			]
		);

		return $query->have_posts() ? $query->posts[0] : null;
	}

}
