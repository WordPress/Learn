<?php
/**
 * File containing the class Ordering_Question_REST_Extensions
 *
 * @package sensei-pro
 */

namespace Sensei_Pro_Advanced_Quiz\Advanced_Questions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Ordering_Question_REST_Extensions is responsible for extending quiz lesson API.
 */
class Ordering_Question_REST_Extensions {
	use \Sensei_REST_API_Question_Helpers_Trait;

	/**
	 * Ordering_Question_REST_Extensions constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'add_hooks' ] );
	}

	/**
	 * Adds hooks for the REST extensions.
	 *
	 * @access private
	 */
	public function add_hooks() {
		add_filter( 'sensei_question_type_specific_properties', [ $this, 'get_ordering_question_properties' ], 10, 3 );
		add_action( 'sensei_rest_api_question_saved', [ $this, 'save_ordering_question' ], 10, 3 );
		add_filter( 'sensei_rest_api_schema_single_question', [ $this, 'add_ordering_question_schema' ] );
		add_filter( 'sensei_rest_api_schema_question_type', [ $this, 'get_question_type_schema' ], 10, 2 );
	}

	/**
	 * Saves ordering question specific properties.
	 *
	 * @access private
	 *
	 * @param int|WP_Error $result        Post ID on success or WP_Error on failure.
	 * @param string       $question_type The question type.
	 * @param array        $question      The question JSON arguments.
	 */
	public function save_ordering_question( $result, string $question_type, array $question ) {
		if ( 'ordering' !== $question_type || is_wp_error( $result ) || empty( $result ) ) {
			return;
		}

		if ( empty( $question['answer']['answers'] ) ) {
			delete_post_meta( $result, '_question_right_answer' );
			return;
		}

		foreach ( $question['answer']['answers'] as $i => $answer ) {
			if ( ! isset( $answer['label'] ) || empty( $answer['label'] ) ) {
				unset( $question['answer']['answers'][ $i ] );
				continue;
			}
			$question['answer']['answers'][ $i ]['id'] = md5( $answer['label'] );
		}

		update_post_meta( $result, '_question_right_answer', $question['answer']['answers'] );
	}

	/**
	 * Expands lesson quiz API to return ordering question properties
	 *
	 * @access private
	 *
	 * @param array    $type_specific_properties The properties of the question.
	 * @param string   $question_type            The question type.
	 * @param \WP_Post $question                 The question post.
	 */
	public function get_ordering_question_properties( array $type_specific_properties, string $question_type, \WP_Post $question ) : array {
		if ( 'ordering' !== $question_type ) {
			return $type_specific_properties;
		}

		$answers = get_post_meta( $question->ID, '_question_right_answer', true );
		$answers = array_map(
			function ( $answer ) {
				unset( $answer['id'] );
				return $answer;
			},
			empty( $answers ) ? [] : $answers
		);

		$type_specific_properties['answer']['answers'] = $answers;

		return $type_specific_properties;
	}

	/**
	 * Expands lesson quiz schema to include ordering questions.
	 *
	 * @access private
	 *
	 * @param array $single_question_schema The existing schema.
	 */
	public function add_ordering_question_schema( array $single_question_schema ) : array {
		$single_question_schema['oneOf'][] = $this->get_ordering_question_schema();

		return $single_question_schema;
	}

	/**
	 * Expands lesson quiz schema to include ordering questions.
	 *
	 * @access private
	 *
	 * @param array  $schema The existing schema.
	 * @param string $type   The question type\.
	 */
	public function get_question_type_schema( array $schema, string $type ) : array {
		if ( 'ordering' === $type ) {
			return $this->get_ordering_question_schema();
		}

		return $schema;
	}

	/**
	 * Helper method which returns the ordering question schema.
	 */
	private function get_ordering_question_schema() : array {
		$ordering_properties = [
			'type'    => [
				'type'     => 'string',
				'pattern'  => 'ordering',
				'required' => true,
			],
			'answer'  => [
				'properties' => [
					'answers' => [
						'type'        => 'array',
						'description' => 'The answers for the ordering question',
						'items'       => [
							'type'       => 'object',
							'properties' => [
								'label' => [
									'type'        => 'string',
									'description' => 'Content of the answer',
								],
							],
						],
					],
				],
			],
			'options' => [
				'properties' => [
					'answerFeedback' => [
						'type'        => [ 'string', 'null' ],
						'description' => 'Feedback to show quiz takers once quiz is submitted',
					],
				],
			],
		];

		return [
			'title'      => 'Ordering question',
			'type'       => 'object',
			'properties' => array_merge_recursive( $this->get_common_question_properties_schema(), $ordering_properties ),
		];
	}
}
