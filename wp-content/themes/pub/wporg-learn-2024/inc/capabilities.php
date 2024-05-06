<?php

namespace WordPressdotorg\Theme\Learn_2024\Capabilities;

defined( 'WPINC' ) || die();

/**
 * Actions and filters.
 */
add_filter( 'map_meta_cap', __NAMESPACE__ . '\map_meta_caps', 20, 4 ); // Needs to fire after meta caps in wporg-internal-notes.

/**
 * Map primitive caps to our custom caps.
 *
 * @param array  $required_caps
 * @param string $current_cap
 * @param int    $user_id
 * @param mixed  $args
 *
 * @return mixed
 */
function map_meta_caps( $required_caps, $current_cap, $user_id, $args ) {
	switch ( $current_cap ) {
		case 'edit_any_learn_content':
			$required_caps       = array();
			$learn_content_types = array( 'course', 'lesson', 'meeting' );

			// Grant `edit_any_learn_content` when the user has `edit_posts` for any of the learn post types.
			foreach ( $learn_content_types as $post_type ) {
				$object = get_post_type_object( $post_type );
				if ( user_can( $user_id, $object->cap->edit_posts ) ) {
					$required_caps[] = $object->cap->edit_posts;
					break 2; // Breaks out of the foreach and the switch.
				}
			}

			$required_caps[] = 'do_not_allow';
			break;
	}

	return $required_caps;
}
