<?php
/**
 * File containing the class Scd_Ext_Utils.
 *
 * @package sensei-pro
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sensei Content Drip Extension Utilities Class
 *
 * Common functions used by the Content drip extension.
 * Priori to Sensei Pro 1.0.0 this class was wrongly located at `class-scd-ext-utilities.php` instead of `class-scd-ext-utils.php`.
 *
 * @package    WordPress
 * @subpackage Sensei Content Drip
 * @category   Utilities
 * @author     WooThemes
 * @since      1.0.0
 *
 * TABLE OF CONTENTS
 * - get_dripping_lessons_by_type
 * - get_course_users
 */
class Scd_Ext_Utils {
	/**
	 * Returns all the lesson with passed in drip type
	 *
	 * @param string $type
	 * @return array
	 */
	public function get_dripping_lessons_by_type( $type ) {
		// Setup the return value.
		$dripping_lesson_ids = [];

		if ( empty( $type ) ) {
			return $dripping_lesson_ids;
		}

		// If type none return all lessons with no meta query.
		if ( 'none' === $type ) {
			$meta_query = [];
		} else {
			$meta_query = [
				[
					'key'   => '_sensei_content_drip_type',
					'value' => sanitize_key( $type ),
				],
			];
		}

		// Create the lesson query args.
		$lesson_query_args = [
			'post_type'      => 'lesson',
			// phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
			'posts_per_page' => 500,
			'meta_query'     => $meta_query,
		];

		// Fetch all posts matching the arguments.
		$lesson_objects = get_posts( $lesson_query_args );

		// If not empty get the id otherwise move and return and empty array.
		if ( ! empty( $lesson_objects ) ) {
			// Get only the lesson ids.
			foreach ( $lesson_objects as $lesson ) {
				array_push( $dripping_lesson_ids, absint( $lesson->ID ) );
			}
		}

		return $dripping_lesson_ids;
	}

	/**
	 * Return all the user taking a given course
	 *
	 * @param  string $course_id
	 * @return array
	 */
	public function get_course_users( $course_id ) {
		$course_users = [];

		if ( empty( $course_id ) ) {
			return $course_users;
		}

		// Guild up the query parameters to get all users in this course id.
		$activity_query = [
			'post_id' => absint( $course_id ),
			'type'    => 'sensei_course_status',
			'value'   => 'in-progress',
			'field'   => 'user_id',
		];

		$course_users = Sensei_Utils::sensei_activity_ids( $activity_query );

		return $course_users;
	}

	/**
	 * Return a DateTime object for the given lesson ID (bwc support)
	 *
	 * @param string $lesson_id
	 * @return DateTimeImmutable|bool
	 * @throws Exception Thrown if lesson date is wrong.
	 */
	public static function date_from_datestring_or_timestamp( $lesson_id ) {
		$lesson_set_date = get_post_meta( $lesson_id, '_sensei_content_drip_details_date', true );
		$timezone        = Sensei_Content_Drip()->utils->wp_timezone();

		if ( ! ctype_digit( $lesson_set_date ) ) {
			// backwards compatibility for data that's still using the old format.
			$drip_date = new DateTimeImmutable( $lesson_set_date, $timezone );
		} else {
			$drip_date = DateTimeImmutable::createFromFormat( 'U', $lesson_set_date );
			$drip_date = $drip_date->setTimezone( $timezone );
		}

		return $drip_date;
	}

	/**
	 *  Handles which message to show users when the message is both set in a translation, as well as under Sensei -> Settings -> Content Drip.
	 * Translation takes precedence, then setting, then default message
	 *
	 * @param string $default_message
	 * @param string $settings_field
	 * @return string
	 * @from 1.0.7
	 */
	public function check_for_translation( $default_message, $settings_field ) {

		// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- backwards compatibility
		$possible_translation = __( $default_message, 'sensei-pro' );
		// Return the translation if present.
		if ( $possible_translation !== $default_message ) {
			return $possible_translation;
		}
		// If not, check if "Sensei -> Settings -> Content Drip" is set and return that
		// If that is not set either, return the default English string.
		$settings_message = Sensei_Content_Drip()->settings->get_setting( $settings_field );

		return ( empty( $settings_message ) ) ? $default_message : $settings_message;
	}

	/**
	 * Get the current datetime as a DateTimeImmutable object.
	 *
	 * @return DateTimeImmutable
	 */
	public function current_datetime() {
		// Provide light compatibility pre-WP 5.3. This won't be smart about timezones.
		if ( ! function_exists( 'current_datetime' ) ) {
			return new DateTimeImmutable( 'now', $this->wp_timezone() );
		}

		return current_datetime();
	}

	/**
	 * Get the current WP Timezone.
	 *
	 * @return DateTimeZone
	 */
	public function wp_timezone() {
		// Provide light compatibility pre-WP 5.3. This won't be smart about timezones.
		if ( ! function_exists( 'wp_timezone' ) ) {
			return new DateTimeZone( 'UTC' );
		}

		return wp_timezone();
	}
}

