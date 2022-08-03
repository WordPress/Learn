<?php
/**
 * File for \Sensei_Pro_Block_Visibility\Types\Groups class.
 *
 * @package sensei-pro
 * @since 1.5.0
 */

namespace Sensei_Pro_Block_Visibility\Types;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \Sensei_Pro_Student_Groups\Repositories\Group_Student_Repository;

/**
 * Class that handles the "Visible to Groups" visibility type.
 */
class Groups extends Type {
	/**
	 * Group student repository
	 *
	 * @var Group_Student_Repository;
	 */
	private $group_student_repository;

	/**
	 * Constructor
	 */
	public function __construct() {
		global $wpdb;
		$this->group_student_repository = new Group_Student_Repository( $wpdb );
	}

	/**
	 * Name
	 */
	public function name(): string {
		return 'GROUPS';
	}

	/**
	 * Label
	 */
	public function label(): string {
		return __( 'Specific Group', 'sensei-pro' );
	}

	/**
	 * Badge label
	 */
	public function badge_label(): string {
		return __( 'Selected groups', 'sensei-pro' );
	}

	/**
	 * Retrieves the description.
	 */
	public function description(): string {
		return __( 'Block visible to selected groups.', 'sensei-pro' );
	}

	/**
	 * Tells if the block is visible or not.
	 *
	 * @param array $visibility_settings The sensei visibility settings.
	 */
	public function is_visible( array $visibility_settings ): bool {
		$current_user_id = get_current_user_id();

		// Get a list of allowed group ids.
		$allowed_group_ids = array_map(
			function ( $group ) {
				// We convert the ids into strings to be able to
				// compare them against list of groups that the student
				// belongs to, which are also in strings.
				return (string) $group['value'];
			},
			$visibility_settings['groups'] ?? []
		);

		// Get a list of groups that the user belongs to.
		$user_group_ids = $this->group_student_repository->get_student_groups( $current_user_id );

		// Fing the groups that are both in allowed groups list
		// and the groups list that current user belongs to.
		$matching_group_ids = array_intersect( $allowed_group_ids, $user_group_ids );

		// If there is at least one match then user can see the block.
		return (bool) count( $matching_group_ids );
	}
}
