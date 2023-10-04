<?php
/**
 * File contains Chat_GPT_Controller class.
 *
 * @package sensei-pro-ai
 * @since 1.14.0
 */

namespace Sensei_Pro_AI\Rest_Api\Controllers;

use Sensei_Pro_AI\Services\Question_Generator_Service;
use WP_Post;
use WP_Query;
use WP_REST_Controller;
use WP_REST_Response;
use WP_REST_Server;
use WP_Error;

/**
 * Chat_GPT_Controller class.
 *
 * @since 1.14.0
 */
class Chat_GPT_Controller extends WP_REST_Controller {

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
	protected $rest_base = 'gpt';

	/**
	 * Question_Generator_Service instance.
	 *
	 * @var Question_Generator_Service
	 */
	private $question_generator_service;

	/**
	 * Chat_GPT_Controller constructor.
	 *
	 * @param Question_Generator_Service $question_generator_service Question_Generator_Service instance.
	 *
	 * @since 1.14.0
	 */
	public function __construct( Question_Generator_Service $question_generator_service ) {
		$this->question_generator_service = $question_generator_service;
	}

	/**
	 * Register the REST API endpoints for sensei gpt.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			$this->rest_base . '/questions',
			[
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'create_question_based_on_text' ],
					'permission_callback' => [ $this, 'get_question_permission_check' ],
					'args'                => $this->get_args_schema( 'create_question_based_on_text' ),
				],
			]
		);
	}

	/**
	 * Get questions from text.
	 *
	 * @since 1.14.0
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_question_based_on_text( $request ) {
		update_user_meta( get_current_user_id(), \Sensei_Pro_AI\Sensei_Pro_AI::USER_QUESTION_AI_USED_META_KEY, true );
		$params = $request->get_params();
		$text   = $params['text'];
		$count  = intval( $params['count'] );

		sensei_log_event( 'gpt_question_create' );

		try {
			$questions = $this->question_generator_service->generate_questions( $text, $count );

			return new WP_REST_Response(
				$questions['data'],
				$questions['status']
			);
		} catch ( \Exception $e ) {
			return new WP_Error(
				'sensei_pro_ai_gpt_error',
				$e->getMessage()
			);
		}
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
			'create_question_based_on_text' => [
				'text'  => [
					'description' => __( 'The text to create questions from.', 'sensei-pro' ),
					'type'        => 'string',
					'minLength'   => 10,
					'required'    => true,
				],
				'count' => [
					'description' => __( 'The number of questions to create.', 'sensei-pro' ),
					'type'        => 'integer',
					'minimum'     => 1,
					'maximum'     => 3,
					'required'    => true,
				],
			],
		];
		return $schema_list[ $key ];
	}

	/**
	 * Check if the current user can perform a lesson question creation operations.
	 *
	 * @access private
	 *
	 * @return boolean
	 */
	public function get_question_permission_check() {
		return current_user_can( 'edit_lessons' );
	}

	/**
	 * Get questions from chat gpt api.
	 *
	 * @param string $text Text to generate question from.
	 * @param int    $count Number of questions to generate.
	 *
	 * @return array
	 * @throws \Exception Error.
	 */
	private function get_questions_from_chat_gpt_api( $text, $count ) {
		$token = apply_filters( 'sensei_pro_chat_gpt_token', '' );

		$gpt_prompt =
			"Return only a JSON array with $count multiple choice questions based on the following text.
			The question objects should be with properties \"question\", \"answers\", \"correct_answer_index\",
			\"correct_answer_explanation\", \"wrong_answer_feedback\". Do not add any text after or before the json.\n\n$text";

		$payload  = [
			'model'       => 'gpt-3.5-turbo',
			'temperature' => 0.3,
			'max_tokens'  => 500,
			'messages'    => [
				[
					'role'    => 'user',
					'content' => $gpt_prompt,
				],
			],
		];
		$headers  = [
			'Authorization' => 'Bearer ' . $token,
			'content-Type'  => 'application/json',
			'credentials'   => 'include',
		];
		$response = wp_remote_post(
			'https://api.openai.com/v1/chat/completions',
			[
				'method'  => 'POST',
				'headers' => $headers,
				'timeout' => 60,
				'body'    => wp_json_encode( $payload ),
			]
		);

		if ( is_wp_error( $response ) ) {
			$error_message = wp_json_encode( $response->get_error_messages() );
			throw new \Exception( __( 'Something went wrong', 'sensei-pro' ) . ' : ' . $error_message );
		}
		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		return $body['choices'][0]['message']['content'];
	}
}
