<?php
/**
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
add_action( 'transition_post_status', __NAMESPACE__ . '\maybe_notify_new_published_post', 10, 3 );

/**
 * Only send notification for post getting published.
 *
 * @param string  $new_status The new status for the post.
 * @param string  $old_status The old status for the post.
 * @param WP_Post $post The post.
 */
function maybe_notify_new_published_post( $new_status, $old_status, $post ) {
	if ( 'publish' != $new_status ) {
		return;
	}

	/**
	 * Gutenberg sends two requests when we hit the Publish/Update button.
	 * https://github.com/WordPress/wordpress.org/pull/84#discussion_r919290748
	 *
	 * How it affects this action:
	 * For example, if the post status is changed from 'draft' to 'published'.
	 * For the first request,
	 * $old_status would be different from $new_status.
	 * For the second request,
	 * $old_status would be the same as $new_status, both their values would be 'publish'.
	 */
	if ( 'wporg_workshop' === $post->post_type ) {
		notify_workshop_presenter( $post );
	}
}

/**
 * Sends activity notification for workshop presenter.
 *
 * @param WP_Post $post Post object.
 */
function notify_workshop_presenter( $post ) {
	if ( defined( 'WP_IMPORTING' ) && WP_IMPORTING ) {
		return;
	}

	$presenter_wporg_username = filter_input( INPUT_POST, 'presenter-wporg-username' );

	if ( empty( $presenter_wporg_username ) ) {
		return;
	}

	$unique_presenter_wporg_username = array_unique( array_map( 'trim', explode( ',', $presenter_wporg_username ) ) );
	$permalink                       = get_permalink( $post );
	$title                           = wp_kses_data( $post->post_title );
	$content                         = wp_trim_words(
		strip_shortcodes( has_excerpt( $post ) ? $post->post_excerpt : $post->post_content ),
		55
	);

	foreach ( $unique_presenter_wporg_username as $username ) {
		$user_id = get_user_by( 'slug', strtolower( $username ) )->ID;

		if ( ! $user_id ) {
			continue;
		}

		$request_body = array(
			'action'       => 'wporg_handle_activity',
			'component'    => 'learn',
			'type'         => 'workshop_presenter_assign',
			'user_id'      => $user_id,
			'primary_link' => $permalink,
			'item_id'      => $post->ID,
			'content'      => $content,
			'message'      => sprintf(
				'Assigned as a presenter on the Learn WordPress tutorial, <i><a href="%s">%s</a></i>',
				$permalink,
				$title,
			),
		);

		Profiles_API\api( $request_body );
	}
}


/**
 * Add an activity to a user's profile when they complete a course.
 */
function add_course_completed_activity( string $status, int $user_id, int $course_id ) : void {
	if ( 'complete' !== $status ) {
		return;
	}

	$course_url = get_permalink( $course_id );

	$request_body = array(
		'action'       => 'wporg_handle_activity',
		'component'    => 'learn',
		'type'         => 'learn_course_complete',
		'user_id'      => $user_id,
		'primary_link' => $course_url,
		'item_id'      => $course_id,

		'message' => sprintf(
			'Completed the course <em><a href="%s">%s</a></em> on learn.wordpress.org',
			$course_url,
			wp_kses_data( get_the_title( $course_id ) )
		),
	);

	Profiles_API\api( $request_body );
}
