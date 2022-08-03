<?php
/**
 * File containing the class \Sensei_Pro_Student_Groups\Rest_Api\Responses\Course_Response.
 *
 * @package student-groups
 * @since   1.4.0
 */

namespace Sensei_Pro_Student_Groups\Rest_Api\Responses;

use DateTime;
use Sensei_Pro_Student_Groups\Models\Access_Period;

/**
 * Class Course_Response.
 *
 * @since 1.4.0
 */
class Course_Response {
	/**
	 * Prepares course representation.
	 *
	 * @param \WP_Post      $course        Course.
	 * @param Access_Period $access_period Access period.
	 * @param boolean       $flat          Set true if you need a flat array in response.
	 *
	 * @return array
	 */
	public static function from_course_and_access_period( \WP_Post $course, Access_Period $access_period, bool $flat = false ) {
		$access_period = [
			'startDate' => self::get_formatted_date( $access_period->get_start_date() ),
			'endDate'   => self::get_formatted_date( $access_period->get_end_date() ),
			'status'    => $access_period->get_status(),
		];
		return array_merge(
			[
				'id'    => $course->ID,
				'title' => $course->post_title,
			],
			$flat ? $access_period : [ 'accessPeriod' => $access_period ]
		);
	}

	/**
	 * Returns formatted date.
	 *
	 * @param ?DateTime $date
	 *
	 * @return string|null
	 */
	private static function get_formatted_date( $date ): ?string {
		if ( ! $date ) {
			return null;
		}

		return $date->format( 'Y-m-d' );
	}
}
