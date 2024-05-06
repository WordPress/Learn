<?php

namespace WordPressdotorg\Theme\Learn_2024\Taxonomy;

defined( 'WPINC' ) || die();

/**
 * Actions and filters.
 */
add_action( 'init', __NAMESPACE__ . '\register' );

/**
 * Register all the taxonomies.
 */
function register() {
	register_experience_level();
}

/**
 * Register the Experience Level taxonomy.
 */
function register_experience_level() {
	$labels = array(
		'name'                       => _x( 'Experience Levels', 'Taxonomy General Name', 'wporg-learn' ),
		'singular_name'              => _x( 'Experience Level', 'Taxonomy Singular Name', 'wporg-learn' ),
		'menu_name'                  => __( 'Experience level', 'wporg-learn' ),
		'all_items'                  => __( 'All experience levels', 'wporg-learn' ),
		'parent_item'                => __( 'Parent experience level', 'wporg-learn' ),
		'parent_item_colon'          => __( 'Parent experience level:', 'wporg-learn' ),
		'new_item_name'              => __( 'New experience level Name', 'wporg-learn' ),
		'add_new_item'               => __( 'Add New experience level', 'wporg-learn' ),
		'edit_item'                  => __( 'Edit experience level', 'wporg-learn' ),
		'update_item'                => __( 'Update experience level', 'wporg-learn' ),
		'view_item'                  => __( 'View experience level', 'wporg-learn' ),
		'separate_items_with_commas' => __( 'Separate experience levels with commas', 'wporg-learn' ),
		'add_or_remove_items'        => __( 'Add or remove experience levels', 'wporg-learn' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'wporg-learn' ),
		'popular_items'              => __( 'Popular experience levels', 'wporg-learn' ),
		'search_items'               => __( 'Search experience levels', 'wporg-learn' ),
		'not_found'                  => __( 'No experience level found', 'wporg-learn' ),
		'no_terms'                   => __( 'No experience levels', 'wporg-learn' ),
		'items_list'                 => __( 'Experience levels list', 'wporg-learn' ),
		'items_list_navigation'      => __( 'Experience levels list navigation', 'wporg-learn' ),
	);

	$args = array(
		'labels'            => $labels,
		'hierarchical'      => false,
		'public'            => true,
		'query_var'         => 'wporg_experience_level', // Prevent collisions with query params in the archive
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_nav_menus' => true,
		'show_tagcloud'     => false,
		'show_in_rest'      => true,
		'capabilities'      => array(
			'assign_terms' => 'edit_any_learn_content', // See WordPressdotorg\Theme\Learn_2024\Capabilities\map_meta_caps
		),
	);

	register_taxonomy( 'level', array( 'lesson', 'course' ), $args );
}

/**
 * Get available taxonomy terms for a post type.
 *
 * @param string $taxonomy The taxonomy.
 * @param string $post_type The post type.
 * @param string $post_status The post status.
 * @return array The available taxonomy terms.
 */
function get_available_taxonomy_terms( $taxonomy, $post_type, $post_status = null ) {
	$posts = get_posts( array(
		'post_status'    => $post_status ?? 'any',
		'post_type'      => $post_type,
		'posts_per_page' => -1,
	) );

	if ( empty( $posts ) ) {
		return array();
	}

	$term_ids = array();
	foreach ( $posts as $post ) {
		$post_terms = wp_get_post_terms( $post->ID, $taxonomy, array( 'fields' => 'ids' ) );

		if ( ! is_wp_error( $post_terms ) ) {
			$term_ids = array_merge( $term_ids, $post_terms );
		}
	}

	if ( empty( $term_ids ) ) {
		return array();
	}

	$term_ids = array_unique( $term_ids );

	$terms = get_terms( array(
		'taxonomy'   => $taxonomy,
		'include'    => $term_ids,
		'hide_empty' => false,
	) );

	$levels = array();
	foreach ( $terms as $term ) {
		$levels[ $term->slug ] = $term->name;
	}

	return $levels;
}
