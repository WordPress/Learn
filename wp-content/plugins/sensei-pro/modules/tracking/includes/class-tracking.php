<?php
/**
 * File containing the class \Sensei_Pro_Tracking\Tracking.
 *
 * @package sensei-pro-tracking
 * @since   1.10.0
 */

namespace Sensei_Pro_Tracking;

use Sensei_Data_Port_Manager;
use Sensei_Data_Port_Utilities;
use Sensei_Pro_Advanced_Quiz\Quiz_Timer;
use Sensei_Pro_Co_Teachers\Co_Teachers;
use Sensei_Pro_Course_Expiration\Course_Expiration;
use Sensei_WC_Paid_Courses\Dependency_Checker;
use WP_Query;

/**
 * Main tracking class.
 *
 * @internal
 */
class Tracking {
	/**
	 * Class instance.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Fetch an instance of the class.
	 *
	 * @internal
	 */
	public static function instance(): self {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Class constructor.
	 *
	 * @internal
	 */
	private function __construct() {
		// Silence is golden.
	}

	/**
	 * Initialize the class and add hooks.
	 *
	 * @internal
	 */
	public function init(): void {
		add_filter( 'sensei_usage_tracking_data', [ $this, 'add_usage_tracking_data' ] );
	}

	/**
	 * Add the usage tracking data to be sent.
	 *
	 * @internal
	 *
	 * @param array $usage_data The usage tracking data.
	 *
	 * @return array
	 */
	public function add_usage_tracking_data( array $usage_data ): array {
		return array_merge(
			$usage_data,
			[
				'lesson_content_drip'  => $this->get_lesson_content_drip_count(),
				'lesson_quiz_timer'    => $this->get_lesson_quiz_timer_count(),
				'course_pricing'       => $this->get_course_pricing_count(),
				'course_co_teachers'   => $this->get_course_co_teachers_count(),
				'course_access_period' => $this->get_course_access_period_count(),
				'groups'               => $this->get_groups_count(),
				'cohorts'              => $this->get_cohorts_count(),
			]
		);
	}

	/**
	 * Get the total number of published lessons where content drip is enabled.
	 *
	 * @return int
	 */
	private function get_lesson_content_drip_count(): int {
		$query = new WP_Query(
			[
				'posts_per_page' => 1,
				'post_type'      => 'lesson',
				'post_status'    => 'publish',
				'fields'         => 'ids',
				'meta_query'     => [
					[
						'key'     => '_sensei_content_drip_type',
						'value'   => [ '', 'none' ],
						'compare' => 'NOT IN',
					],
				],
			]
		);

		return $query->found_posts;
	}

	/**
	 * Get the total number of published lessons where quiz timer is enabled.
	 *
	 * @return int
	 */
	private function get_lesson_quiz_timer_count(): int {
		$quizzes_query = new WP_Query(
			[
				'posts_per_page' => -1,
				'post_type'      => 'quiz',
				'post_status'    => 'publish',
				'fields'         => 'id=>parent',
				'meta_query'     => [
					[
						'key'   => Quiz_Timer::META_ENABLE_TIMER,
						'value' => 1,
					],
					[
						'key'     => Quiz_Timer::META_QUIZ_TIMER,
						'value'   => 1,
						'compare' => '>=',
						'type'    => 'NUMERIC',
					],
				],
			]
		);

		$lesson_ids = wp_list_pluck( $quizzes_query->posts, 'post_parent' );
		if ( ! $lesson_ids ) {
			return 0;
		}

		$lessons_query = new WP_Query(
			[
				'posts_per_page' => 1,
				'post_type'      => 'lesson',
				'post_status'    => 'publish',
				'fields'         => 'ids',
				'post__in'       => $lesson_ids,
			]
		);

		return $lessons_query->found_posts;
	}

	/**
	 * Get the total number of published courses that have one or more products associated.
	 *
	 * @return int
	 */
	private function get_course_pricing_count(): int {
		if ( ! Dependency_Checker::woocommerce_dependency_is_met() ) {
			return 0;
		}

		$query = new WP_Query(
			[
				'posts_per_page' => 1,
				'post_type'      => 'course',
				'post_status'    => 'publish',
				'fields'         => 'ids',
				'post__not_in'   => [ $this->get_demo_course_id() ],
				'meta_query'     => [
					[
						'key' => '_course_woocommerce_product',
					],
				],
			]
		);

		return $query->found_posts;
	}

	/**
	 * Get the total number of published courses that have one or more co-teachers associated.
	 *
	 * @return int
	 */
	private function get_course_co_teachers_count(): int {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe and rare query.
		$courses_with_co_teacher = $wpdb->get_col(
			$wpdb->prepare(
				"
				SELECT DISTINCT meta_value
				FROM $wpdb->usermeta
				WHERE meta_key = %s
				AND meta_value != %d
				",
				Co_Teachers::COTEACHER_META_KEY,
				$this->get_demo_course_id()
			)
		);

		if ( ! $courses_with_co_teacher ) {
			return 0;
		}

		$courses_query = new WP_Query(
			[
				'posts_per_page' => 1,
				'post_type'      => 'course',
				'post_status'    => 'publish',
				'fields'         => 'ids',
				'post__in'       => $courses_with_co_teacher,
			]
		);

		return $courses_query->found_posts;
	}

	/**
	 * Get the total number of published courses that use the access period feature.
	 *
	 * @return int
	 */
	private function get_course_access_period_count(): int {
		$query = new WP_Query(
			[
				'posts_per_page' => 1,
				'post_type'      => 'course',
				'post_status'    => 'publish',
				'fields'         => 'ids',
				'post__not_in'   => [ $this->get_demo_course_id() ],
				'meta_query'     => [
					'relation' => 'OR',
					[
						'key'     => Course_Expiration::START_TYPE,
						'value'   => 'immediately',
						'compare' => '!=',
					],
					[
						'key'     => Course_Expiration::EXPIRATION_TYPE,
						'value'   => 'no-expiration',
						'compare' => '!=',
					],
				],
			]
		);

		return $query->found_posts;
	}

	/**
	 * Get the total number of groups.
	 *
	 * @return int
	 */
	private function get_groups_count(): int {
		$groups_query = new WP_Query(
			[
				'posts_per_page' => 1,
				'post_type'      => 'group',
				'post_status'    => 'publish',
				'fields'         => 'ids',
			]
		);

		return $groups_query->found_posts;
	}

	/**
	 * Get the total number of groups that have at least one published course
	 * and one student assigned to them.
	 *
	 * @return int
	 */
	private function get_cohorts_count(): int {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe and rare query.
		return (int) $wpdb->get_var(
			"
			SELECT COUNT(DISTINCT gp.ID)
			FROM $wpdb->posts AS gp
			INNER JOIN {$wpdb->prefix}sensei_pro_groups_courses AS gc
			    ON gp.ID = gc.group_id
			INNER JOIN {$wpdb->prefix}sensei_pro_groups_students AS gs
			    ON gp.ID = gs.group_id
			INNER JOIN $wpdb->posts AS cp
			    ON gc.course_id = cp.ID AND cp.post_status = 'publish'
			WHERE gp.post_type = 'group'
			AND gp.post_status = 'publish'
			"
		);
	}

	/**
	 * Get the demo course ID.
	 *
	 * @return int|null
	 */
	private function get_demo_course_id(): ?int {
		if ( ! method_exists( Sensei_Data_Port_Utilities::class, 'get_demo_course_id' ) ) {
			return null;
		}

		return Sensei_Data_Port_Utilities::get_demo_course_id();
	}
}
