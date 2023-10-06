<?php
/**
 * File containing the class Course_Outline_Controller.
 *
 * @package sensei-pro
 */

namespace Sensei_Pro_AI\Rest_Api\Controllers;

use Sensei_Pro_AI\Services\Course_Outline_Service;

/**
 * Course_Outline_Controller class.
 *
 * @since 1.16.0
 */
class Course_Outline_Controller extends \WP_REST_Controller {
	/**
	 * Routes namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'sensei-pro-ai/v1';

	/**
	 * Routes prefix.
	 *
	 * @var string
	 */
	protected $rest_base = 'course-outline';

	/**
	 * Course Outline service.
	 *
	 * @var Course_Outline_Service
	 */
	private $course_outline_service;

	/**
	 * Class constructor.
	 *
	 * @param Course_Outline_Service $course_outline_service The Course Outline service.
	 */
	public function __construct( Course_Outline_Service $course_outline_service ) {
		$this->course_outline_service = $course_outline_service;
	}

	/**
	 * Register the REST API routes.
	 *
	 * @since 1.16.0
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			$this->rest_base,
			[
				[
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'request_course_outline' ],
					'permission_callback' => [ $this, 'can_request_course_outline' ],
					'args'                => [
						'course_title'       => [
							'description' => __( 'The course title.', 'sensei-pro' ),
							'required'    => true,
							'type'        => 'string',
						],
						'course_description' => [
							'description' => __( 'The course description.', 'sensei-pro' ),
							'required'    => true,
							'type'        => 'string',
						],
						'intended_audience'  => [
							'description' => __( 'The intended course audience', 'sensei-pro' ),
							'type'        => 'string',
							'required'    => true,
						],
						'skill_level'        => [
							'description' => __( 'The audience skill level.', 'sensei-pro' ),
							'type'        => 'string',
							'enum'        => [
								'beginner',
								'intermediate',
								'advanced',
							],
						],
					],
				],
			]
		);
	}

	/**
	 * Request the course outline from the AI API and return the response.
	 *
	 * @since 1.16.0
	 *
	 * @param \WP_REST_Request $request The request object.
	 *
	 * @return \WP_REST_Response
	 */
	public function request_course_outline( \WP_REST_Request $request ): \WP_REST_Response {
		$params             = $request->get_params();
		$course_title       = $params['course_title'];
		$course_description = $params['course_description'];
		$intended_audience  = $params['intended_audience'];
		$skill_level        = $params['skill_level'] ?? '';

		sensei_log_event( 'request_course_outline' );

		try {
			$response = $this->course_outline_service->request( $course_title, $course_description, $intended_audience, $skill_level );

			return new \WP_REST_Response(
				$response['data'],
				$response['status']
			);
		} catch ( \Exception $e ) {
			return new \WP_REST_Response(
				$e->getMessage(),
				500
			);
		}
	}

	/**
	 * Check if the current user can request the course outline.
	 *
	 * @param \WP_REST_Request $request The request object.
	 *
	 * @return bool
	 */
	public function can_request_course_outline( \WP_REST_Request $request ): bool {
		return is_user_logged_in();
	}
}
