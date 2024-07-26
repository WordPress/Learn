<?php

/**
 * Enqueue scripts and styles.
 */
function enqueue_assets() {
	if ( ! is_singular( 'course' ) ) {
		return;
	}

	$script_asset_path = get_stylesheet_directory() . '/build/course-outline/index.asset.php';
	if ( ! file_exists( $script_asset_path ) ) {
		wp_die( 'You need to run `yarn start` or `yarn build` to build the required assets.' );
	}

	$script_asset = require( $script_asset_path );
	wp_enqueue_script(
		'wporg-learn-2024-course-outline',
		get_stylesheet_directory_uri() . '/build/course-outline/index.js',
		$script_asset['dependencies'],
		$script_asset['version'],
		true
	);

	wp_localize_script(
		'wporg-learn-2024-course-outline',
		'wporgCourseOutlineL10n',
		array(
			'inProgress' => __( 'In progress', 'wporg-learn' ),
			'locked' => __( 'Locked', 'wporg-learn' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'enqueue_assets' );
