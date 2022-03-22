<?php
/**
 * Sensei Quiz timer.
 *
 * @package sensei-pro
 */

namespace Sensei_Pro_Advanced_Quiz;

use Sensei_Assets;
use Sensei_Course_Theme_Option;
use Sensei_Utils;
use WP_Post;
use Sensei_WC_Paid_Courses\Sensei_WC_Paid_Courses;

/**
 * Sensei Quiz timer class.
 */
class Quiz_Timer {

	const META_ENABLE_TIMER         = '_enable_quiz_timer';
	const META_QUIZ_TIMER           = '_quiz_timer';
	const META_QUIZ_TIMER_STARTED   = 'quiz_timer_started';
	const META_QUIZ_TIMER_SUBMITTED = 'quiz_timer_submitted';
	const QUIZ_TIMER_HTML           = '<div id="sensei-quiz-timer"></div>';

	/**
	 * Script and stylesheet loading.
	 *
	 * @var Sensei_Assets
	 */
	private $assets;

	/**
	 * Quiz Timer constructor.
	 *
	 * @param string $module_name Name of the module.
	 */
	public function __construct( $module_name ) {
		$this->assets = \Sensei_Pro\Modules\assets_loader( $module_name );
		new Quiz_Timer_Block();

		add_action( 'sensei_single_quiz_questions_before', [ $this, 'the_quiz_timer' ], 20 );
		add_action( 'sensei_user_quiz_submitted', [ $this, 'save_quiz_submitted_meta' ], 10, 2 );
		add_filter( 'sensei_use_sensei_template', [ $this, 'maybe_skip_single_quiz_template' ], 90, 1 );
		add_filter( 'the_content', [ $this, 'quiz_start_page' ], 10 );
		add_action( 'template_redirect', [ $this, 'start_quiz_timer' ] );
		add_action( 'template_redirect', [ $this, 'setup_learning_mode_start_page' ] );
		add_action( 'sensei_user_lesson_reset', [ $this, 'reset_user_data' ], 10, 2 );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_assets' ], 21 );

		// Expand lesson-quiz REST endpoint.
		add_action( 'sensei_rest_api_lesson_quiz_update', [ $this, 'save_timer_meta' ], 10, 2 );
		add_filter( 'sensei_rest_api_lesson_quiz_response', [ $this, 'add_quiz_timer' ], 10, 2 );

	}

	/**
	 * Save the timer value as post meta.
	 *
	 * @access private
	 *
	 * @param WP_Post $lesson         The lesson.
	 * @param array   $request_params The request parameters.
	 *
	 * @return void
	 */
	public function save_timer_meta( WP_Post $lesson, $request_params ) {
		$quiz_id = Sensei()->lesson->lesson_quizzes( $lesson->ID );

		if (
			isset( $request_params['options'] )
			&& array_key_exists( 'enable_quiz_timer', $request_params['options'] )
		) {
			$enable_quiz_timer = $request_params['options']['enable_quiz_timer'];
			$timer_value       = $request_params['options']['timer_value'] ?? null;

			update_post_meta( $quiz_id, self::META_ENABLE_TIMER, $enable_quiz_timer );
			update_post_meta( $quiz_id, self::META_QUIZ_TIMER, $timer_value );
		}
	}

	/**
	 * Add quiz settings to response.
	 *
	 * @access private
	 *
	 * @param array   $quiz_data The request parameters.
	 * @param WP_Post $quiz      The lesson.
	 *
	 * @return array
	 */
	public function add_quiz_timer( array $quiz_data, WP_Post $quiz ) : array {
		$enable_quiz_timer                         = get_post_meta( $quiz->ID, self::META_ENABLE_TIMER, true );
		$quiz_data['options']['enable_quiz_timer'] = $enable_quiz_timer ?? false;

		$timer_value                         = (int) get_post_meta( $quiz->ID, self::META_QUIZ_TIMER, true );
		$quiz_data['options']['timer_value'] = $timer_value > 0 ? $timer_value : null;

		return $quiz_data;
	}

	/**
	 * Save the quiz submit time as user data.
	 *
	 * @param int $user_id The user id.
	 * @param int $quiz_id The quiz id.
	 */
	public function save_quiz_submitted_meta( int $user_id, int $quiz_id ) {

		$lesson_id    = Sensei()->quiz->get_lesson_id( $quiz_id );
		$time_started = Sensei_Utils::get_user_data(
			self::META_QUIZ_TIMER_STARTED,
			$lesson_id,
			$user_id
		);

		if ( $time_started ) {
			Sensei_Utils::add_user_data(
				self::META_QUIZ_TIMER_SUBMITTED,
				$lesson_id,
				time(),
				$user_id
			);
		}

	}

	/**
	 * Find a block in the block hierarchy.
	 *
	 * @param string $name Block name.
	 * @param array  $blocks Blocks.
	 *
	 * @return mixed|null Block if found.
	 */
	private function find_block( $name, $blocks ) {
		foreach ( $blocks as $block ) {
			if ( $name === $block['blockName'] ) {
				return $block;
			}
			$inner_block = $this->find_block( $name, $block['innerBlocks'] );
			if ( $inner_block ) {
				return $inner_block;
			}
		}

		return null;
	}

	/**
	 * Enqueue Frontend Assets.
	 */
	public function enqueue_frontend_assets() {

		if ( ! $this->should_enable_quiz_timer() ) {
			return;
		}

		$this->assets->enqueue( 'sensei-quiz-timer-frontend', 'blocks/quiz-timer-frontend.js', [ 'wp-element', 'wp-dom-ready' ], '1.0' );

		$time         = get_post_meta( get_the_ID(), self::META_QUIZ_TIMER, true );
		$time_started = Sensei_Utils::get_user_data(
			self::META_QUIZ_TIMER_STARTED,
			Sensei()->quiz->get_lesson_id( get_the_ID() ),
			get_current_user_id()
		);
		$time_elapsed = $time_started ? time() - $time_started : 0;
		$time_left    = $time < $time_elapsed ? 0 : $time - $time_elapsed;

		wp_localize_script(
			'sensei-quiz-timer-frontend',
			'sensei_quiz_timer_params',
			[
				'time_left'       => $time_left,
				'time'            => $time,
				'is_not_started'  => $this->has_unstarted_quiz_timer(),
				'is_course_theme' => Sensei_WC_Paid_Courses::should_use_learning_mode(),
			]
		);
	}

	/**
	 * Render the base HTML of the timer.
	 */
	public function the_quiz_timer() {
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		echo self::QUIZ_TIMER_HTML;
	}

	/**
	 * Skip single quiz template if the timer still needs to be started.
	 *
	 * @param mixed $use_templates
	 *
	 * @return false|mixed
	 */
	public function maybe_skip_single_quiz_template( $use_templates ) {

		return $this->has_unstarted_quiz_timer() ? false : $use_templates;
	}

	/**
	 * Check if the quiz timer should be enabled.
	 *
	 * @return bool
	 */
	public function should_enable_quiz_timer(): bool {

		// Make sure we are on the quiz single post.
		if ( ! is_single() || get_post_type() !== 'quiz' ) {
			return false;
		}

		// Make sure the time meta is set.
		$enable      = get_post_meta( get_the_ID(), self::META_ENABLE_TIMER, true );
		$timer_value = get_post_meta( get_the_ID(), self::META_QUIZ_TIMER, true );

		if ( ! $enable || ! $timer_value ) {
			return false;
		}

		// Make sure the lesson is in progress.
		$lesson_id     = Sensei()->quiz->get_lesson_id( get_the_ID() );
		$lesson_status = Sensei_Utils::user_lesson_status( $lesson_id );

		if ( $lesson_status && 'in-progress' !== $lesson_status->comment_approved ) {
			return false;
		}

		return true;

	}

	/**
	 * Check if the quiz has a timer, and it hasn't been started yet.
	 *
	 * @return bool
	 */
	public function has_unstarted_quiz_timer() {
		if ( ! is_single() || get_post_type() !== 'quiz' ) {
			return false;
		}

		$time         = get_post_meta( get_the_ID(), self::META_QUIZ_TIMER, true );
		$time_started = Sensei_Utils::get_user_data(
			self::META_QUIZ_TIMER_STARTED,
			Sensei()->quiz->get_lesson_id( get_the_ID() ),
			get_current_user_id()
		);

		return $time && ! $time_started;
	}

	/**
	 * Maybe get the start page for the quiz, where the learner can start the timer.
	 *
	 * @param mixed $content
	 *
	 * @return mixed|void
	 */
	public function quiz_start_page( $content ) {
		if ( ! $this->should_enable_quiz_timer() || ! $this->has_unstarted_quiz_timer() ) {
			return $content;
		}

		return $this->get_quiz_start_page_content();
	}

	/**
	 * Echo the start page for the quiz, where the learner can start the timer.
	 */
	public function quiz_start_page_e() {
		echo $this->get_quiz_start_page_content();
	}

	/**
	 * Get quiz start page content.
	 *
	 * @return string
	 */
	private function get_quiz_start_page_content() {
		$time         = get_post_meta( get_the_ID(), self::META_QUIZ_TIMER, true );
		$time_minutes = self::format_as_minutes( $time );

		$question_count = count( Sensei()->quiz->get_questions( get_the_ID() ) ?? [] );

		return '
			<div>
				<div class="sensei-lms-quiz-timer__start-page">
					<form method="POST">
						<div class="sensei-lms-quiz-timer__start-page__question-count">'
							// translators: the number of questions.
							. wp_kses_post( sprintf( _n( 'Quiz length: %d question', 'Quiz length: %d questions', $question_count, 'sensei-pro' ), $question_count ) ) .
						'</div>

						<div class="sensei-lms-quiz-timer__start-page__time-limit-notice">
							<h3>'
								// translators: the time limit in minutes.
								. wp_kses_post( sprintf( __( 'Time limit: %s', 'sensei-pro' ), $time_minutes ) ) .
							'</h3>
							<div>'
								// translators: the time limit in minutes.
								. wp_kses_post( sprintf( __( 'You will have %s available to finish this quiz.', 'sensei-pro' ), $time_minutes ) ) .
							'</div>
						</div>
						<div class="wp-block-button sensei-lms-quiz-timer__start-page__start-button">'
							. wp_nonce_field( 'sensei_start_quiz_timer', 'sensei_start_quiz_timer_nonce' ) .
							'<button name="start_quiz_timer" class="wp-block-button__link">'
								. esc_attr__( 'Start Quiz', 'sensei-pro' ) .
							'</button>
						</div>
					</form>
				</div>
			</div>
		';
	}

	/**
	 * Start quiz timer form submission handler.
	 *
	 * @param double $time Time in seconds?.
	 */
	public static function format_as_minutes( $time ) {
		$mins = floor( $time / 60 );
		// translators: The number of minute(s).
		return sprintf( _n( '%s minute', '%s minutes', $mins, 'sensei-pro' ), $mins );
	}

	/**
	 * Start quiz timer form submission handler.
	 *
	 * @access private
	 */
	public function start_quiz_timer() {

		if ( ! isset( $_POST['start_quiz_timer'] )
			|| ! isset( $_POST['sensei_start_quiz_timer_nonce'] )
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Do not change the nonce.
			|| ! wp_verify_nonce( wp_unslash( $_POST['sensei_start_quiz_timer_nonce'] ), 'sensei_start_quiz_timer' ) ) {
			return;
		}

		$this->set_user_start_time();

		// To properly load the content, we need to refresh the page.
		// This is needed because the `sensei_use_sensei_template` filter has already fired.
		wp_safe_redirect( get_permalink( get_the_ID() ) );
		exit;
	}

	/**
	 * Show start page instead of quiz content in learning mode when the timer is not started.
	 *
	 * @access private
	 */
	public function setup_learning_mode_start_page() {
		if ( Sensei_WC_Paid_Courses::should_use_learning_mode() && $this->should_enable_quiz_timer() && $this->has_unstarted_quiz_timer() ) {
			add_action( 'sensei_single_quiz_content_inside_before', [ $this, 'quiz_start_page_e' ] );
			add_action( 'sensei_can_user_view_lesson', '__return_false' );
			remove_filter( 'the_content', [ $this, 'quiz_start_page' ] );
		}
	}

	/**
	 * Sets the user quiz start time to the current timestamp if not already started.
	 */
	private function set_user_start_time() {
		$lesson_id = Sensei()->quiz->get_lesson_id( get_the_ID() );
		$user_id   = get_current_user_id();
		$started   = Sensei_Utils::get_user_data( self::META_QUIZ_TIMER_STARTED, $lesson_id, $user_id );

		if ( $started ) {
			return;
		}

		Sensei_Utils::update_user_data( self::META_QUIZ_TIMER_STARTED, $lesson_id, time(), $user_id );
	}

	/**
	 * Reset the user data that is associated with the quiz timer.
	 *
	 * @param int $user_id   The user id.
	 * @param int $lesson_id The lesson id.
	 */
	public function reset_user_data( int $user_id, int $lesson_id ) {

		Sensei_Utils::delete_user_data(
			self::META_QUIZ_TIMER_STARTED,
			$lesson_id,
			$user_id
		);

		Sensei_Utils::delete_user_data(
			self::META_QUIZ_TIMER_SUBMITTED,
			$lesson_id,
			$user_id
		);

	}
}
