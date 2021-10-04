<?php

namespace WPOrg_Learn\Capabilities;

defined( 'WPINC' ) || die();

/**
 * Actions and filters.
 */
add_filter( 'user_has_cap', __NAMESPACE__ . '\set_post_type_caps' );

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
