<?php
/**
 * File containing the Co_Teachers_Rest_Api class.
 *
 * @package sensei-pro
 * @since   1.9.0
 */

namespace Sensei_Pro_Co_Teachers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class containing the REST API definition to manage co-teachers.
 */
class Co_Teachers_Rest_Api extends \WP_REST_Controller {

	/**
	 * Class instance.
	 *
	 * @var Co_Teachers_Rest_Api
	 */
	private static $instance;

	/**
	 * Main Co-Teachers instace.
	 *
	 * @var Co_Teachers
	 */
	private $co_teachers;

	/**
	 * Routes namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'sensei-pro-internal/v1';

	/**
	 * Routes prefix.
	 *
	 * @var string
	 */
	protected $rest_base = 'coteachers';


	/**
	 * Retrieve the Co_Teachers_Rest_Api instance.
	 */
	public static function instance(): Co_Teachers_Rest_Api {
		if ( ! self::$instance ) {
			self::$instance = new self( Co_Teachers::instance() );
		}

		return self::$instance;
	}

	/**
	 * Class constructor.
	 *
	 * @param Co_Teachers $co_teachers The main Co_Teachers class.
	 */
	public function __construct( Co_Teachers $co_teachers ) {
		$this->co_teachers = $co_teachers;
	}

	/**
	 * Initializes the class and adds all filters and actions.
	 */
	public static function init() {
		$instance = self::instance();

		add_action( 'rest_api_init', [ $instance, 'register_routes' ] );
	}

	/**
	 * Register the REST API routes.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			"/{$this->rest_base}/(?P<course_id>[\d]+)/",
			[
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_coteachers' ],
					'permission_callback' => [ $this, 'can_manage_coteachers' ],
					'args'                => [
						'course_id' => [
							'required' => true,
							'type'     => 'integer',
						],
					],
				],
				[
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'set_coteachers' ],
					'permission_callback' => [ $this, 'can_manage_coteachers' ],
					'args'                => [
						'course_id' => [
							'required' => true,
							'type'     => 'integer',
						],
						'users'     => [
							'required' => true,
							'type'     => 'array',
							'items'    => [
								'type' => 'integer',
							],
						],
					],
				],
			]
		);
	}

	/**
	 * Check if the current user can manage co-teachers.
	 *
	 * @param \WP_REST_Request $request The request object.
	 *
	 * @return bool
	 */
	public function can_manage_coteachers( \WP_REST_Request $request ): bool {
		$course_id = $request->get_param( 'course_id' );
		return $this->co_teachers->can_current_user_manage_coteachers( $course_id );
	}

	/**
	 * Get the co-teachers for a course.
	 *
	 * @param \WP_REST_Request $request The request object.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_coteachers( \WP_REST_Request $request ): \WP_REST_Response {
		$course_id = $request->get_param( 'course_id' );

		$co_teachers = $this->co_teachers->get_course_coteachers_ids( $course_id );

		return new \WP_REST_Response( $co_teachers );
	}

	/**
	 * Set the co-teachers for a course.
	 *
	 * @param \WP_REST_Request $request The request object.
	 *
	 * @return \WP_REST_Response
	 */
	public function set_coteachers( \WP_REST_Request $request ): \WP_REST_Response {
		$course_id = $request->get_param( 'course_id' );
		$users     = $request->get_param( 'users' );

		$this->co_teachers->set_course_coteachers_ids( $course_id, $users );

		return new \WP_REST_Response( $users );
	}
}
