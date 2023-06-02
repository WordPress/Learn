<?php
/**
 * File for \Sensei_Pro_Block_Visibility\Types\Enrolled_To_Any_Course class.
 *
 * @package sensei-pro
 * @since 1.10.0
 */

namespace Sensei_Pro_Block_Visibility\Types;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class that handles the "Visible to users that are enrolled to any course" visibility type.
 */
class Enrolled_To_Any_Course extends Type {
	/**
	 * Name
	 */
	public function name(): string {
		return 'ENROLLED_TO_ANY_COURSE';
	}

	/**
	 * Label
	 */
	public function label(): string {
		return __( 'Students that are enrolled in at least one course', 'sensei-pro' );
	}

	/**
	 * Badge label
	 */
	public function badge_label(): string {
		return __( 'Enrolled to any course', 'sensei-pro' );
	}

	/**
	 * Retrieves the description.
	 */
	public function description(): string {
		return __( 'This block is visible only to students that are enrolled in any course.', 'sensei-pro' );
	}

	/**
	 * Tells if the block should be visible or not depending on the settings.
	 *
	 * @param array $visibility_settings The visibility settings.
	 * @return bool
	 */
	public function is_visible( array $visibility_settings ): bool {
		$current_user_id = get_current_user_id();

		// If there is no user id then can't view the block.
		if ( ! $current_user_id ) {
			return false;
		}

		$enrolled_query_args = [
			'posts_per_page' => 1,
			'fields'         => 'ids',
		];

		$enrolled_query = \Sensei_Learner::instance()->get_enrolled_active_courses_query( $current_user_id, $enrolled_query_args );
		return $enrolled_query->found_posts > 0;
	}
}
