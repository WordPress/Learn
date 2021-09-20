<?php
/**
 * The sidebar containing the courses widget area.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package WordPressdotorg\Theme
 */

namespace WordPressdotorg\Theme;

if ( is_active_sidebar( 'wporg-learn-courses' ) ) :
	dynamic_sidebar( 'wporg-learn-courses' );
endif;
