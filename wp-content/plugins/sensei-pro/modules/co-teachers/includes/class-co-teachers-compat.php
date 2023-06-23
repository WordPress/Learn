<?php
/**
 * File containing the Co_Teachers_Compat class.
 *
 * @package sensei-pro
 * @since   1.9.0
 */

namespace Sensei_Pro_Co_Teachers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class containing all necessary compatibility code for Co-Teachers.
 */
class Co_Teachers_Compat {

	/**
	 * Class instance.
	 *
	 * @var Co_Teachers_Compat
	 */
	private static $instance;

	/**
	 * Retrieve the singleton instance.
	 */
	public static function instance(): Co_Teachers_Compat {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Class constructor.
	 */
	public function __construct() {
		// Silence is golden.
	}

	/**
	 * Initializes the class and adds all filters and actions.
	 */
	public static function init() {
		$instance = self::instance();

		add_filter( 'wp_insert_post_data', [ $instance, 'set_quiz_author_on_create' ], 10, 4 );
	}

	/**
	 * Hooks into `wp_insert_post_data` and updates the quiz author to the lesson author on create.
	 *
	 * NOTE: this was added to Sensei core in https://github.com/Automattic/sensei/pull/6129
	 * and can be removed when our minimum supported version includes that change.
	 *
	 * @param mixed     $data                The data to be saved.
	 * @param mixed     $postarr             The post data.
	 * @param mixed     $unsanitized_postarr Unsanitized post data.
	 * @param bool|null $update              Whether the action is for an existing post being updated or not.
	 * @return mixed
	 */
	public function set_quiz_author_on_create( $data, $postarr, $unsanitized_postarr, $update = null ) {
		// Only continue if this filter doesn't already exist in core.
		if ( false !== has_filter( 'wp_insert_post_data', [ Sensei()->quiz, 'set_quiz_author_on_create' ] ) ) {
			return $data;
		}

		// Compatibility for WP < 6.0.
		if ( null === $update ) {
			$update = ! empty( $postarr['ID'] );
		}

		// Only handle new posts.
		if ( $update ) {
			return $data;
		}

		// Only handle quizzes.
		if ( 'quiz' !== $data['post_type'] ) {
			return $data;
		}

		// Set author to lesson author.
		$lesson_id = $postarr['post_parent'] ?? null;
		if ( $lesson_id ) {
			$lesson = get_post( $lesson_id );
			if ( $lesson ) {
				$data['post_author'] = $lesson->post_author;
			}
		}

		return $data;
	}
}
