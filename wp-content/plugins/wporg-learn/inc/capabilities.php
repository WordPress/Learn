<?php

namespace WPOrg_Learn\Capabilities;

defined( 'WPINC' ) || die();

/**
 * Actions and filters.
 */
add_filter( 'user_has_cap', __NAMESPACE__ . '\set_post_type_caps' );
add_filter( 'map_meta_cap', __NAMESPACE__ . '\map_meta_caps', 10, 4 );
add_action( 'init', __NAMESPACE__ . '\add_or_update_lesson_plan_editor_role' );

/**
 *
 *
 * @param array $user_caps A list of primitive caps (keys) and whether user has them (boolean values).
 *
 * @return array
 */
function set_post_type_caps( $user_caps ) {
	$capability_types = array(
		array( 'lesson_plan', 'lesson_plans' ),
		array( 'workshop', 'workshops' ),
	);

	foreach ( $capability_types as $capability_type ) {
		// Set corresponding caps for all roles.
		$cap_args = array(
			'capability_type' => $capability_type,
			'capabilities'    => array(),
			'map_meta_cap'    => true,
		);
		$cap_map = (array) get_post_type_capabilities( (object) $cap_args );

		foreach ( $user_caps as $cap => $bool ) {
			if ( $bool && isset( $cap_map[ $cap ] ) ) {
				$user_caps[ $cap_map[ $cap ] ] = true;
			}
		}
	}

	return $user_caps;
}

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
			$learn_content_types = array( 'lesson-plan', 'wporg_workshop', 'course', 'lesson' );

			foreach ( $learn_content_types as $post_type ) {
				$object = get_post_type_object( $post_type );
				if ( user_can( $user_id, $object->cap->edit_posts ) ) {
					$required_caps[] = $object->cap->edit_posts;
					break 2;
				}
			}

			$required_caps[] = 'edit_posts';
			break;
	}

	return $required_caps;
}

/**
 * Add the Lesson Plan Editor role if it doesn't exist yet, or ensure it has the correct capabilities.
 *
 * Once a role has been added, and is stored in the database, it can't be changed using `add_role` because it
 * will return early.
 *
 * @return \WP_Role|null
 */
function add_or_update_lesson_plan_editor_role() {
	$role_caps = get_lesson_plan_editor_role_caps();
	$lpe_role  = get_role( 'lesson_plan_editor' );

	if ( is_null( $lpe_role ) ) {
		$lpe_role = add_role(
			'lesson_plan_editor',
			__( 'Lesson Plan Editor', 'wporg-learn' ),
			$role_caps
		);
	} else {
		$caps_to_remove = array_diff(
			array_keys( $lpe_role->capabilities, true, true ),
			array_keys( $role_caps, true, true )
		);

		foreach ( $caps_to_remove as $remove ) {
			$lpe_role->remove_cap( $remove );
		}

		$caps_to_add = array_diff(
			array_keys( $role_caps, true, true ),
			array_keys( $lpe_role->capabilities, true, true )
		);

		foreach ( $caps_to_add as $add ) {
			$lpe_role->add_cap( $add );
		}
	}

	return $lpe_role;
}

/**
 * Generate a list of capabilities for the Lesson Plan Editor role.
 *
 * @return array
 */
function get_lesson_plan_editor_role_caps() {
	$cap_args = array(
		'capability_type' => array( 'lesson_plan', 'lesson_plans' ),
		'capabilities'    => array(),
		'map_meta_cap'    => true,
	);
	$cap_map = (array) get_post_type_capabilities( (object) $cap_args );

	$editor_caps = get_role( 'editor' )->capabilities;
	$role_caps   = array();

	// Same caps as the editor, but only for lesson plans, not posts.
	foreach ( $cap_map as $primative_cap => $mapped_cap ) {
		if ( isset( $editor_caps[ $primative_cap ] ) && $editor_caps[ $primative_cap ] ) {
			$role_caps[ $mapped_cap ] = true;
		}
	}

	return $role_caps;
}
