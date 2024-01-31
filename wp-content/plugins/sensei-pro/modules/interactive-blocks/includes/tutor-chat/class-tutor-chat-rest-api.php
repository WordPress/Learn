<?php
/**
 * File containing the class Tutor_Chat_Rest_Api.
 *
 * @package sensei-pro
 */

namespace Sensei_Pro_Interactive_Blocks\Tutor_Chat;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class containing the REST API definition for Tutor Chat.
 *
 * @since 1.15.1
 */
class Tutor_Chat_Rest_Api extends \WP_REST_Controller {
	/**
	 * Routes namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'sensei-pro-interactive-blocks/v1';

	/**
	 * Routes prefix.
	 *
	 * @var string
	 */
	protected $rest_base = 'tutor-chat';

	/**
	 * Tutor Chat service.
	 *
	 * @var Tutor_Chat_Service
	 */
	private $tutor_chat_service;

	/**
	 * Class constructor.
	 *
	 * @param Tutor_Chat_Service $tutor_chat_service Tutor Chat service.
	 */
	public function __construct( Tutor_Chat_Service $tutor_chat_service ) {
		$this->tutor_chat_service = $tutor_chat_service;
	}

	/**
	 * Register the REST API routes.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			"/{$this->rest_base}",
			[
				[
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'request_tutor_chat' ],
					'permission_callback' => [ $this, 'can_request_tutor_chat' ],
					'args'                => [
						'question'      => [
							'description' => __( 'The question the student replies.', 'sensei-pro' ),
							'required'    => true,
							'type'        => 'string',
							'maxLength'   => 100,
							'minLength'   => 1,
						],
						'answer'        => [
							'description' => __( 'The correct answer provided by the teacher.', 'sensei-pro' ),
							'required'    => true,
							'type'        => 'string',
							'maxLength'   => 100,
							'minLength'   => 1,
						],
						'context'       => [
							'description' => __( 'The context of the question to help tutor with thinking up a clue.', 'sensei-pro' ),
							'type'        => 'string',
							'required'    => true,
							'maxLength'   => 200,
						],
						'student_reply' => [
							'description' => __( 'The last student\'s to the question.', 'sensei-pro' ),
							'type'        => 'string',
							'required'    => true,
							'maxLength'   => 100,
							'minLength'   => 1,
						],
					],
				],
			]
		);
	}

	/**
	 * Check if the current user can request tutor chat.
	 *
	 * @param \WP_REST_Request $request The request object.
	 *
	 * @return bool
	 */
	public function can_request_tutor_chat( \WP_REST_Request $request ): bool {
		return is_user_logged_in();
	}

	/**
	 * Request remote API to get a hint.
	 *
	 * @param \WP_REST_Request $request The request object.
	 *
	 * @return \WP_REST_Response
	 */
	public function request_tutor_chat( \WP_REST_Request $request ): \WP_REST_Response {
		$params        = $request->get_params();
		$question      = $params['question'];
		$answer        = $params['answer'];
		$context       = $params['context'];
		$student_reply = $params['student_reply'];

		if ( function_exists( 'sensei_log_event' ) ) {
			sensei_log_event( 'request_tutor_chat' );
		}

		try {
			$response = $this->tutor_chat_service->request( $question, $answer, $context, $student_reply );

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
}
