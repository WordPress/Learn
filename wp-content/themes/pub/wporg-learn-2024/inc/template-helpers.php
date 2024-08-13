<?php

namespace WordPressdotorg\Theme\Learn_2024\Template_Helpers;

/**
 * Get the URL of the "My Courses" page.
 *
 * @return string The URL of the "My Courses" page.
 */
function get_my_courses_page_url() {
	if ( function_exists( 'Sensei' ) ) {
		$page_id = Sensei()->settings->get_my_courses_page_id();

		return $page_id ? get_permalink( $page_id ) : '';
	}

	return '';
}
