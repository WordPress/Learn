<?php
/**
 * File for \Sensei_Pro_Block_Visibility\Types\Completed_Any_Course class.
 *
 * @package sensei-pro
 * @since 1.10.0
 */

namespace Sensei_Pro_Block_Visibility\Types;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class that handles the "Visible to users that completed any course" visibility type.
 */
class Completed_Any_Course extends Type {
	/**
	 * Name
	 */
	public function name(): string {
		return 'COMPLETED_ANY_COURSE';
	}

	/**
	 * Label
	 */
	public function label(): string {
		return __( 'Students that completed at least one course', 'sensei-pro' );
	}

	/**
	 * Badge label
	 */
	public function badge_label(): string {
		return __( 'Students that completed at least one course', 'sensei-pro' );
	}

	/**
	 * Retrieves the description.
	 */
	public function description(): string {
		return __( 'This block is visible only to students that completed any course.', 'sensei-pro' );
	}

	/**
	 * Tells if the block should be visible or not depending on the settings.
	 *
	 * @param array $visibility_settings The visibility settings.
	 * @return bool
	 */
	public function is_visible( array $visibility_settings ): bool {
		$current_user_id = get_current_user_id();

		// If there is no course id or user id then can't view the block.
		if ( ! $current_user_id ) {
			return false;
		}

		$completed_query_args = [
			'posts_per_page' => 1,
			'fields'         => 'ids',
		];

		$completed_query = \Sensei_Learner::instance()->get_enrolled_completed_courses_query( $current_user_id, $completed_query_args );
		return $completed_query->found_posts > 0;
	}
}
