<?php

namespace WPOrg_Learn\Capabilities;

defined( 'WPINC' ) || die();

/**
 * Actions and filters.
 */
add_filter( 'user_has_cap', __NAMESPACE__ . '\set_post_type_caps' );
add_filter( 'user_has_cap', __NAMESPACE__ . '\set_caps_for_internal_notes' );
add_filter( 'map_meta_cap', __NAMESPACE__ . '\map_meta_caps', 20, 4 ); // Needs to fire after meta caps in wporg-internal-notes.
add_action( 'init', __NAMESPACE__ . '\add_or_update_lesson_plan_editor_role' );
add_action( 'init', __NAMESPACE__ . '\add_or_update_workshop_reviewer_role' );

/**
 * Assign custom post type caps to roles based on their equivalents for the 'post' post type.
 *
 * Example: If a user has the `edit_others_posts` cap (from Editor role), this will also give them
 * the equivalent `edit_others_lesson_plans` and `edit_others_workshops` caps.
 *
 * @param bool[] $user_caps A list of primitive caps (keys) and whether user has them (boolean values).
 *
 * @return array
 */
function set_post_type_caps( $user_caps ) {
	$capability_types = array(
		array( 'lesson_plan', 'lesson_plans' ),
		array( 'tutorial', 'tutorials' ),
	);

	foreach ( $capability_types as $capability_type ) {
		// Generate the caps for a capability type.
		$cap_args = array(
			'capability_type' => $capability_type,
			'capabilities'    => array(),
			'map_meta_cap'    => true,
		);
		$cap_map = (array) get_post_type_capabilities( (object) $cap_args );

		foreach ( $user_caps as $cap_slug => $granted ) {
			if ( $granted && isset( $cap_map[ $cap_slug ] ) ) {
				$user_caps[ $cap_map[ $cap_slug ] ] = true;
			}
		}
	}

	return $user_caps;
}

/**
 * Enable a cap for managing internal notes on workshop posts.
 *
 * The `promote_users` cap is shared by admin and workshop reviewer roles, which are the two roles
 * that should also have access to internal notes.
 *
 * @param bool[] $user_caps
 *
 * @return mixed
 */
function set_caps_for_internal_notes( $user_caps ) {
	if ( isset( $user_caps['promote_users'] ) && true === $user_caps['promote_users'] ) {
		$user_caps['manage_workshop_internal_notes'] = true;
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

			// Grant `edit_any_learn_content` when the user has `edit_posts` for any of our custom post types.
			foreach ( $learn_content_types as $post_type ) {
				$object = get_post_type_object( $post_type );
				if ( user_can( $user_id, $object->cap->edit_posts ) ) {
					$required_caps[] = $object->cap->edit_posts;
					break 2; // Breaks out of the foreach and the switch.
				}
			}

			$required_caps[] = 'do_not_allow';
			break;

		case 'read-internal-notes':
		case 'create-internal-note':
		case 'delete-internal-note':
			// Override the meta caps set up in the Internal Notes plugin, specifically for the workshop post type.
			$parent = ! empty( $args[0] ) ? get_post( $args[0] ) : false;
			if ( $parent && 'wporg_workshop' === get_post_type( $parent ) ) {
				$required_caps = array( 'manage_workshop_internal_notes' );
			}
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
 * The Lesson Plan Editor should have full editing caps for the Lesson Plan post type, but no other post types.
 * They can assign/unassign terms from the various taxonomies to lesson plans, but they can't add/edit/delete terms.
 *
 * @return bool[]
 */
function get_lesson_plan_editor_role_caps() {
	// Generate the caps for a capability type.
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

/**
 * Add the Workshop Reviewer role if it doesn't exist yet, or ensure it has the correct capabilities.
 *
 * Once a role has been added, and is stored in the database, it can't be changed using `add_role` because it
 * will return early.
 *
 * @return \WP_Role|null
 */
function add_or_update_workshop_reviewer_role() {
	$role_caps = get_workshop_reviewer_role_caps();
	$wr_role  = get_role( 'workshop_reviewer' );

	if ( is_null( $wr_role ) ) {
		$wr_role = add_role(
			'workshop_reviewer',
			__( 'Tutorial Reviewer', 'wporg-learn' ),
			$role_caps
		);
	} else {
		$caps_to_remove = array_diff(
			array_keys( $wr_role->capabilities, true, true ),
			array_keys( $role_caps, true, true )
		);

		foreach ( $caps_to_remove as $remove ) {
			$wr_role->remove_cap( $remove );
		}

		$caps_to_add = array_diff(
			array_keys( $role_caps, true, true ),
			array_keys( $wr_role->capabilities, true, true )
		);

		foreach ( $caps_to_add as $add ) {
			$wr_role->add_cap( $add );
		}
	}

	return $wr_role;
}

/**
 * Generate a list of capabilities for the Workshop Reviewer role.
 *
 * The Workshop Reviewer should have all the same caps as the Editor role, with the addition of `promote_users`
 * (normally reserved for the Admin role), so that they can add workshop presenters as new users on the site.
 *
 * This also gives them the cap to manage internal notes on workshop posts. (See `set_caps_for_internal_notes` above.)
 *
 * @return bool[]
 */
function get_workshop_reviewer_role_caps() {
	$role_caps = get_role( 'editor' )->capabilities;

	$role_caps['promote_users'] = true;

	return $role_caps;
}
