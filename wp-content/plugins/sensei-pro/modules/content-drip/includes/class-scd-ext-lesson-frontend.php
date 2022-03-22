<?php
/**
 * File containing the class Scd_Ext_Lesson_Frontend.
 *
 * @package sensei-pro
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sensei Content Drip ( scd ) Extension Lesson Frontend
 *
 * The class controls all frontend activity relating to sensei lessons.
 *
 * @package WordPress
 * @subpackage Sensei Content Drip
 * @category Core
 * @author WooThemes
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 * - __construct
 * - lesson_content_drip_filter
 * - get_lesson_with_updated_content
 * - get_drip_type_message
 * - generate_absolute_drip_type_message
 * - generate_dynamic_drip_type_message
 * - get_lesson_drip_type
 */
class Scd_Ext_Lesson_Frontend {
	/**
	 * The message shown in place of lesson content
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
		$default_message      = 'This lesson will become available on [date].';
		$settings_field       = 'scd_drip_message';
		$this->message_format = Sensei_Content_Drip()->utils->check_for_translation( $default_message, $settings_field );

		// Hook int all post of type lesson to determine if they should be.
		add_filter( 'the_posts', [ $this, 'lesson_content_drip_filter' ], 1 );

		// Add action to not display comments if the lesson is blocked.
		add_action( 'sensei_pagination', [ $this, 'hide_comments' ] );
	}

	/**
	 * Loops through each post page to confirm if ths content should be hidden
	 *
	 * @since  1.0.0
	 * @param array $lessons
	 * @return array
	 * @uses   the_posts()
	 */
	public function lesson_content_drip_filter( $lessons ) {
		// This should only apply to the front end on single course and lesson pages.
		if ( is_admin() || empty( $lessons ) ) {
			return $lessons;
		}

		// The first post in the array should be of post type lesson.
		if ( 'lesson' !== $lessons[0]->post_type ) {
			return $lessons;
		}

		// Loop through each post and replace the content.
		foreach ( $lessons as $index => $lesson ) {
			if ( Sensei_Content_Drip()->access_control->is_lesson_access_blocked( $lesson->ID ) ) {
				// Change the lesson content accordingly.
				$lessons[ $index ] = $this->get_lesson_with_updated_content( $lesson );

				// Remove hooked.
				global $wp_query;

				if ( $wp_query->is_main_query() && count( $wp_query->posts ) > 0 && 'lesson' === $wp_query->query_vars['post_type'] ) {
					$current_lesson = $wp_query->posts[0];

					if ( isset( $current_lesson->ID ) && $current_lesson->ID === $lesson->ID ) {
						$this->remove_single_lesson_hooks();
					}
				}
			}
		}

		return $lessons;
	}

	/**
	 * Hides the comments if the lesson is blocked by content drip.
	 *
	 * @since  2.0.1
	 * @access private
	 */
	public function hide_comments() {

		if ( 'lesson' !== get_post_type() ) {
			return;
		}

		if ( Sensei_Content_Drip::instance()->access_control->is_lesson_access_blocked( get_the_ID() ) ) {
			remove_action( 'sensei_pagination', [ 'Sensei_Lesson', 'output_comments' ], 90 );
		}
	}

	/**
	 * Replace post content with settings or filtered message
	 * This function acts on the title, content, embedded video and quiz
	 *
	 * @since  1.0.0
	 * @param  WP_Post $lesson
	 * @return WP_Post $lesson
	 */
	public function get_lesson_with_updated_content( $lesson ) {
		// Ensure all things are in place before proceeding.
		if ( empty( $lesson ) ) {
			return $lesson;
		}

		// Get the compiled message text.
		$new_content = $this->get_drip_type_message( $lesson->ID );

		// Wrap the message in sensei notice.
		$new_content = '<div class="sensei-message info">' . esc_html( $new_content ) . '</div>';

		/**
		 * Filter the message a user will see when content is not available.
		 *
		 * @since 1.0.0
		 * @param string $drip_message the message
		 */
		$new_content = wp_kses_post( apply_filters( 'sensei_content_drip_lesson_message', $new_content ) );

		// If a manual excerpt is not set, do not show an auto excerpt, and instead only
		// display the sensei_content_drip_lesson_message.
		if ( empty( $lesson->post_excerpt ) ) {
			$lesson->post_excerpt = $new_content;
			$lesson->post_content = $new_content;
		} else {
			$lesson->post_content = '<p>' . $lesson->post_excerpt . '</p>' . $new_content;
			$lesson->post_excerpt = '<p>' . $lesson->post_excerpt . '</p>' . $new_content;
		}

		// Return the lesson with changed content.
		return $lesson;
	}

	/**
	 * Hide all things on the single lesson
	 *
	 * @since 1.0.5
	 */
	public function remove_single_lesson_hooks() {
		// Disable the current lessons video.
		remove_all_actions( 'sensei_lesson_video' );

		// Hide the lesson quiz notice and quiz buttons.
		remove_all_actions( 'sensei_lesson_quiz_meta' );

		// Hide buttons from sensei version 1.9 onwards.
		remove_action( 'sensei_single_lesson_content_inside_after', [ 'Sensei_Lesson', 'footer_quiz_call_to_action' ] );

		// Hide the lesson quiz notice.
		remove_action( 'sensei_single_lesson_content_inside_before', [ 'Sensei_Lesson', 'user_lesson_quiz_status_message' ], 20 );

		// Hide lesson meta (e.g. Media from Sensei-Media-Items).
		remove_all_actions( 'sensei_lesson_single_meta' );
	}


	/**
	 * Check if the lesson can be made available to the the user at this point
	 * according to the drip meta data
	 *
	 * @since  1.0.0
	 * @param  string $lesson_id
	 * @return bool $dripped
	 */
	public function get_drip_type_message( $lesson_id ) {
		$message = '';

		// Check that the correct data has been passed.
		if ( empty( $lesson_id ) ) {
			// Just return the simple message as the exact message can not be determined without the ID.
			return $message;
		}

		$drip_type = get_post_meta( $lesson_id, '_sensei_content_drip_type', true );
		if ( 'absolute' === $drip_type ) {
			// Call the absolute drip type message creator function which creates a message dependant on the date.
			$message = $this->generate_absolute_drip_type_message( $lesson_id );
		} elseif ( 'dynamic' === $drip_type ) {
			// Call the dynamic drip type message creator function which creates a message dependant on the date.
			$message = $this->generate_dynamic_drip_type_message( $lesson_id );
		}

		return esc_html( $message );
	}

	/**
	 * Absolute drip type: converting the formatted messages into a standard string
	 * depending on the details passed in
	 *
	 * @since  1.0.0
	 * @param  int $lesson_id
	 * @return bool
	 */
	public function generate_absolute_drip_type_message( $lesson_id ) {
		// Get this lessons drip data.
		$lesson_drip_date = Scd_Ext_Utils::date_from_datestring_or_timestamp( $lesson_id );
		$formatted_date   = $lesson_drip_date->format( get_option( 'date_format' ) );

		// Replace the shortcode in the class message_format property set in the constructor.
		if ( strpos( $this->message_format, '[date]' ) ) {
			$absolute_drip_type_message = str_replace( '[date]', $formatted_date, $this->message_format );
		} else {
			$absolute_drip_type_message = $this->message_format . ' ' . $formatted_date;
		}

		return esc_html( $absolute_drip_type_message );
	}

	/**
	 * Dynamic drip type: converting the formatted message into a standard string
	 * depending on the details passed in
	 *
	 * @since  1.0.0
	 * @param  int $lesson_id
	 * @return bool $dripped
	 */
	public function generate_dynamic_drip_type_message( $lesson_id ) {
		$current_user          = wp_get_current_user();
		$user_id               = $current_user->ID;
		$lesson_available_date = Sensei_Content_Drip()->access_control->get_lesson_drip_date( $lesson_id, $user_id );

		if ( ! $lesson_available_date ) {
			return '';
		}

		$formatted_date = $lesson_available_date->format( get_option( 'date_format' ) );

		// Replace string content in the class message_format property set in the constructor.
		$dynamic_drip_type_message = str_replace( '[date]', $formatted_date, $this->message_format );

		return esc_html( $dynamic_drip_type_message );
	}

	/**
	 * Checks the lesson drip type
	 *
	 *  @param  string | int $lesson_id
	 *  @return string none, absolute or dynamic
	 */
	public function get_lesson_drip_type( $lesson_id ) {
		// Basics, checking out the passed in lesson object.
		if ( empty( $lesson_id ) || 'lesson' !== get_post_type( $lesson_id ) ) {
			return 'none';
		}

		// Retrieve the drip type from the lesson.
		$drip_type = get_post_meta( $lesson_id, '_sensei_content_drip_type', true );

		return empty( $drip_type ) ? 'none' : esc_html( $drip_type );
	}
}
