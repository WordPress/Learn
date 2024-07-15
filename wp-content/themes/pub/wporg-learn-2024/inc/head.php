<?php
/**
 * HTML head markup and customizations.
 */

namespace WordPressdotorg\Theme\Learn_2024\Head;

add_action( 'init', __NAMESPACE__ . '\init' );

/**
 * Handles adding/removing hooks as needed.
 */
function init() {
	add_filter( 'document_title_parts', __NAMESPACE__ . '\document_title' );
	add_filter( 'document_title_separator', __NAMESPACE__ . '\document_title_separator' );
}

/**
 * Filters document title to add context based on what is being viewed.
 *
 * @param array $parts The document title parts.
 * @return array The document title parts.
 */
function document_title( $parts ) {
	global $wp_query;

	$parts['site']  = __( 'Learn.WordPress.org', 'wporg-learn' );
	$post_type      = get_query_var( 'post_type' );
	$sep            = '–';

	if ( is_singular() ) {
		// Add post type to title if it's a parsed item.
		if ( get_post_type_object( $post_type ) ) {
			$parts['title'] .= " $sep " . get_post_type_object( $post_type )->labels->singular_name;
		}
	}

	// If results are paged and the max number of pages is known.
	if ( is_paged() && $wp_query->max_num_pages ) {
		$parts['page'] = sprintf(
			// translators: 1: current page number, 2: total number of pages
			__( 'Page %1$s of %2$s', 'wporg-learn' ),
			get_query_var( 'paged' ),
			$wp_query->max_num_pages
		);
	}

	return $parts;
}

/**
 * Customizes the document title separator.
 *
 * @param string $separator Current document title separator.
 * @return string
 */
function document_title_separator( $separator ) {
	return '|';
}
