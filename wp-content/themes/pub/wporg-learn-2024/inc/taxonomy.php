<?php

namespace WordPressdotorg\Theme\Learn_2024\Taxonomy;

defined( 'WPINC' ) || die();

/**
 * Actions and filters.
 */
add_action( 'init', __NAMESPACE__ . '\register' );
add_filter( 'sensei_course_custom_navigation_tabs', __NAMESPACE__ . '\add_sensei_course_custom_navigation_tabs' );

/**
 * Register all the taxonomies.
 */
function register() {
	register_audience();
	register_experience_level();
	register_learning_pathway();
}

/**
 * Register the Audience taxonomy.
 */
function register_audience() {
	$labels = array(
		'name'                       => _x( 'Audiences', 'Taxonomy General Name', 'wporg-learn' ),
		'singular_name'              => _x( 'Audience', 'Taxonomy Singular Name', 'wporg-learn' ),
		'menu_name'                  => __( 'Audience', 'wporg-learn' ),
		'all_items'                  => __( 'All audiences', 'wporg-learn' ),
		'parent_item'                => __( 'Parent audience', 'wporg-learn' ),
		'parent_item_colon'          => __( 'Parent audience:', 'wporg-learn' ),
		'new_item_name'              => __( 'New audience Name', 'wporg-learn' ),
		'add_new_item'               => __( 'Add New audience', 'wporg-learn' ),
		'edit_item'                  => __( 'Edit audience', 'wporg-learn' ),
		'update_item'                => __( 'Update audience', 'wporg-learn' ),
		'view_item'                  => __( 'View audience', 'wporg-learn' ),
		'separate_items_with_commas' => __( 'Separate audiences with commas', 'wporg-learn' ),
		'add_or_remove_items'        => __( 'Add or remove audiences', 'wporg-learn' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'wporg-learn' ),
		'popular_items'              => __( 'Popular audiences', 'wporg-learn' ),
		'search_items'               => __( 'Search audiences', 'wporg-learn' ),
		'not_found'                  => __( 'No audience found', 'wporg-learn' ),
		'no_terms'                   => __( 'No audiences', 'wporg-learn' ),
		'items_list'                 => __( 'Audiences list', 'wporg-learn' ),
		'items_list_navigation'      => __( 'Audiences list navigation', 'wporg-learn' ),
	);

	$args = array(
		'labels'            => $labels,
		'hierarchical'      => false,
		'public'            => true,
		'query_var'         => 'wporg_audience', // Prevent collisions with query params in the archive
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_nav_menus' => true,
		'show_tagcloud'     => false,
		'show_in_rest'      => true,
		'capabilities'      => array(
			'assign_terms' => 'edit_any_learn_content', // See WordPressdotorg\Theme\Learn_2024\Capabilities\map_meta_caps
		),
	);

	register_taxonomy( 'audience', array( 'lesson', 'course' ), $args );
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
 * Register the Learning Pathway taxonomy.
 */
function register_learning_pathway() {
	$labels = array(
		'name'                       => _x( 'Learning Pathways', 'Taxonomy General Name', 'wporg-learn' ),
		'singular_name'              => _x( 'Learning Pathway', 'Taxonomy Singular Name', 'wporg-learn' ),
		'menu_name'                  => __( 'Learning pathway', 'wporg-learn' ),
		'all_items'                  => __( 'All learning pathways', 'wporg-learn' ),
		'parent_item'                => __( 'Parent learning pathway', 'wporg-learn' ),
		'parent_item_colon'          => __( 'Parent learning pathway:', 'wporg-learn' ),
		'new_item_name'              => __( 'New learning pathway Name', 'wporg-learn' ),
		'add_new_item'               => __( 'Add New learning pathway', 'wporg-learn' ),
		'edit_item'                  => __( 'Edit learning pathway', 'wporg-learn' ),
		'update_item'                => __( 'Update learning pathway', 'wporg-learn' ),
		'view_item'                  => __( 'View learning pathway', 'wporg-learn' ),
		'separate_items_with_commas' => __( 'Separate learning pathways with commas', 'wporg-learn' ),
		'add_or_remove_items'        => __( 'Add or remove learning pathways', 'wporg-learn' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'wporg-learn' ),
		'popular_items'              => __( 'Popular learning pathways', 'wporg-learn' ),
		'search_items'               => __( 'Search learning pathways', 'wporg-learn' ),
		'not_found'                  => __( 'No learning pathway found', 'wporg-learn' ),
		'no_terms'                   => __( 'No learning pathways', 'wporg-learn' ),
		'items_list'                 => __( 'Learning pathways list', 'wporg-learn' ),
		'items_list_navigation'      => __( 'Learning pathways list navigation', 'wporg-learn' ),
	);

	$args = array(
		'labels'            => $labels,
		'hierarchical'      => false,
		'public'            => true,
		'query_var'         => 'wporg_learning_pathway', // Prevent collisions with query params in the archive
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_nav_menus' => true,
		'show_tagcloud'     => false,
		'show_in_rest'      => true,
		'capabilities'      => array(
			'assign_terms' => 'edit_others_posts',
		),
	);

	register_taxonomy( 'learning-pathways', array( 'course' ), $args );
}

/**
 * Add custom navigation tabs for Sensei courses.
 *
 * @param array $tabs The existing navigation tabs.
 * @return array The modified navigation tabs.
 */
function add_sensei_course_custom_navigation_tabs( $tabs ) {
	$tabs['learning-pathways'] = array(
		'label'     => __( 'Learning Pathways', 'wporg-learn' ),
		'url'       => admin_url( 'edit-tags.php?taxonomy=learning-pathways&post_type=course' ),
		'screen_id' => 'edit-learning-pathways',
	);

	return $tabs;
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
