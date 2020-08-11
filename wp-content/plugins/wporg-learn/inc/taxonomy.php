<?php

namespace WPOrg_Learn\Taxonomy;

defined( 'WPINC' ) || die();

/**
 * Register all the taxonomies.
 */
function register() {
	register_lesson_taxonomies();
}

/**
 * Register taxonomies for the lesson plan post type.
 */
function register_lesson_taxonomies() {
	$labels = array(
		'name'                       => _x( 'Categories', 'Taxonomy General Name', 'wporg_learn' ),
		'singular_name'              => _x( 'Category', 'Taxonomy Singular Name', 'wporg_learn' ),
		'menu_name'                  => __( 'Categories', 'wporg_learn' ),
		'all_items'                  => __( 'All Categories', 'wporg_learn' ),
		'new_item_name'              => __( 'New Category', 'wporg_learn' ),
		'add_new_item'               => __( 'Add New Category', 'wporg_learn' ),
		'edit_item'                  => __( 'Edit Category', 'wporg_learn' ),
		'update_item'                => __( 'Update Category', 'wporg_learn' ),
		'view_item'                  => __( 'View Category', 'wporg_learn' ),
		'separate_items_with_commas' => __( 'Separate categories with commas', 'wporg_learn' ),
		'add_or_remove_items'        => __( 'Add or remove categories', 'wporg_learn' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'wporg_learn' ),
		'popular_items'              => __( 'Popular categories', 'wporg_learn' ),
		'search_items'               => __( 'Search categories', 'wporg_learn' ),
		'not_found'                  => __( 'Not Found', 'wporg_learn' ),
		'no_terms'                   => __( 'No categories', 'wporg_learn' ),
		'items_list'                 => __( 'Categories list', 'wporg_learn' ),
		'items_list_navigation'      => __( 'Categories list navigation', 'wporg_learn' ),
	);

	$args = array(
		'labels'            => $labels,
		'hierarchical'      => true,
		'public'            => true,
		'rewrite'           => array(
			'slug' => 'lesson-plans'
		),
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_nav_menus' => false,
		'show_tagcloud'     => false,
		'show_in_rest'      => true,
	);

	register_taxonomy( 'wporg_lesson_category', array( 'lesson-plan' ), $args );
}
