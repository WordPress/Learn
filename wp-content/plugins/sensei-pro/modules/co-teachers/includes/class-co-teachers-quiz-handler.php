<?php
/**
 * File containing the Co_Teachers_Quiz_Handler class.
 *
 * @package sensei-pro
 * @since   1.9.0
 */

namespace Sensei_Pro_Co_Teachers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class containing all special handling needed for Co-Teachers quizzes and
 * questions.
 */
class Co_Teachers_Quiz_Handler {

	/**
	 * Class instance.
	 *
	 * @var Co_Teachers_Quiz_Handler
	 */
	private static $instance;

	/**
	 * Main Co-Teachers instance.
	 *
	 * @var Co_Teachers
	 */
	private $co_teachers;

	/**
	 * Retrieve the singleton instance.
	 */
	public static function instance(): Co_Teachers_Quiz_Handler {
		if ( ! self::$instance ) {
			self::$instance = new self( Co_Teachers::instance() );
		}

		return self::$instance;
	}

	/**
	 * Class constructor.
	 *
	 * @param Co_Teachers $co_teachers The main Co_Teachers instance.
	 */
	public function __construct( Co_Teachers $co_teachers ) {
		$this->co_teachers = $co_teachers;
		$this->assets      = \Sensei_Pro\Modules\assets_loader( Co_Teachers::MODULE_NAME );
	}

	/**
	 * Initializes the class and adds all filters and actions.
	 */
	public static function init() {
		$instance = self::instance();

		add_filter( 'add_post_meta', [ $instance, 'set_question_author_on_quiz_assignment_for_coteachers' ], 10, 4 );
		add_filter( 'wp_insert_post_data', [ $instance, 'do_not_change_question_author_on_update_for_coteachers' ], 10, 4 );
		add_filter( 'user_has_cap', [ $instance, 'grant_coteacher_access_to_questions' ], 10, 3 );
		add_action( 'enqueue_block_editor_assets', [ $instance, 'enqueue_editor_assets' ] );
		add_action( 'rest_api_init', [ $instance, 'add_coteacher_field_to_rest_api' ] );
	}

	/**
	 * Hooks into `add_post_meta` and updates the author of Questions created by
	 * Co-Teachers to be the main teacher.
	 *
	 * @param int    $post_id    The post ID.
	 * @param string $meta_key   The meta key.
	 * @param mixed  $meta_value The meta value.
	 */
	public function set_question_author_on_quiz_assignment_for_coteachers( $post_id, $meta_key, $meta_value ) {
		// Only continue if the first _quiz_id is being set on a question.
		if (
			'_quiz_id' !== $meta_key
			|| get_post_type( $post_id ) !== 'question'
			|| metadata_exists( 'post', $post_id, '_quiz_id' )
		) {
			return;
		}

		// Only continue if the question's current author is a co-teacher on the quiz being assigned.
		$quiz_id = intval( $meta_value );
		if ( ! $this->co_teachers->is_coteacher( get_post_field( 'post_author', $post_id ), get_post( $quiz_id ) ) ) {
			return;
		}

		// Update the author on the question.
		$lesson_id = $quiz_id ? Sensei()->quiz->get_lesson_id( $quiz_id ) : null;
		$course_id = $lesson_id ? Sensei()->lesson->get_course_id( $lesson_id ) : null;
		if ( $course_id ) {
			wp_update_post(
				[
					'ID'          => $post_id,
					'post_author' => get_post_field( 'post_author', $course_id ),
				]
			);
		}
	}

	/**
	 * Hooks into `wp_insert_post_data` and prevents the author of a question
	 * from changing if we are editing as a co-teacher.
	 *
	 * @param array     $data                The data to be saved.
	 * @param array     $postarr             The post data.
	 * @param array     $unsanitized_postarr Unsanitized post data.
	 * @param bool|null $update              Whether the action is for an existing post being updated or not.
	 *
	 * @return array The post data with the correct author.
	 */
	public function do_not_change_question_author_on_update_for_coteachers( $data, $postarr, $unsanitized_postarr, $update = null ) {
		// Compatibility for WP < 6.0.
		if ( null === $update ) {
			$update = ! empty( $postarr['ID'] );
		}

		// Only continue if we are updating a question and we are not already the author.
		if ( ! $update ) {
			return $data;
		}

		$post_id         = $postarr['ID'];
		$post_type       = get_post_type( $post_id );
		$original_author = get_post_field( 'post_author', $post_id );
		if ( 'question' !== $post_type || get_current_user_id() === $original_author ) {
			return $data;
		}

		// We are editing the question, so we must have the capability to do so.
		// Now, check if we only have that capability because we are a
		// co-teacher. If so, then we never want to change the question author.
		remove_filter( 'user_has_cap', [ $this, 'grant_coteacher_access_to_questions' ], 10 );
		$has_cap = current_user_can( 'edit_question', $post_id );
		add_filter( 'user_has_cap', [ $this, 'grant_coteacher_access_to_questions' ], 10, 3 );

		if ( ! $has_cap ) {
			// Do not change the author.
			$data['post_author'] = get_post_field( 'post_author', $post_id );
		}

		return $data;
	}

	/**
	 * Hooks into `user_has_cap` and grants Co-Teachers access to questions.
	 * They should only have access if they are a Co-Teacher or the main teacher
	 * on all courses that include the given question.
	 *
	 * @param array $allcaps All the user's capabilities.
	 * @param array $caps    Required capability.
	 * @param array $args    Arguments for the capability check.
	 *
	 * @return array The user's capabilities.
	 */
	public function grant_coteacher_access_to_questions( $allcaps, $caps, $args ) {
		$requested_cap = $args[0] ?? null;
		$user_id       = $args[1] ?? null;
		$post_id       = $args[2] ?? null;

		// Only continue if the user is trying to edit a question that they do not already own.
		if (
			'edit_question' !== $requested_cap
			|| ! $user_id
			|| ! $post_id
			|| 'question' !== get_post_type( $post_id )
			|| get_post_field( 'post_author', $post_id ) === $user_id
		) {
			return $allcaps;
		}

		// Only grant access if the user is a Co-Teacher on all courses that include the question.
		$course_ids = $this->get_courses_with_question( $post_id );
		foreach ( $course_ids as $course_id ) {
			if ( ! $this->co_teachers->is_coteacher( $user_id, get_post( $course_id ) ) ) {
				return $allcaps;
			}
		}

		// Grant the user access to the question.
		$allcaps['edit_others_questions'] = true;

		return $allcaps;
	}

	/**
	 * Enqueue assets needed for Quiz and Question handling in the block editor.
	 */
	public function enqueue_editor_assets() {
		$this->assets->enqueue( 'sensei-co-teachers-quiz-script', 'admin-co-teachers-quiz.js', [ 'wp-hooks' ] );
	}

	/**
	 * Add the `is_coteacher` flag to the REST API response for lessons and
	 * courses.
	 */
	public function add_coteacher_field_to_rest_api() {
		register_rest_field(
			[ 'lesson', 'course', 'quiz' ],
			'is_coteacher',
			[
				'get_callback'    => function ( $post ) {
					return Co_Teachers::instance()->is_coteacher( get_current_user_id(), get_post( $post['id'] ) );
				},
				'update_callback' => null,
				'schema'          => [
					'description' => __( 'Whether the current user is a co-teacher of the course.', 'sensei-pro' ),
					'type'        => 'boolean',
				],
			]
		);
	}

	/**
	 * Get all of the Courses that includes the given question in one of its
	 * quizzes.
	 *
	 * @param int $question_id The question ID.
	 *
	 * @return int[] The Course IDs.
	 */
	private function get_courses_with_question( $question_id ) {
		$course_ids = [];

		$quiz_ids = get_post_meta( $question_id, '_quiz_id', false );
		foreach ( $quiz_ids as $quiz_id ) {
			$lesson_id = $quiz_id ? Sensei()->quiz->get_lesson_id( $quiz_id ) : null;
			$course_id = $lesson_id ? Sensei()->lesson->get_course_id( $lesson_id ) : null;

			if ( $course_id ) {
				$course_ids[] = $course_id;
			}
		}

		return array_unique( $course_ids );
	}
}
