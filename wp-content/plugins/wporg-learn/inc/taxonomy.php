<?php

namespace WPOrg_Learn\Taxonomy;

defined( 'WPINC' ) || die();

/**
 * Actions and filters.
 */
add_action( 'init', __NAMESPACE__ . '\register' );

/**
 * Register all the taxonomies.
 */
function register() {
	register_lesson_audience();
	register_lesson_category();
	register_lesson_duration();
	register_lesson_group();
	register_lesson_instruction_type();
	register_lesson_level();
	register_workshop_series();
	register_workshop_topic();
}

/**
 * Register the Lesson Audience taxonomy.
 */
function register_lesson_audience() {
	$labels = array(
		'name'                       => _x( 'Audiences', 'Taxonomy General Name', 'wporg-learn' ),
		'singular_name'              => _x( 'Audience', 'Taxonomy Singular Name', 'wporg-learn' ),
		'menu_name'                  => __( 'Audience', 'wporg-learn' ),
		'all_items'                  => __( 'All Audiences', 'wporg-learn' ),
		'parent_item'                => __( 'Parent Audience', 'wporg-learn' ),
		'parent_item_colon'          => __( 'Parent Audience:', 'wporg-learn' ),
		'new_item_name'              => __( 'New Audience Name', 'wporg-learn' ),
		'add_new_item'               => __( 'Add Audience', 'wporg-learn' ),
		'edit_item'                  => __( 'Edit Audience', 'wporg-learn' ),
		'update_item'                => __( 'Update Audience', 'wporg-learn' ),
		'view_item'                  => __( 'View Audience', 'wporg-learn' ),
		'separate_items_with_commas' => __( 'Separate Audiences with commas', 'wporg-learn' ),
		'add_or_remove_items'        => __( 'Add or remove Audiences', 'wporg-learn' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'wporg-learn' ),
		'popular_items'              => __( 'Popular Audiences', 'wporg-learn' ),
		'search_items'               => __( 'Search Audiences', 'wporg-learn' ),
		'not_found'                  => __( 'Not Found', 'wporg-learn' ),
		'no_terms'                   => __( 'No Audiences', 'wporg-learn' ),
		'items_list'                 => __( 'Audiences list', 'wporg-learn' ),
		'items_list_navigation'      => __( 'Audiences list navigation', 'wporg-learn' ),
	);

	$args   = array(
		'labels'            => $labels,
		'hierarchical'      => false,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_nav_menus' => true,
		'show_tagcloud'     => false,
		'show_in_rest'      => true,
	);

	register_taxonomy( 'audience', array( 'lesson-plan' ), $args );
}

/**
 * Register the Lesson Category taxonomy.
 */
function register_lesson_category() {
	$labels = array(
		'name'                       => _x( 'Categories', 'Taxonomy General Name', 'wporg-learn' ),
		'singular_name'              => _x( 'Category', 'Taxonomy Singular Name', 'wporg-learn' ),
		'menu_name'                  => __( 'Categories', 'wporg-learn' ),
		'all_items'                  => __( 'All Categories', 'wporg-learn' ),
		'new_item_name'              => __( 'New Category', 'wporg-learn' ),
		'add_new_item'               => __( 'Add New Category', 'wporg-learn' ),
		'edit_item'                  => __( 'Edit Category', 'wporg-learn' ),
		'update_item'                => __( 'Update Category', 'wporg-learn' ),
		'view_item'                  => __( 'View Category', 'wporg-learn' ),
		'separate_items_with_commas' => __( 'Separate categories with commas', 'wporg-learn' ),
		'add_or_remove_items'        => __( 'Add or remove categories', 'wporg-learn' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'wporg-learn' ),
		'popular_items'              => __( 'Popular categories', 'wporg-learn' ),
		'search_items'               => __( 'Search categories', 'wporg-learn' ),
		'not_found'                  => __( 'Not Found', 'wporg-learn' ),
		'no_terms'                   => __( 'No categories', 'wporg-learn' ),
		'items_list'                 => __( 'Categories list', 'wporg-learn' ),
		'items_list_navigation'      => __( 'Categories list navigation', 'wporg-learn' ),
	);

	$args = array(
		'labels'            => $labels,
		'hierarchical'      => true,
		'public'            => true,
		'rewrite'           => array(
			'slug' => 'lesson-plans',
		),
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_nav_menus' => false,
		'show_tagcloud'     => false,
		'show_in_rest'      => true,
	);

	register_taxonomy( 'wporg_lesson_category', array( 'lesson-plan' ), $args );
}

/**
 * Register the Lesson Duration taxonomy.
 */
function register_lesson_duration() {
	$labels = array(
		'name'                       => _x( 'Duration', 'Taxonomy General Name', 'wporg-learn' ),
		'singular_name'              => _x( 'Duration', 'Taxonomy Singular Name', 'wporg-learn' ),
		'menu_name'                  => __( 'Duration', 'wporg-learn' ),
		'all_items'                  => __( 'All Durations', 'wporg-learn' ),
		'parent_item'                => __( 'Parent Duration', 'wporg-learn' ),
		'parent_item_colon'          => __( 'Parent Duration:', 'wporg-learn' ),
		'new_item_name'              => __( 'New Duration', 'wporg-learn' ),
		'add_new_item'               => __( 'Add New Duration', 'wporg-learn' ),
		'edit_item'                  => __( 'Edit Duration', 'wporg-learn' ),
		'update_item'                => __( 'Update Duration', 'wporg-learn' ),
		'view_item'                  => __( 'View Duration', 'wporg-learn' ),
		'separate_items_with_commas' => __( 'Separate durations with commas', 'wporg-learn' ),
		'add_or_remove_items'        => __( 'Add or remove durations', 'wporg-learn' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'wporg-learn' ),
		'popular_items'              => __( 'Popular durations', 'wporg-learn' ),
		'search_items'               => __( 'Search durations', 'wporg-learn' ),
		'not_found'                  => __( 'Not Found', 'wporg-learn' ),
		'no_terms'                   => __( 'No durations', 'wporg-learn' ),
		'items_list'                 => __( 'Durations list', 'wporg-learn' ),
		'items_list_navigation'      => __( 'Durations list navigation', 'wporg-learn' ),
	);

	$args   = array(
		'labels'            => $labels,
		'hierarchical'      => false,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_nav_menus' => false,
		'show_tagcloud'     => false,
		'show_in_rest'      => true,
	);

	register_taxonomy( 'duration', array( 'lesson-plan' ), $args );
}

/**
 * Register the Lesson Group taxonomy.
 */
function register_lesson_group() {
	$labels = array(
		'name'                       => _x( 'Lesson Groups', 'Lesson Plans associated to workshop group.', 'wporg-learn' ),
		'singular_name'              => _x( 'Lesson Group', 'Taxonomy Singular Name', 'wporg-learn' ),
		'menu_name'                  => __( 'Lesson Group', 'wporg-learn' ),
		'all_items'                  => __( 'All lesson groups', 'wporg-learn' ),
		'parent_item'                => __( 'Parent lesson group', 'wporg-learn' ),
		'parent_item_colon'          => __( 'Parent lesson group:', 'wporg-learn' ),
		'new_item_name'              => __( 'New lesson group Name', 'wporg-learn' ),
		'add_new_item'               => __( 'Add Lesson Group', 'wporg-learn' ),
		'edit_item'                  => __( 'Edit lesson group', 'wporg-learn' ),
		'update_item'                => __( 'Update lesson group', 'wporg-learn' ),
		'view_item'                  => __( 'View lesson group', 'wporg-learn' ),
		'separate_items_with_commas' => __( 'Separate lesson groups with commas', 'wporg-learn' ),
		'add_or_remove_items'        => __( 'Add or remove lesson groups', 'wporg-learn' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'wporg-learn' ),
		'popular_items'              => __( 'Popular lesson groups', 'wporg-learn' ),
		'search_items'               => __( 'Search lesson groups', 'wporg-learn' ),
		'not_found'                  => __( 'No lesson groups Found', 'wporg-learn' ),
		'no_terms'                   => __( 'No lesson groups', 'wporg-learn' ),
		'items_list'                 => __( 'Lesson groups list', 'wporg-learn' ),
		'items_list_navigation'      => __( 'Lesson groups list navigation', 'wporg-learn' ),
	);

	$args = array(
		'labels'            => $labels,
		'hierarchical'      => true,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_nav_menus' => true,
		'show_tagcloud'     => false,
		'show_in_rest'      => true,
	);

	register_taxonomy( 'lesson_group', array( 'lesson-plan' ), $args );
}

/**
 * Register the Lesson Instruction Type taxonomy.
 */
function register_lesson_instruction_type() {
	$labels = array(
		'name'                       => _x( 'Instruction Types', 'Taxonomy General Name', 'wporg-learn' ),
		'singular_name'              => _x( 'Instruction Type', 'Taxonomy Singular Name', 'wporg-learn' ),
		'menu_name'                  => __( 'Instruction Type', 'wporg-learn' ),
		'all_items'                  => __( 'All Instruction Types', 'wporg-learn' ),
		'parent_item'                => __( 'Parent Instruction Type', 'wporg-learn' ),
		'parent_item_colon'          => __( 'Parent Instruction Type:', 'wporg-learn' ),
		'new_item_name'              => __( 'New Instruction Type Name', 'wporg-learn' ),
		'add_new_item'               => __( 'Add Instruction Type', 'wporg-learn' ),
		'edit_item'                  => __( 'Edit Instruction Type', 'wporg-learn' ),
		'update_item'                => __( 'Update Instruction Type', 'wporg-learn' ),
		'view_item'                  => __( 'View Instruction Type', 'wporg-learn' ),
		'separate_items_with_commas' => __( 'Separate Instruction Types with commas', 'wporg-learn' ),
		'add_or_remove_items'        => __( 'Add or remove Instruction Types', 'wporg-learn' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'wporg-learn' ),
		'popular_items'              => __( 'Popular Instruction Types', 'wporg-learn' ),
		'search_items'               => __( 'Search Instruction Types', 'wporg-learn' ),
		'not_found'                  => __( 'Not Found', 'wporg-learn' ),
		'no_terms'                   => __( 'No Instruction Types', 'wporg-learn' ),
		'items_list'                 => __( 'Instruction Types list', 'wporg-learn' ),
		'items_list_navigation'      => __( 'Instruction Types list navigation', 'wporg-learn' ),
	);

	$args   = array(
		'labels'            => $labels,
		'hierarchical'      => false,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_nav_menus' => true,
		'show_tagcloud'     => false,
		'show_in_rest'      => true,
	);

	register_taxonomy( 'instruction_type', array( 'lesson-plan' ), $args );
}

/**
 * Register the Lesson Level taxonomy.
 */
function register_lesson_level() {
	$labels = array(
		'name'                       => _x( 'Experience Levels', 'Taxonomy General Name', 'wporg-learn' ),
		'singular_name'              => _x( 'Experience Level', 'Taxonomy Singular Name', 'wporg-learn' ),
		'menu_name'                  => __( 'Experience Level', 'wporg-learn' ),
		'all_items'                  => __( 'All Experience Levels', 'wporg-learn' ),
		'parent_item'                => __( 'Parent Experience Level', 'wporg-learn' ),
		'parent_item_colon'          => __( 'Parent Experience Level:', 'wporg-learn' ),
		'new_item_name'              => __( 'New Experience Level Name', 'wporg-learn' ),
		'add_new_item'               => __( 'Add New Experience Level', 'wporg-learn' ),
		'edit_item'                  => __( 'Edit Experience Level', 'wporg-learn' ),
		'update_item'                => __( 'Update Experience Level', 'wporg-learn' ),
		'view_item'                  => __( 'View Experience Level', 'wporg-learn' ),
		'separate_items_with_commas' => __( 'Separate experience levels with commas', 'wporg-learn' ),
		'add_or_remove_items'        => __( 'Add or remove experience levels', 'wporg-learn' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'wporg-learn' ),
		'popular_items'              => __( 'Popular Experience levels', 'wporg-learn' ),
		'search_items'               => __( 'Search Experience Levels', 'wporg-learn' ),
		'not_found'                  => __( 'Not Experience Found', 'wporg-learn' ),
		'no_terms'                   => __( 'No experience levels', 'wporg-learn' ),
		'items_list'                 => __( 'Experience Levels list', 'wporg-learn' ),
		'items_list_navigation'      => __( 'Experience Levels list navigation', 'wporg-learn' ),
	);

	$args   = array(
		'labels'            => $labels,
		'hierarchical'      => false,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_nav_menus' => true,
		'show_tagcloud'     => false,
		'show_in_rest'      => true,
	);

	register_taxonomy( 'level', array( 'lesson-plan' ), $args );
}

/**
 * Register the Workshop Series taxonomy.
 */
function register_workshop_series() {
	$labels = array(
		'name'                       => _x( 'Workshop Series', 'taxonomy general name', 'wporg-learn' ),
		'singular_name'              => _x( 'Workshop Series', 'taxonomy singular name', 'wporg-learn' ),
		'menu_name'                  => __( 'Series', 'wporg-learn' ),
		'all_items'                  => __( 'All Series', 'wporg-learn' ),
		'new_item_name'              => __( 'New Series Name', 'wporg-learn' ),
		'add_new_item'               => __( 'Add Series', 'wporg-learn' ),
		'edit_item'                  => __( 'Edit Series', 'wporg-learn' ),
		'update_item'                => __( 'Update Series', 'wporg-learn' ),
		'view_item'                  => __( 'View Series', 'wporg-learn' ),
		'separate_items_with_commas' => __( 'Separate series with commas', 'wporg-learn' ),
		'add_or_remove_items'        => __( 'Add or remove series', 'wporg-learn' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'wporg-learn' ),
		'popular_items'              => __( 'Popular Series', 'wporg-learn' ),
		'search_items'               => __( 'Search Series', 'wporg-learn' ),
		'not_found'                  => __( 'No series found', 'wporg-learn' ),
		'no_terms'                   => __( 'No series ', 'wporg-learn' ),
		'items_list'                 => __( 'Series list', 'wporg-learn' ),
		'items_list_navigation'      => __( 'Series list navigation', 'wporg-learn' ),
		'back_to_items'              => __( '&larr; Back to Series', 'wporg-learn' ),
	);

	$args = array(
		'labels'            => $labels,
		'hierarchical'      => false,
		'public'            => true,
		'rewrite'           => array(
			'slug' => 'workshops',
		),
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_nav_menus' => true,
		'show_tagcloud'     => false,
		'show_in_rest'      => true,
	);

	register_taxonomy( 'wporg_workshop_series', array( 'wporg_workshop' ), $args );
}

/**
 * Register the Workshop Topic taxonomy.
 */
function register_workshop_topic() {
	$labels = array(
		'name'                       => _x( 'Topics', 'Topic Plans associated to workshop.', 'wporg-learn' ),
		'singular_name'              => _x( 'Topic', 'Taxonomy Singular Name', 'wporg-learn' ),
		'menu_name'                  => __( 'Topics', 'wporg-learn' ),
		'all_items'                  => __( 'All topic', 'wporg-learn' ),
		'parent_item'                => __( 'Parent topic', 'wporg-learn' ),
		'parent_item_colon'          => __( 'Parent topic:', 'wporg-learn' ),
		'new_item_name'              => __( 'New Topic Name', 'wporg-learn' ),
		'add_new_item'               => __( 'Add Topic', 'wporg-learn' ),
		'edit_item'                  => __( 'Edit Topic', 'wporg-learn' ),
		'update_item'                => __( 'Update Topic', 'wporg-learn' ),
		'view_item'                  => __( 'View Topic', 'wporg-learn' ),
		'separate_items_with_commas' => __( 'Separate Topic with commas', 'wporg-learn' ),
		'add_or_remove_items'        => __( 'Add or remove Topic', 'wporg-learn' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'wporg-learn' ),
		'popular_items'              => __( 'Popular Topics', 'wporg-learn' ),
		'search_items'               => __( 'Search Topics', 'wporg-learn' ),
		'not_found'                  => __( 'No Topic Found', 'wporg-learn' ),
		'no_terms'                   => __( 'No Topic ', 'wporg-learn' ),
		'items_list'                 => __( 'Topic list', 'wporg-learn' ),
		'items_list_navigation'      => __( 'Topic list navigation', 'wporg-learn' ),
	);

	$args = array(
		'labels'            => $labels,
		'hierarchical'      => false,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_nav_menus' => true,
		'show_tagcloud'     => false,
		'show_in_rest'      => true,
	);

	register_taxonomy( 'topic', array( 'wporg_workshop' ), $args );
}
