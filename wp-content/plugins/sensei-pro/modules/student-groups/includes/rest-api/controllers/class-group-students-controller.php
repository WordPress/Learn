<?php
/**
 * File containing the class \Sensei_Pro_Student_Groups\Rest_API\Controllers\Group_Students.
 *
 * @package student-groups
 * @since   1.4.0
 */

namespace Sensei_Pro_Student_Groups\Rest_Api\Controllers;

use Sensei_Pro_Student_Groups\Repositories\Group_Student_Repository;
use Sensei_Pro_Student_Groups\Rest_Api\Responses\Student_Response;

/**
 * REST API class for managing students in the group.
 *
 * @since 1.4.0
 */
class Group_Students_Controller extends \WP_REST_Controller {

	/**
	 * Endpoint namespace for internal routes.
	 *
	 * @var string
	 */
	protected $namespace = 'sensei-pro-student-groups/v1';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'groups/(?P<group_id>[\d]+)/students';

	/**
	 * Group student repository.
	 *
	 * @var Group_Student_Repository
	 */
	private $group_student_repository;

	/**
	 * Group_Students_Controller constructor.
	 *
	 * @param Group_Student_Repository $group_student_mapper Group student mapper.
	 */
	public function __construct( Group_Student_Repository $group_student_mapper ) {
		$this->group_student_repository = $group_student_mapper;
	}

	/**
	 * Register the routes for managing students in the group.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				[
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create_group_student' ],
					'permission_callback' => [ $this, 'create_group_student_permission_check' ],
					'args'                => $this->get_args_schema( 'create_group_student' ),
				],
				[
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'batch_delete_group_students' ],
					'permission_callback' => [ $this, 'delete_group_student_permission_check' ],
					'args'                => $this->get_args_schema( 'batch_delete_group_students' ),
				],
			]
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<student_id>[\d]+)',
			[
				[
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete_group_student' ],
					'permission_callback' => [ $this, 'delete_group_student_permission_check' ],
					'args'                => $this->get_args_schema( 'delete_group_student' ),
				],
			]
		);
	}

	/**
	 * Add students to the group.
	 *
	 * Implementation of the REST API endpoint for adding students to the group.
	 * The request body should contain a list of student IDs to add to the group.
	 * The response will contain a list of student IDs that were successfully added.
	 * If any of the students were already in the group, they will not be added again but appear in the response.
	 *
	 * @access private
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function create_group_student( \WP_REST_Request $request ) {
		$url_params = $request->get_url_params();
		$group_id   = (int) $url_params['group_id'];

		$group = get_post( $group_id );
		if ( ! $group || 'group' !== $group->post_type ) {
			return new \WP_Error( 'group_not_found', __( 'Group not found.', 'sensei-pro' ), [ 'status' => 404 ] );
		}

		$json_params = $request->get_json_params();
		$student_ids = $json_params['student_ids'] ?? [];
		if ( ! is_array( $student_ids ) || empty( $student_ids ) ) {
			return new \WP_Error(
				'invalid_student_ids',
				__( 'Student IDs not provided.', 'sensei-pro' ),
				[ 'status' => 400 ]
			);
		}

		$student_ids = array_map( 'intval', $student_ids );
		$student_ids = array_unique( $student_ids );
		$users       = get_users( [ 'include' => $student_ids ] );
		if ( count( $student_ids ) !== count( $users ) ) {
			return new \WP_Error(
				'invalid_student_ids',
				__( 'Invalid student IDs or users not found.', 'sensei-pro' ),
				[ 'status' => 400 ]
			);
		}

		$response = [];
		foreach ( $users as $user ) {
			$group_student = $this->group_student_repository->find_by_group_and_user( $group, $user );
			if ( null === $group_student ) {
				// todo: catch exception.
				$this->group_student_repository->create( $group, $user );
			}
			$response[] = Student_Response::from_user( $user );
		}

		sensei_log_event( 'group_students_add', [ 'count' => count( $student_ids ) ] );

		/**
		 * Fires after students are added to a group.
		 *
		 * The response contains the list of added students.
		 *
		 * @since 1.4.0
		 *
		 * @param \WP_Post $group       Group.
		 * @param array    $student_ids Array of student IDs.
		 * @param array    $response    Array of student responses.
		 */
		$response = apply_filters(
			'sensei_pro_student_groups_create_group_student_response',
			$response,
			$group,
			$student_ids
		);

		return new \WP_REST_Response( $response, 201 );
	}

	/**
	 * Delete student from the group.
	 *
	 * @access private
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function delete_group_student( \WP_REST_Request $request ) {
		$url_params = $request->get_url_params();
		$group_id   = (int) $url_params['group_id'];
		$student_id = (int) $url_params['student_id'];

		$group = get_post( $group_id );
		if ( ! $group || 'group' !== $group->post_type ) {
			return new \WP_Error( 'group_not_found', __( 'Group not found.', 'sensei-pro' ), [ 'status' => 404 ] );
		}

		$student = get_user_by( 'ID', $student_id );
		if ( ! $student ) {
			return new \WP_Error( 'student_not_found', __( 'Student not found.', 'sensei-pro' ), [ 'status' => 404 ] );
		}

		$group_student = $this->group_student_repository->find_by_group_and_user( $group, $student );
		if ( ! $group_student ) {
			return new \WP_Error(
				'student_not_found_in_group',
				__( 'Student not found in group.', 'sensei-pro' ),
				[ 'status' => 400 ]
			);
		}

		$this->group_student_repository->delete( $group, $student );

		return new \WP_REST_Response(
			[
				'id'   => $student_id,
				'name' => $student->display_name,
			],
			201
		);
	}

	/**
	 * Delete multiple students from a group.
	 *
	 * @access private
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function batch_delete_group_students( \WP_REST_Request $request ) {
		$params      = $request->get_params();
		$group_id    = (int) $params['group_id'];
		$student_ids = array_map( 'absint', $params['student_ids'] );

		$group = get_post( $group_id );
		if ( ! $group || 'group' !== $group->post_type ) {
			return new \WP_Error( 'group_not_found', __( 'Group not found.', 'sensei-pro' ), [ 'status' => 404 ] );
		}

		$batch_delete_result = $this->group_student_repository->batch_delete( $group_id, $student_ids );
		if ( false === $batch_delete_result ) {
			return new \WP_Error( 'batch_delete_operation_failed', __( 'Sorry, could not perform batch delete operation.', 'sensei-pro' ), [ 'status' => 400 ] );
		}

		return new \WP_REST_Response(
			$student_ids,
			200
		);
	}

	/**
	 * Check if the current user can create a group student.
	 * This is a permission check for the create_group_student() method.
	 *
	 * @param \WP_REST_Request $request Request object.
	 */
	public function create_group_student_permission_check( \WP_REST_Request $request ): bool {
		$params         = $request->get_url_params();
		$edit_group_cap = get_post_type_object( 'group' )->cap->edit_post;

		if ( ! isset( $params['group_id'] ) ) {
			return false;
		}

		$group = get_post( $params['group_id'] );
		if ( $group && ! current_user_can( $edit_group_cap, $params['group_id'] ) ) {
			return false;
		}

		return current_user_can( 'manage_sensei' );
	}

	/**
	 * Check if the current user can delete a group student.
	 * This is a permission check for the delete_group_student() method.
	 *
	 * @param \WP_REST_Request $request Request object.
	 */
	public function delete_group_student_permission_check( \WP_REST_Request $request ): bool {
		$params         = $request->get_url_params();
		$edit_group_cap = get_post_type_object( 'group' )->cap->edit_post;

		if ( ! isset( $params['group_id'] ) ) {
			return false;
		}

		$group = get_post( $params['group_id'] );
		if ( $group && ! current_user_can( $edit_group_cap, $params['group_id'] ) ) {
			return false;
		}

		return current_user_can( 'manage_sensei' );
	}

	/**
	 * Schema definition for endpoint arguments.
	 *
	 * @param string $key Key for the param.
	 *
	 * @return array[]
	 */
	private function get_args_schema( string $key ): array {
		$schema_list = [
			'create_group_student'        => [
				'student_ids' => [
					'description' => 'Student IDs to add to the group.',
					'type'        => 'array',
					'minItems'    => 1,
					'items'       => [
						'type' => 'integer',
					],
					'required'    => true,
				],
			],
			'batch_delete_group_students' => [
				'student_ids'       => [
					'description' => 'Student IDs to remove from the group.',
					'type'        => 'array',
					'minItems'    => 1,
					'items'       => [
						'type' => 'integer',
					],
					'required'    => true,
				],
				'remove_enrolments' => [
					'description' => 'Flag to determine whether to remove enrollments.',
					'type'        => 'integer',
					'enum'        => [ 0, 1 ],
					'required'    => false,
				],
			],
			'delete_group_student'        => [
				'student_id'        => [
					'description' => 'Student ID to delete from the group.',
					'type'        => 'integer',
					'required'    => true,
				],
				'remove_enrolments' => [
					'description' => 'Flag to determine whether to remove enrollments.',
					'type'        => 'integer',
					'enum'        => [ 0, 1 ],
					'required'    => false,
				],
			],
		];

		return $schema_list[ $key ];
	}
}
