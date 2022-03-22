<?php
/**
 * File containing the class Ordering_Question_Grader.
 *
 * @package sensei-pro
 */

namespace Sensei_Pro_Advanced_Quiz\Advanced_Questions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Ordering_Question_Grader is responsible for grading of ordering questions.
 */
class Ordering_Question_Grader {

	/**
	 * The ordering question type.
	 *
	 * @var Ordering_Question_Type
	 */
	private $ordering_question_type;

	/**
	 * Ordering_Question_Type constructor.
	 *
	 * @param Ordering_Question_Type $ordering_question_type The ordering question type.
	 */
	public function __construct( Ordering_Question_Type $ordering_question_type ) {
		$this->ordering_question_type = $ordering_question_type;

		add_filter( 'sensei_autogradable_question_types', [ $this, 'add_autogradable_question_type' ] );
		add_filter( 'sensei_pre_grade_question_auto', [ $this, 'grade_ordering_question' ], 10, 4 );
		add_filter( 'sensei_grading_display_quiz_question', [ $this, 'modify_ordering_question_before_display' ], 10, 5 );
		add_filter( 'sensei_question_show_answers', [ $this, 'calculate_correct_orderings' ], 10, 5 );
	}

	/**
	 * Add 'ordering' type to auto-gradable question types
	 *
	 * @param array $types An array of default auto-gradable types.
	 *
	 * @return array
	 */
	public function add_autogradable_question_type( $types ) {
		$types[] = 'ordering';

		return $types;
	}

	/**
	 * Grades an ordering question.
	 *
	 * @param int|false    $question_grade The grade of the question.
	 * @param int          $question_id    The question id.
	 * @param string       $question_type  The question type.
	 * @param string|array $answer         User supplied question answer.
	 *
	 * @return bool|int
	 */
	public function grade_ordering_question( $question_grade, $question_id, $question_type, $answer ) {
		if ( 'ordering' !== $question_type ) {
			return false;
		}

		$right_answer = get_post_meta( $question_id, '_question_right_answer', true );

		if ( is_array( $right_answer ) && count( $right_answer ) === count( $answer ) ) {
			$correct = true;
			foreach ( $answer as $index => $check_answer ) {
				if ( $check_answer !== $right_answer[ $index ]['id'] ) {
					$correct = false;
				}
			}

			// If correct then get the grade and return it.
			if ( $correct ) {
				$question_grade = \Sensei()->question->get_question_grade( $question_id );
			}
		}
		return $question_grade;
	}

	/**
	 * Calculates which orderings where correct.
	 *
	 * @access private
	 *
	 * @param bool $show_correct_answers Whether to show the answer to the question.
	 * @param int  $question_id          Question ID.
	 * @param int  $quiz_id              Quiz ID.
	 * @param int  $lesson_id            Lesson ID.
	 * @param int  $user_id              User ID.
	 *
	 * @return bool Whether to show the answer to the question.
	 */
	public function calculate_correct_orderings( $show_correct_answers, $question_id, $quiz_id, $lesson_id, $user_id ) {
		if ( false === $show_correct_answers ) {
			return $show_correct_answers;
		}

		$question_type = Sensei()->question->get_question_type( $question_id );

		if ( 'ordering' !== $question_type ) {
			return $show_correct_answers;
		}

		$user_answers = Sensei()->quiz->get_user_answers( $lesson_id, $user_id );

		if ( empty( $user_answers ) || empty( $user_answers[ $question_id ] ) ) {
			return $show_correct_answers;
		}

		$user_question_answer = $user_answers[ $question_id ];
		$right_answer         = get_post_meta( $question_id, '_question_right_answer', true );

		if ( is_array( $right_answer ) && count( $right_answer ) === count( $user_question_answer ) ) {
			$user_results = [];

			foreach ( $right_answer as $index => $answer ) {
				$user_results[] = $answer['id'] === $user_question_answer[ $index ];
			}

			$this->ordering_question_type->mark_user_correct_orderings( $question_id, $user_results );
		}

		return $show_correct_answers;
	}

	/**
	 * Modify the ordering question in the grading page before it is displayed.
	 *
	 * @param null|array   $display_values      Default display values.
	 * @param string       $question_type       The question type.
	 * @param int          $question_id         Question id.
	 * @param string|array $right_answer        The right answer for the question.
	 * @param string|array $user_answer_content User supplied answer.
	 *
	 * @return null|array
	 */
	public function modify_ordering_question_before_display( $display_values, $question_type, $question_id, $right_answer, $user_answer_content ) {
		if ( 'ordering' !== $question_type ) {
			return $display_values;
		}

		// Add ordering display logic here.
		$processed_user_answer = [];
		foreach ( $user_answer_content as $content ) {
			foreach ( $right_answer as $answer ) {
				if ( $answer['id'] === $content ) {
					$processed_user_answer[] = $answer['label'];
					break;
				}
			}
		}

		$processed_right_answer = array_map(
			function ( $item ) {
				return $item['label'];
			},
			$right_answer
		);

		return [
			'type_name'           => 'ordering',
			'right_answer'        => $processed_right_answer,
			'user_answer_content' => $processed_user_answer,
			'grade_type'          => 'auto-grade',
		];
	}
}
