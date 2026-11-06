<?php
/**
 * Add activity, etc to profiles.wordpress.org when users do noteworthy things.
 */

namespace WPOrg_Learn\Profiles;

use WordPressdotorg\Profiles as Profiles_API;
use const WPOrg_Learn\Taxonomy\PROFILE_BADGE_TAXONOMY;

defined( 'WPINC' ) || die();

/*
 * Badges on profiles.wordpress.org that a course can give. A course gives a badge when it carries the Profile
 * Badge term whose slug is the badge slug; see `register_profile_badge()` in `inc/taxonomy.php`.
 */
const BADGE_GRADUATE = 'credits-graduate'; // Given when the course is completed.
const BADGE_ENROLLED = 'credits-enrolled'; // Given when the Onboarding module is completed, taken away on graduation.

// The Sensei module whose completion gives the enrolled badge, matched by name.
const ENROLLED_MODULE_NAME = 'Onboarding';

/*
 * Requests will always fail when in local environments, unless the dev is proxied. Proxied devs could test
 * locally if they're careful (especially with user IDs), but it's better to test on w.org sandboxes with
 * test accounts. That prevents real profiles from having test data accidentally added to them.
 */
if ( 'local' === wp_get_environment_type() ) {
	return;
}

add_action( 'sensei_course_status_updated', __NAMESPACE__ . '\add_course_completed_activity', 9, 5 ); // Before `redirect_to_course_completed_page()`.
add_action( 'sensei_user_lesson_end', __NAMESPACE__ . '\maybe_assign_enrolled_badge', 10, 2 );
add_action( 'transition_post_status', __NAMESPACE__ . '\maybe_notify_new_published_post', 10, 3 );

/**
 * Only send notification for post getting published.
 *
 * @param string  $new_status The new status for the post.
 * @param string  $old_status The old status for the post.
 * @param WP_Post $post The post.
 */
function maybe_notify_new_published_post( $new_status, $old_status, $post ) {
	if ( 'publish' != $new_status || 'publish' === $old_status ) {
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
	$title                           = esc_html( $post->post_title );
	$content                         = wp_trim_words(
		strip_shortcodes( has_excerpt( $post ) ? $post->post_excerpt : $post->post_content ),
		55
	);

	foreach ( $unique_presenter_wporg_username as $username ) {
		$user_id = get_user_by( 'slug', strtolower( $username ) )->ID;

		if ( ! $user_id ) {
			continue;
		}

		Profiles_API\add_activity(
			'learn',
			'workshop_presenter_assign',
			$user_id,
			array(
				'primary_link' => $permalink,
				'item_id'      => $post->ID,
				'content'      => $content,
				'message'      => sprintf(
					'Assigned as a presenter on the Learn WordPress tutorial, <i><a href="%s">%s</a></i>',
					esc_url( $permalink ),
					$title,
				),
			)
		);
	}
}


/**
 * Add an activity to a user's profile when they complete a course, and give the badges the course carries.
 */
function add_course_completed_activity( string $status, int $user_id, int $course_id, int $comment_id, ?string $previous_status ): void {
	if ( 'complete' !== $status || 'complete' === $previous_status ) {
		return;
	}

	$course_url = get_permalink( $course_id );
	Profiles_API\add_activity(
		'learn',
		'learn_course_complete',
		$user_id,
		array(
			'primary_link' => $course_url,
			'item_id'      => $course_id,
			'message'      => sprintf(
				'Completed the course <em><a href="%s">%s</a></em> on learn.wordpress.org',
				esc_url( $course_url ),
				esc_html( get_the_title( $course_id ) )
			),
		)
	);

	$badges = get_course_badges( $course_id );

	/*
	 * Courses that gave the graduate badge before the Profile Badge taxonomy existed. Remove this list once
	 * each of them carries the `credits-graduate` term.
	 */
	switch ( get_post( $course_id )->post_name ) {
		case '50-hours-wordpress-credits':
		case 'wordpress-credits':
		case 'wordpress-credits-developer-track':
		case 'wordpress-credits-designer-track':
		case 'wordpress-credits-translation-track':
		case 'wordpress-credits-foundations':
		case 'wordpress-credits-intro-to-contribution':
		case 'wordpress-credits-self-onboarding-pilot':
			$badges[] = BADGE_GRADUATE;
			break;
	}

	if ( ! in_array( BADGE_GRADUATE, $badges, true ) ) {
		return;
	}

	assign_profile_badge( BADGE_GRADUATE, $user_id );

	// Graduating supersedes being enrolled.
	if ( in_array( BADGE_ENROLLED, $badges, true ) ) {
		remove_profile_badge( BADGE_ENROLLED, $user_id );
	}
}

/**
 * Give the enrolled badge when a learner completes the Onboarding module of a course that carries the term.
 *
 * The badge marks a learner who has reached the project phase of a course, not one who merely enrolled: Sensei
 * starts a lesson as soon as an enrolled learner opens it, so a started lesson proves little, while a completed
 * Onboarding module proves the learner did the work. `sensei_user_lesson_end` fires when a lesson is completed,
 * a quiz is passed or a quiz submission is graded, after the lesson progress is saved.
 *
 * @param int $user_id   The learner.
 * @param int $lesson_id The lesson just completed.
 */
function maybe_assign_enrolled_badge( $user_id, $lesson_id ): void {
	$user_id   = (int) $user_id;
	$lesson_id = (int) $lesson_id;
	$course_id = (int) Sensei()->lesson->get_course_id( $lesson_id );

	if ( ! $user_id || ! $course_id || ! in_array( BADGE_ENROLLED, get_course_badges( $course_id ), true ) ) {
		return;
	}

	$module = Sensei()->modules->get_lesson_module( $lesson_id );

	if ( ! $module || 0 !== strcasecmp( ENROLLED_MODULE_NAME, trim( $module->name ) ) ) {
		return;
	}

	if ( Sensei()->modules->calculate_user_module_progress( $user_id, $module->term_id, $course_id ) < 100 ) {
		return;
	}

	// A graduate who retakes the course already has the badge this one leads to.
	if ( Profiles_API\has_badge( BADGE_GRADUATE, $user_id ) ) {
		return;
	}

	assign_profile_badge( BADGE_ENROLLED, $user_id );
}

/**
 * Get the badges a course gives, from its Profile Badge terms.
 *
 * A term's slug is the badge slug on profiles.wordpress.org. Only the badges this file knows how to give are
 * returned; any other term is logged and ignored.
 *
 * @return string[] Badge slugs.
 */
function get_course_badges( int $course_id ): array {
	$terms = get_the_terms( $course_id, PROFILE_BADGE_TAXONOMY );

	if ( ! $terms || is_wp_error( $terms ) ) {
		return array();
	}

	$known  = array( BADGE_GRADUATE, BADGE_ENROLLED );
	$badges = array();

	foreach ( $terms as $term ) {
		if ( in_array( $term->slug, $known, true ) ) {
			$badges[] = $term->slug;
			continue;
		}

		trigger_error(
			esc_html( sprintf( 'Course %d carries the profile badge term "%s", which this site does not know how to give.', $course_id, $term->slug ) ),
			E_USER_WARNING
		);
	}

	return $badges;
}

/**
 * Give a badge to a user, logging a failure instead of stopping.
 *
 * On production the request is queued and sent later; on a sandbox it is sent at once. A badge that does not
 * exist on profiles.wordpress.org yet fails there, not here.
 */
function assign_profile_badge( string $badge, int $user_id ): void {
	if ( ! Profiles_API\assign_badge( $badge, $user_id ) ) {
		trigger_error(
			esc_html( sprintf( 'Could not give the "%s" profile badge to user %d.', $badge, $user_id ) ),
			E_USER_WARNING
		);
	}
}

/**
 * Take a badge away from a user, logging a failure instead of stopping.
 *
 * Nothing is sent when the user does not have the badge, so a badge that does not exist yet is skipped.
 */
function remove_profile_badge( string $badge, int $user_id ): void {
	if ( ! Profiles_API\remove_badge( $badge, $user_id ) ) {
		trigger_error(
			esc_html( sprintf( 'Could not take the "%s" profile badge away from user %d.', $badge, $user_id ) ),
			E_USER_WARNING
		);
	}
}
