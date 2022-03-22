<?php
/**
 * File containing the class Ordering_Question_Type.
 *
 * @package sensei-pro
 */

namespace Sensei_Pro_Advanced_Quiz\Advanced_Questions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Ordering_Question_Type is the entry point for ordering question type.
 */
class Ordering_Question_Type {

	/**
	 * The ordering questions data.
	 *
	 * @var array
	 */
	private $questions_data;

	/**
	 * Script and stylesheet loading.
	 *
	 * @var Sensei_Assets
	 */
	private $assets;

	/**
	 * Ordering_Question_Type constructor.
	 *
	 * @param string $module_name Name of the module.
	 */
	public function __construct( $module_name ) {
		$this->assets = \Sensei_Pro\Modules\assets_loader( $module_name );
		new Ordering_Question_REST_Extensions();
		new Ordering_Question_Grader( $this );

		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_assets' ] );
		add_filter( 'sensei_question_types', [ $this, 'add_question_type' ] );
		add_filter( 'sensei_quiz_enable_block_based_editor', [ $this, 'enable_block_editor' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'register_frontend_assets' ] );
		add_action( 'sensei_quiz_question_inside_after', [ $this, 'render_ordering_question' ], 9 );
		add_action( 'sensei_single_quiz_questions_after', [ $this, 'enqueue_frontend_assets' ] );

		$this->questions_data = [];
	}

	/**
	 * Enqueues block editor assets.
	 *
	 * @access private
	 */
	public function enqueue_block_editor_assets() {
		$this->assets->enqueue( 'sensei-ordering-question-type', 'blocks/ordering-question-type.js', [ 'wp-block-editor', 'wp-element', 'wp-hooks', 'wp-i18n', 'wp-keycodes', 'wp-polyfill' ] );
		$this->assets->enqueue( 'sensei-ordering-question-type-style', 'blocks/ordering-question-type.css' );
	}

	/**
	 * Enqueues questions data in the frontend.
	 *
	 * @access private
	 */
	public function enqueue_frontend_assets() {
		if ( empty( $this->questions_data ) ) {
			return;
		}

		$this->assets->enqueue( 'sensei-ordering-question-frontend-js', 'frontend/ordering-question.js' );
		$this->assets->enqueue( 'sensei-ordering-question-frontend-css', 'frontend/ordering-question-styles.css' );

		$this->assets->enqueue( 'sensei-ordering-question-type-style', 'blocks/ordering-question-type.css' );

		wp_add_inline_script(
			'sensei-ordering-question-frontend-js',
			sprintf(
				'window.sensei_ordering_questions = %s',
				wp_json_encode( $this->questions_data )
			),
			'before'
		);
	}

	/**
	 * Adds ordering question type.
	 *
	 * @access private
	 *
	 * @param array $types Existing types.
	 *
	 * @return array Modified types.
	 */
	public function add_question_type( array $types ) : array {
		$new_types = [ 'ordering' => __( 'Ordering', 'sensei-pro' ) ];

		return array_merge( $types, $new_types );
	}

	/**
	 * Advanced quiz types are available for the quiz editor only.
	 *
	 * @access private
	 *
	 * @return bool
	 */
	public function enable_block_editor() : bool {
		return true;
	}

	/**
	 * Adds frontend assets for quizzes.
	 */
	public function register_frontend_assets() {
		$this->assets->register( 'sensei-ordering-question-frontend-js', 'frontend/ordering-question.js', [ 'wp-element', 'wp-primitives' ] );
		$this->assets->register( 'sensei-ordering-question-frontend-css', 'frontend/ordering-question-styles.css' );
	}

	/**
	 * Renders the data for the ordering question types.
	 *
	 * @param int $question_id The id of the question that needs to be rendered.
	 */
	public function render_ordering_question( int $question_id ) {
		$question_type = Sensei()->question->get_question_type( $question_id );
		if ( 'ordering' !== $question_type ) {
			return;
		}

		$lesson_id     = Sensei()->quiz->get_lesson_id( get_the_ID() );
		$right_answer  = get_post_meta( $question_id, '_question_right_answer', true );
		$saved_answer  = Sensei()->quiz->get_user_question_answer( $lesson_id, $question_id, get_current_user_id() );
		$question_data = [
			'id'      => $question_id,
			'answers' => $right_answer,
		];

		// If user has saved answers then sort the answers the way user
		// sorted them before.
		if ( $saved_answer ) {

			$user_answers = [];
			foreach ( $saved_answer as $id ) {
				foreach ( $question_data['answers'] as $answer ) {
					if ( $answer['id'] === $id ) {
						$user_answers[] = $answer;
					}
				}
			}

			$question_data['answers'] = $user_answers;
		} elseif ( count( $question_data['answers'] ) > 1 ) {
			do {
				shuffle( $question_data['answers'] );
			} while ( $question_data['answers'] === $right_answer );
		}

		$this->questions_data[ $question_id ]['question']           = $question_data;
		$this->questions_data[ $question_id ]['user_saved_answers'] = ! empty( $saved_answer );

		echo '<div id="sensei-ordering-question-' . esc_attr( $question_id ) . '" class="ordering-question-answers-container"></div>';
	}

	/**
	 * Marks the correct and wrong answers of a user.
	 *
	 * @param int   $question_id       The id of the question that needs to be rendered.
	 * @param array $correct_orderings An array of booleans which are true if the ordering was correct.
	 */
	public function mark_user_correct_orderings( int $question_id, array $correct_orderings ) {
		if ( empty( $this->questions_data[ $question_id ]['question']['answers'] ) || false === $this->questions_data[ $question_id ]['user_saved_answers'] ) {
			return;
		}

		$user_answers = $this->questions_data[ $question_id ]['question']['answers'];

		if ( count( $user_answers ) !== count( $correct_orderings ) ) {
			return;
		}

		$user_answers = array_map(
			function( $answer, $index ) use ( $correct_orderings ) {
				$answer['correct'] = $correct_orderings[ $index ];
				return $answer;
			},
			$user_answers,
			array_keys( $user_answers )
		);

		$this->questions_data[ $question_id ]['question']['answers'] = $user_answers;
	}
}
