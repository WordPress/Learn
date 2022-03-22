<?php
/**
 * File containing the class Scd_Ext_Quiz_Frontend.
 *
 * @package sensei-pro
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sensei Content Drip ( scd ) Extension Quiz Frontend
 *
 * The class controls all frontend activity relating to blocking access if it is part of a drip campaign
 *
 * @package WordPress
 * @subpackage Sensei Content Drip
 * @category Core
 * @author WooThemes
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 * - __construct
 * - quiz_content_drip_filter
 * - get_quiz_with_updated_content
 * - get_drip_type_message
 * - generate_absolute_drip_type_message
 * - generate_dynamic_drip_type_message
 * - get_quiz_drip_type
 * - get_quiz_lesson_id
 */
class Scd_Ext_Quiz_Frontend {

	/**
	 * The message shown in place of quiz content
	 *
	 * @var    string
	 * @access protected
	 * @since  1.0.0
	 */
	protected $drip_message;

	/**
	 * Constructor function
	 */
	public function __construct() {
		// Set a formatted  message shown to user when the content has not yet dripped.
		$this->message_format = Sensei_Content_Drip()->utils->check_for_translation(
			'This quiz will become available on [date].',
			'scd_drip_quiz_message'
		);
		// Hook int all post of type quiz to determine if they should be.
		add_filter( 'the_posts', [ $this, 'quiz_content_drip_filter' ], 1 );
		// Show SCD Message if Quiz lesson is restricted.
		add_action( 'sensei_single_quiz_content_inside_before', [ $this, 'the_user_status_message' ], 40 );
	}

	/**
	 * Loops through each post page
	 * to confirm if ths content should be hidden
	 *
	 * @since  1.0.0
	 * @param  array $quizzes
	 * @return array
	 * @uses   the_posts()
	 */
	public function quiz_content_drip_filter( $quizzes ) {
		// This should only apply to the front end on single course and quiz pages.
		if ( is_admin() || empty( $quizzes ) || 'quiz' !== $quizzes[0]->post_type ) {
			return $quizzes;
		}

		// Loop through each post and replace the content.
		foreach ( $quizzes as $index => $quiz ) {
			$lesson_id = $this->get_quiz_lesson_id( $quiz->ID );
			if ( Sensei_Content_Drip()->access_control->is_lesson_access_blocked( $lesson_id ) ) {
				// Change the quiz content accordingly.
				$quizzes[ $index ] = $this->get_quiz_with_updated_content( $quiz );
				// User Should not be able to view restricted quiz Questions.
				add_filter( 'sensei_can_user_view_lesson', [ $this, 'can_user_access_quiz_for_lesson' ], 20, 2 );
			}
		}

		return $quizzes;
	}

	/**
	 * Don't show quiz content when part of a lesson that hasn't dripped yet.
	 * Hooked into "sensei_can_user_view_lesson"
	 *
	 * @since 1.0.6
	 * @param boolean $can_user_view_lesson
	 * @param int     $lesson_id
	 * @param int     $user_id
	 * @return bool
	 */
	public function can_user_access_quiz_for_lesson( $can_user_view_lesson, $lesson_id, $user_id = null ) {
		if ( false === empty( $lesson_id ) && Sensei_Content_Drip()->access_control->is_lesson_access_blocked( $lesson_id ) ) {
			return false;
		}

		return $can_user_view_lesson;
	}

	/**
	 * Display SCD Message if this Quiz is part of a lesson that has't dripped yet.
	 * Hooked into "sensei_single_quiz_content_inside_before"
	 *
	 * @since 1.0.6
	 * @param int $quiz_id
	 */
	public function the_user_status_message( $quiz_id ) {
		if ( empty( $quiz_id ) ) {
			return;
		}

		$lesson_id = Sensei()->quiz->get_lesson_id( $quiz_id );
		$user_id   = get_current_user_id();

		if ( Sensei_Content_Drip()->access_control->is_lesson_access_blocked( $lesson_id ) &&
			! Sensei_Content_Drip()->access_control->sensei_should_block_lesson( $lesson_id, $user_id ) ) {
			$drip_message_body = $this->get_drip_type_message( $quiz_id );
			if ( empty( $drip_message_body ) ) {
				return;
			}

			$message = '<div class="sensei-message info">' . esc_html( $drip_message_body ) . '</div>';
			if ( ! empty( Sensei()->frontend->messages ) ) {
				$message .= Sensei()->frontend->messages;
			}

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $message;
		}
	}

	/**
	 * Replace post content with settings or filtered message
	 * This function acts on the title, content, embedded video and quiz
	 *
	 * @since  1.0.0
	 * @param  WP_Post $quiz
	 * @return WP_Post
	 */
	public function get_quiz_with_updated_content( $quiz ) {
		// Ensure all things are in place before proceeding.
		if ( empty( $quiz ) ) {
			return $quiz;
		}

		// Get the compiled message text.
		$new_content = $this->get_drip_type_message( $quiz->ID );

		// Wrap the message in sensei notice.
		$new_content = '<div class="sensei-message info">' . esc_html( $new_content ) . '</div>';

		/**
		 * Filter the message a user will see when content is not available.
		 *
		 * @since 1.0.0
		 * @param string $drip_message the message
		 */
		$new_content        = wp_kses_post( apply_filters( 'sensei_content_drip_quiz_message', $new_content ) );
		$quiz->post_content = '<p>' . wp_trim_words( $quiz->post_content, 20 ) . '</p>' . $new_content;

		// Set the excerpt to be a trimmed down version of the full content if it is empty.
		if ( empty( $quiz->post_excerpt ) ) {
			$quiz->post_excerpt = '<p>' . wp_trim_words( $quiz->post_content, 20 ) . '</p>' . $new_content;
		} else {
			$quiz->post_excerpt = '<p>' . $quiz->post_excerpt . '&hellip;</p>' . $new_content;
		}

		// Hide the quiz questions.
		remove_all_actions( 'sensei_quiz_questions' );

		// Hide the quiz quiz notice and quiz buttons.
		remove_all_actions( 'sensei_pagination' );

		return $quiz;
	}

	/**
	 * Check if  the quiz can be made available to the the user at this point
	 * according to the drip meta data
	 *
	 * @since  1.0.0
	 * @param  string $quiz_id
	 * @return bool
	 */
	public function get_drip_type_message( $quiz_id ) {
		$message = '';

		// Check that the correct data has been passed.
		if ( empty( $quiz_id ) ) {
			// Just return the simple message as the exact message can not be determined without the ID.
			return $message;
		}

		$drip_type = get_post_meta( $quiz_id, '_sensei_content_drip_type', true );
		if ( 'absolute' === $drip_type ) {
			// Call the absolute drip type message creator function which creates a message dependant on the date.
			$message = $this->generate_absolute_drip_type_message( $quiz_id );
		} elseif ( 'dynamic' === $drip_type ) {
			// Call the dynamic drip type message creator function which creates a message dependant on the date.
			$message = $this->generate_dynamic_drip_type_message( $quiz_id );
		}

		return esc_html( $message );
	}

	/**
	 * Absolute drip type: converting the formatted messages into a standard string depending on the details passed in
	 *
	 * @since  1.0.0
	 * @param  int $quiz_id
	 * @return bool
	 */
	public function generate_absolute_drip_type_message( $quiz_id ) {
		$absolute_drip_type_message = '';

		// Get this quizs drip data.
		$quiz_drip_date = Scd_Ext_Utils::date_from_datestring_or_timestamp( $quiz_id );

		$formatted_date = $quiz_drip_date->format( Sensei_Content_Drip()->get_date_format_string() );

		// Replace the shortcode in the class message_format property set in the constructor.
		if ( strpos( $this->message_format, '[date]' ) ) {
			$absolute_drip_type_message = str_replace( '[date]', $formatted_date, $this->message_format );
		} else {
			$absolute_drip_type_message = $this->message_format . ' ' . $formatted_date;
		}

		return esc_html( $absolute_drip_type_message );
	}

	/**
	 * Dynamic drip type: converting the formatted message into a standard string depending on the details passed in.
	 *
	 * @since  1.0.0
	 * @param int $quiz_id
	 * @return bool
	 */
	public function generate_dynamic_drip_type_message( $quiz_id ) {
		$lesson_id                 = $this->get_quiz_lesson_id( $quiz_id );
		$current_user              = wp_get_current_user();
		$user_id                   = $current_user->ID;
		$dynamic_drip_type_message = '';
		$quiz_available_date       = Sensei_Content_Drip()->access_control->get_lesson_drip_date( $lesson_id, $user_id );

		if ( ! $quiz_available_date ) {
			return '';
		}

		$formatted_date = date_i18n( Sensei_Content_Drip()->get_date_format_string(), $quiz_available_date->getTimestamp() );

		// Replace string content in the class message_format property set in the constructor.
		$dynamic_drip_type_message = str_replace( '[date]', $formatted_date, $this->message_format );

		return esc_html( $dynamic_drip_type_message );
	}

	/**
	 * This function checks the quiz drip type
	 *
	 * @param  string | int $quiz_id
	 * @return string none, absolute or dynamic
	 */
	public function get_quiz_drip_type( $quiz_id ) {
		// Basics, checking out the passed in quiz object.
		if ( empty( $quiz_id ) || 'quiz' !== get_post_type( $quiz_id ) ) {
			return 'none';
		}

		// Retrieve the drip type from the quiz.
		$drip_type = get_post_meta( $quiz_id, '_sensei_content_drip_type', true );

		// Send back the type string.
		return empty( $drip_type ) ? 'none' : esc_html( $drip_type );
	}

	/**
	 * Search the lesson meta to find which lesson this quiz belongs to
	 *
	 * @since  1.0.1
	 * @param  int $quiz_id
	 * @return int
	 */
	public function get_quiz_lesson_id( $quiz_id ) {
		// Look for the quiz's lesson.
		$query_args = [
			'post_type'  => 'lesson',
			'meta_key'   => '_lesson_quiz',
			'meta_value' => absint( $quiz_id ),
		];
		$lessons    = new WP_Query( $query_args );

		if ( ! isset( $lessons->posts ) || empty( $lessons->posts ) ) {
			return false;
		}

		$quiz_lesson = $lessons->posts[0];

		return absint( $quiz_lesson->ID );
	}
}
