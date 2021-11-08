<?php
/*
 * Add activity, etc to profiles.wordpress.org when users do noteworthy things.
 */

namespace WPOrg_Learn\Profiles;
use WP_Error;

defined( 'WPINC' ) || die();

/*
 * Requests will always fail when in local environments, unless the dev is proxied. Proxied devs could test
 * locally if they're careful (especially with user IDs), but it's better to test on w.org sandboxes with
 * test accounts. That prevents real profiles from having test data accidentally added to them.
 */
if ( 'local' === wp_get_environment_type() ) {
	return;
}

const PROFILES_HANDLER_URL = 'https://profiles.wordpress.org/wp-admin/admin-ajax.php';

add_action( 'sensei_course_status_updated', __NAMESPACE__ . '\add_course_completed_activity', 9, 3 ); // Before `redirect_to_course_completed_page()`.
add_filter( 'pre_http_request', __NAMESPACE__ . '\redirect_sandbox_requests', 10, 3 );

/**
 * Add an activity to a user's profile when they complete a course.
 *
 * @param string $status
 * @param int    $user_id
 * @param int    $course_id
 */
function add_course_completed_activity( $status, $user_id, $course_id ) {
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

	request_profile_update( PROFILES_HANDLER_URL, $request_body );
}

/**
 * Send Profiles request and handle errors.
 *
 * @param string $request_url
 * @param array  $body The value intended to be passed to `wp_remote_post()` as `$args['body']`.
 *
 * @return array|WP_Error The response from `wp_remote_post()`.
 */
function request_profile_update( $request_url, $body ) {
	$response = wp_remote_post( $request_url, array( 'body' => $body ) );

	if ( is_wp_error( $response ) ) {
		$error = $response->get_error_message();

	} elseif ( 200 != wp_remote_retrieve_response_code( $response ) || 1 != (int) wp_remote_retrieve_body( $response ) ) {
		$error = sprintf(
			'Error %s %s',
			$response['response']['code'],
			$response['body']
		);
	}

	if ( isset( $error ) ) {
		trigger_error( wp_kses_post( $error ), E_USER_WARNING );
	}

	return $response;
}

/**
 * Send test requests to the current sandbox, instead of production.
 *
 * When making changes to code in this file, the request should go to the dev's sandbox so it can be debugged, and
 * so that changes to the Profiles code can be tested.
 *
 * @param false|array|WP_Error $preempt
 * @param array                $request_args
 * @param string               $request_url
 *
 * @return array|mixed|WP_Error
 */
function redirect_sandbox_requests( $preempt, $request_args, $request_url ) {
	if ( ! defined( 'WPORG_SANDBOXED' ) || ! WPORG_SANDBOXED ) {
		return $preempt;
	}

	if ( PROFILES_HANDLER_URL !== $request_url ) {
		return $preempt;
	}

	$path                            = wp_parse_url( $request_url, PHP_URL_PATH );
	$request_args['headers']['Host'] = 'profiles.wordpress.org';

	/*
	 * It's expected that the sandbox certificate won't be valid. This is safe because we're only connecting
	 * to `127.0.0.1`.
	 */
	$request_args['sslverify'] = false;

	return wp_remote_post( "https://127.0.0.1$path", $request_args );
}
