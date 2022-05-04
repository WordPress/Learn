<?php
/*
 * Add activity, etc to profiles.wordpress.org when users do noteworthy things.
 */

namespace WPOrg_Learn\Profiles;
use WordPressdotorg\Profiles as Profiles_API;

defined( 'WPINC' ) || die();

/*
 * Requests will always fail when in local environments, unless the dev is proxied. Proxied devs could test
 * locally if they're careful (especially with user IDs), but it's better to test on w.org sandboxes with
 * test accounts. That prevents real profiles from having test data accidentally added to them.
 */
if ( 'local' === wp_get_environment_type() ) {
	return;
}

add_action( 'sensei_course_status_updated', __NAMESPACE__ . '\add_course_completed_activity', 9, 3 ); // Before `redirect_to_course_completed_page()`.

/**
 * Add an activity to a user's profile when they complete a course.
 */
function add_course_completed_activity( string $status, int $user_id, int $course_id ) : void {
	if ( 'complete' !== $status ) {
		return;
	}

	$request_body = array(
		'action'       => 'wporg_handle_activity',
		'source'       => 'learn',
		'activity'     => 'course_complete',
		'user'         => $user_id,
		'url'          => get_permalink( $course_id ),
		'course_id'    => $course_id,
		'course_title' => get_the_title( $course_id ),
	);

	Profiles_API\api( $request_body );
}
