<?php
/**
 * File containing the class \Sensei_Pro_Student_Groups\Rest_API\Responses\Create_Group_Student_Response.
 *
 * @package student-groups
 * @since   1.4.0
 */

namespace Sensei_Pro_Student_Groups\Rest_Api\Responses;

/**
 * Class Create_Group_Student_Response.
 *
 * @since 1.4.0
 */
class Student_Response {
	/**
	 * Prepares student representation.
	 *
	 * @param \WP_User $user Student.
	 *
	 * @return array
	 */
	public static function from_user( \WP_User $user ): array {
		return [
			'id'   => $user->ID,
			'name' => $user->display_name,
		];
	}
}
