<?php


/**
 * ⚠️ These tests run against the production database and object cache on learn.w.org and profiles.w.org.
 * Make sure that any modifications are hardcoded to only affect test sites and test user accounts.
 *
 * Usage: wp eval-file e2e.php test_name
 */

namespace WordCamp\Participation_Notifier\Tests;
use WPOrg_Learn\Profiles as Learn_Profiles;
use Exception, WP_User, WP_Post;

// won't do anything if fatal errors
ini_set( 'display_errors', 'On' ); //phpcs:ignore

if ( 'staging' !== wp_get_environment_type() || 'cli' !== php_sapi_name() ) {
	die( 'Error: Wrong environment.' );
}

const TEST_USERNAME  = 'iandunn-test';
const TEST_COURSE_ID = 17240;

/** @var array $args */
main( $args[0] );

/**
 * @param Case $case
 *
 * @return void
 */
function main( $case ) {
	switch_to_blog( 7 ); // learn.w.org
	$user   = get_user_by( 'slug', TEST_USERNAME );
	$course = get_post( TEST_COURSE_ID );

	try {
		require_once WP_PLUGIN_DIR . '/wporg-learn/inc/profiles.php';
		call_user_func( __NAMESPACE__ . "\\test_$case", $user, $course );

	} catch ( Exception $exception ) {
		echo $exception->getMessage(); //phpcs:ignore

	} finally {
		restore_current_blog();
	}
}

/**
 *
 * @param WP_User $user
 * @param WP_Post $course
 *
 * @return void
 */
function test_add( WP_User $user, WP_Post $course ) {
	Learn_Profiles\add_course_completed_activity( 'complete', $user->ID, $course->ID );

	echo "\nThere should be a course confirmation activity on https://profiles.wordpress.org/$user->user_nicename/ \n"; //phpcs:ignore
}
