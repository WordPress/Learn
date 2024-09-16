<?php

namespace WPOrg_Learn\Redirects;

add_action( 'template_redirect', __NAMESPACE__ . '\wporg_learn_redirect_meetings' );
add_action( 'template_redirect', __NAMESPACE__ . '\wporg_learn_redirect_old_urls' );

add_filter( 'allowed_redirect_hosts', __NAMESPACE__ . '\wporg_learn_allowed_redirect_hosts' );

/**
 * Add allowed redirect hosts.
 *
 * @param array $hosts The array of allowed redirect hosts.
 * @return array The updated array of allowed redirect hosts.
 */
function wporg_learn_allowed_redirect_hosts( $hosts ) {
	return array_merge( $hosts, array( 'wordpress.tv', 'make.wordpress.org' ) );
};

/**
 * Redirect meeting posts to associated link
 *
 * @return void
 */
function wporg_learn_redirect_meetings() {
	global $post;

	if ( is_singular( array( 'meeting' ) ) ) {

		if ( ! empty( $post->ID ) ) {

			$redirect = wp_http_validate_url( get_post_meta( $post->ID, 'link', true ) );

			if ( $redirect && wp_redirect( $redirect ) ) {
				exit;
			}
		}
	}

}

/**
 * Redirect old pages to their new homes.
 *
 * @return void
 */
function wporg_learn_redirect_old_urls() {
	if ( ! is_404() ) {
		return;
	}

	// Redirection arrays use the format Source => Destination
	// Any characters after the source will be appended to the destination.

	$pages = array(
		'/handbook'                       => 'https://make.wordpress.org/training/handbook/',
		'/report-content-errors'          => '/report-content-feedback',
		'/social-learning'                => '/online-workshops',
		'/workshop/'                      => '/tutorial/',
		'/workshop-presenter-application' => '/tutorial-presenter-application',
		'/workshops'                      => '/tutorials',
	);

	$tutorials = array(
		'/tutorial/block-editor-01-basics/'      => 'https://wordpress.tv/2021/06/18/shusei-toda-naoko-takano-block-editor-01-basics/',
		'/tutorial/block-editor-02-text-blocks/' => 'https://wordpress.tv/2021/06/03/shusei-toda-block-editor-02-text-blocks/',
		'/tutorial/ja-login-password-reset/'     => 'https://wordpress.tv/2021/02/16/login-password-reset/',
	);

	$courses = array(
		'/course/simple-site-design-with-full-site-editing/'                                                   => '/learning-pathway/user/',
		'/course/part-2-personalized-site-design-with-full-site-editing-and-theme-blocks/'                     => '/learning-pathway/user/',
		'/course/part-3-advanced-site-design-with-full-site-editing-site-editor-templates-and-template-parts/' => '/learning-pathway/user/',
		'/course/developing-with-the-wordpress-rest-api/'                                                      => '/course/beginner-wordpress-developer/',
		'/course/converting-a-shortcode-to-a-block/'                                                           => '/course/beginner-wordpress-developer/',
		'/course/a-developers-guide-to-block-themes-part-1/'                                                   => '/course/intermediate-theme-developer/',
		'/course/a-developers-guide-to-block-themes-part-2/'                                                   => '/course/intermediate-theme-developer/',
	);

	$redirects = array_merge( $pages, $tutorials, $courses );

	// Use `REQUEST_URI` rather than `$wp->request`, to get the entire source URI including url parameters.
	$request = $_SERVER['REQUEST_URI'] ?? '';

	foreach ( $redirects as $source => $destination ) {
		if ( str_starts_with( $request, $source ) ) {
			$redirect = $destination;
			$code = 301;

			// Append any extra request parameters.
			if ( strlen( $request ) > strlen( $source ) ) {
				$redirect .= substr( $request, strlen( $source ) );
			}

			wp_safe_redirect( $redirect, $code, 'Learn WordPress' );
			exit;
		}
	}
}
