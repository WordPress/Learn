<?php
/**
 * File containing the class \Sensei_Pro_Student_Groups\Rest_Api\Controllers\Group_Courses_Controller.
 *
 * @package student-groups
 * @since   1.4.0
 */

namespace Sensei_Pro_Student_Groups\Rest_Api\Controllers;

use Sensei_Pro_Student_Groups\Models\Group_Course;
use Sensei_Pro_Student_Groups\Repositories\Group_Course_Repository;
use Sensei_Pro_Student_Groups\Rest_Api\Responses\Course_Response;
use WP_REST_Controller;

/**
 * Group courses controller class.
 *
 * @since 1.4.0
 */
class Group_Courses_Controller extends WP_REST_Controller {

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
	protected $rest_base = 'groups/(?P<group_id>[\d]+)/courses';

	/**
	 * Group course repository.
	 *
	 * @var Group_Course_Repository
	 */
	private $group_course_repository;

	/**
	 * Group_Courses_Controller constructor.
	 *
	 * @param Group_Course_Repository $group_course_repository Group course repository.
	 */
	public function __construct( Group_Course_Repository $group_course_repository ) {
		$this->group_course_repository = $group_course_repository;
	}

	/**
	 * Register the routes for managing courses in the group.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				[
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create_group_course' ],
					'permission_callback' => [ $this, 'group_course_permission_check' ],
					'args'                => $this->get_args_schema( 'create_group_course' ),
				],
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_group_courses' ],
					'permission_callback' => [ $this, 'get_group_courses_permission_check' ],
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<course_id>[\d]+)',
			[
				[
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete_group_course' ],
					'permission_callback' => [ $this, 'group_course_permission_check' ],
					'args'                => $this->get_args_schema( 'delete_group_course' ),
				],
				[
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'edit_group_course' ],
					'permission_callback' => [ $this, 'group_course_edit_permission_check' ],
					'args'                => $this->get_args_schema( 'edit_group_course' ),
				],
			]
		);
	}

	/**
	 * Add a course to the group.
	 *
	 * Implementation of the REST API endpoint for adding a course to the group.
	 * The request body should contain course ID to add to the group.
	 *
	 * @access private
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function create_group_course( \WP_REST_Request $request ) {
		$url_params = $request->get_url_params();
		$group_id   = (int) $url_params['group_id'];

		$group = get_post( $group_id );
		if ( ! $group || 'group' !== $group->post_type ) {
			return new \WP_Error( 'group_not_found', __( 'Group not found.', 'sensei-pro' ), [ 'status' => 404 ] );
		}

		$json_params = $request->get_json_params();
		$course_id   = (int) $json_params['courseId'];
		$course      = get_post( $course_id );
		if ( ! $course || 'course' !== $course->post_type ) {
			return new \WP_Error( 'course_not_found', __( 'Course not found.', 'sensei-pro' ), [ 'status' => 404 ] );
		}

		$access_period_start = $json_params['startDate'] ?? null;
		$access_period_end   = $json_params['endDate'] ?? null;

		$group_course = $this->group_course_repository->find_by_group_and_course( $group->ID, $course->ID );
		if ( null === $group_course ) {
			try {
				$group_course = $this->group_course_repository->create(
					$group,
					$course,
					$access_period_start,
					$access_period_end
				);
				sensei_log_event( 'group_course_add' );
			} catch ( \RuntimeException $e ) {
				return new \WP_Error( 'group_course_create_failed', $e->getMessage(), [ 'status' => 500 ] );
			}
		}

		$response = Course_Response::from_course_and_access_period( $course, $group_course->get_access_period() );

		return new \WP_REST_Response( $response, 201 );
	}

	/**
	 * Edit the access period of a course added to a group.
	 *
	 * Implementation of the REST API endpoint for editing the access period
	 * of a course in the group.
	 *
	 * @access private
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function edit_group_course( \WP_REST_Request $request ) {
		global $wpdb;
		$params    = $request->get_params();
		$group_id  = (int) $params['group_id'];
		$course_id = (int) $params['course_id'];

		$group = get_post( $group_id );
		if ( ! $group || 'group' !== $group->post_type ) {
			return new \WP_Error( 'group_not_found', __( 'Group not found.', 'sensei-pro' ), [ 'status' => 404 ] );
		}

		$course = get_post( $course_id );
		if ( ! $course || 'course' !== $course->post_type ) {
			return new \WP_Error( 'course_not_found', __( 'Course not found.', 'sensei-pro' ), [ 'status' => 404 ] );
		}

		$group_course = $this->group_course_repository->find_by_group_and_course( $group->ID, $course->ID );

		if ( null === $group_course ) {
			return new \WP_Error(
				'group_course_not_found',
				__( 'Group course not found.', 'sensei-pro' ),
				[ 'status' => 404 ]
			);
		}

		$access_period_start = $params['startDate'] ?? null;
		$access_period_end   = $params['endDate'] ?? null;

		try {
			$group_course     = $this->group_course_repository->update(
				$group_course,
				$access_period_start,
				$access_period_end
			);
			$event_properties = [
				'start_date' => is_null( $access_period_start ) ? 'null' : 'set',
				'end_date'   => is_null( $access_period_end ) ? 'null' : 'set',
			];
			sensei_log_event( 'group_course_access_period_update', $event_properties );
		} catch ( \RuntimeException $e ) {
			return new \WP_Error( 'group_course_update_failed', $e->getMessage(), [ 'status' => 500 ] );
		}

		$response = Course_Response::from_course_and_access_period( $course, $group_course->get_access_period(), true );

		return new \WP_REST_Response( $response, 201 );
	}

	/**
	 * Check if the current user can create a group course.
	 * This is a permission check for the create_group_course() method.
	 *
	 * @param \WP_REST_Request $request Request object.
	 */
	public function group_course_permission_check( \WP_REST_Request $request ): bool {
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
	 * Check if the current user can edit a group course.
	 * This is a permission check for the edit_group_course() method.
	 *
	 * @param \WP_REST_Request $request Request object.
	 */
	public function group_course_edit_permission_check( \WP_REST_Request $request ): bool {
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
	 * Get courses for a group.
	 *
	 * @access private
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_group_courses( \WP_REST_Request $request ) {
		$url_params = $request->get_url_params();
		$group_id   = (int) $url_params['group_id'];

		$group = get_post( $group_id );
		if ( ! $group || 'group' !== $group->post_type ) {
			return new \WP_Error( 'group_not_found', __( 'Group not found.', 'sensei-pro' ), [ 'status' => 404 ] );
		}

		$response = $this->get_groups_and_prepare_response( $group );

		return new \WP_REST_Response( $response );
	}

	/**
	 * Check if the current user can get a list of courses for the given group.
	 *
	 * @param \WP_REST_Request $request Request object.
	 */
	public function get_group_courses_permission_check( \WP_REST_Request $request ): bool {
		$params         = $request->get_url_params();
		$read_group_cap = get_post_type_object( 'group' )->cap->read;

		if ( ! isset( $params['group_id'] ) ) {
			return false;
		}

		$group = get_post( $params['group_id'] );
		if ( $group && ! current_user_can( $read_group_cap, $params['group_id'] ) ) {
			return false;
		}

		return current_user_can( 'manage_sensei' );
	}

	/**
	 * Delete a course from the group.
	 * Implementation of the REST API endpoint for deleting a course from the group.
	 *
	 * @access private
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function delete_group_course( \WP_REST_Request $request ) {
		$url_params = $request->get_url_params();
		$group_id   = (int) $url_params['group_id'];
		$course_id  = (int) $url_params['course_id'];

		$group = get_post( $group_id );
		if ( ! $group || 'group' !== $group->post_type ) {
			return new \WP_Error( 'group_not_found', __( 'Group not found.', 'sensei-pro' ), [ 'status' => 404 ] );
		}

		$course = get_post( $course_id );
		if ( ! $course || 'course' !== $course->post_type ) {
			return new \WP_Error( 'course_not_found', __( 'Course not found.', 'sensei-pro' ), [ 'status' => 404 ] );
		}

		$group_course = $this->group_course_repository->find_by_group_and_course( $group->ID, $course->ID );
		if ( null === $group_course ) {
			return new \WP_Error(
				'group_course_not_found',
				__( 'Group course not found.', 'sensei-pro' ),
				[ 'status' => 404 ]
			);
		}

		$this->group_course_repository->delete( $group, $course );

		$response = Course_Response::from_course_and_access_period( $course, $group_course->get_access_period() );

		return new \WP_REST_Response( $response, 200 );
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
			'create_group_course' => [
				'courseId'  => [
					'description' => 'Course ID to add to the group.',
					'type'        => 'integer',
					'required'    => true,
				],
				'startDate' => [
					'description' => 'Start of the access period for the course.',
					'type'        => [ 'string', 'null' ],
					'required'    => false,
				],
				'endDate'   => [
					'description' => 'End of the access period for the course.',
					'type'        => [ 'string', 'null' ],
					'required'    => false,
				],
			],
			'edit_group_course'   => [
				'startDate' => [
					'description' => 'Start of the access period for the course.',
					'type'        => [ 'string', 'null' ],
					'required'    => false,
				],
				'endDate'   => [
					'description' => 'End of the access period for the course.',
					'type'        => [ 'string', 'null' ],
					'required'    => false,
				],
			],
			'delete_group_course' => [
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

	/**
	 * Fetch group courses and prepare the response.
	 *
	 * @param \WP_Post $group Group post object.
	 *
	 * @return array
	 */
	private function get_groups_and_prepare_response( $group ): array {
		$group_courses = $this->group_course_repository->find_by_group( $group->ID );
		$course_ids    = array_map(
			function ( Group_Course $group_course ) {
				return $group_course->get_course_id();
			},
			$group_courses
		);

		$courses = get_posts(
			[
				'post__in'       => $course_ids,
				'post_type'      => 'course',
				'posts_per_page' => - 1,
				'fields'         => [ 'ID', 'post_title' ],
				'post_status'    => 'all',
			]
		);
		$courses = array_combine( array_column( $courses, 'ID' ), $courses );

		$response = [];
		foreach ( $group_courses as $group_course ) {
			$course     = $courses[ $group_course->get_course_id() ];
			$response[] = Course_Response::from_course_and_access_period( $course, $group_course->get_access_period() );
		}

		return $response;
	}
}
