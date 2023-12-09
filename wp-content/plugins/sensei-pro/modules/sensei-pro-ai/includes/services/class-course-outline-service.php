<?php
/**
 * File contains Course_Outline_Service class.
 *
 * @package sensei-pro-ai
 */

namespace Sensei_Pro_AI\Services;

use Sensei_Pro\AI_API_Client;

/**
 * Course_Outline_Service class.
 *
 * @since 1.16.0
 */
class Course_Outline_Service {
	/**
	 * The AI API Client.
	 *
	 * @var AI_API_Client
	 */
	private $ai_api_client;

	/**
	 * Constructor.
	 *
	 * @param AI_API_Client $ai_api_client
	 */
	public function __construct( AI_API_Client $ai_api_client ) {
		$this->ai_api_client = $ai_api_client;
	}

	/**
	 * Request a course outline.
	 *
	 * @since 1.16.0
	 *
	 * @param string $course_title The course title.
	 * @param string $course_description The course description.
	 * @param string $intended_audience The intended audience.
	 * @param string $skill_level The skill level.
	 *
	 * @return array The response.
	 */
	public function request( string $course_title, string $course_description, string $intended_audience, string $skill_level = '' ): array {
		$response = $this->ai_api_client->request(
			'make-course-outline',
			[
				'course_title'       => $course_title,
				'course_description' => $course_description,
				'intended_audience'  => $intended_audience,
				'skill_level'        => $skill_level,
			]
		);

		if ( is_wp_error( $response ) ) {
			return $this->create_response( $response->get_error_code(), $response->get_error_message() );
		}

		return $this->create_response( 200, $response );
	}

	/**
	 * Create a response with the given status and data.
	 *
	 * @param string $status The status of the response.
	 * @param mixed  $data   The data of the response.
	 *
	 * @return array The response.
	 */
	private function create_response( $status, $data = null ): array {
		return [
			'status' => $status,
			'data'   => $data,
		];
	}
}
