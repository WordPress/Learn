<?php

namespace WPOrg_Learn\Taxonomy;

defined( 'WPINC' ) || die();

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
		'name'                       => _x( 'Audiences', 'Taxonomy General Name', 'wporg_learn' ),
		'singular_name'              => _x( 'Audience', 'Taxonomy Singular Name', 'wporg_learn' ),
		'menu_name'                  => __( 'Audience', 'wporg_learn' ),
		'all_items'                  => __( 'All Audiences', 'wporg_learn' ),
		'parent_item'                => __( 'Parent Audience', 'wporg_learn' ),
		'parent_item_colon'          => __( 'Parent Audience:', 'wporg_learn' ),
		'new_item_name'              => __( 'New Audience Name', 'wporg_learn' ),
		'add_new_item'               => __( 'Add Audience', 'wporg_learn' ),
		'edit_item'                  => __( 'Edit Audience', 'wporg_learn' ),
		'update_item'                => __( 'Update Audience', 'wporg_learn' ),
		'view_item'                  => __( 'View Audience', 'wporg_learn' ),
		'separate_items_with_commas' => __( 'Separate Audiences with commas', 'wporg_learn' ),
		'add_or_remove_items'        => __( 'Add or remove Audiences', 'wporg_learn' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'wporg_learn' ),
		'popular_items'              => __( 'Popular Audiences', 'wporg_learn' ),
		'search_items'               => __( 'Search Audiences', 'wporg_learn' ),
		'not_found'                  => __( 'Not Found', 'wporg_learn' ),
		'no_terms'                   => __( 'No Audiences', 'wporg_learn' ),
		'items_list'                 => __( 'Audiences list', 'wporg_learn' ),
		'items_list_navigation'      => __( 'Audiences list navigation', 'wporg_learn' ),
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
		'name'                       => _x( 'Duration', 'Taxonomy General Name', 'wporg_learn' ),
		'singular_name'              => _x( 'Duration', 'Taxonomy Singular Name', 'wporg_learn' ),
		'menu_name'                  => __( 'Duration', 'wporg_learn' ),
		'all_items'                  => __( 'All Durations', 'wporg_learn' ),
		'parent_item'                => __( 'Parent Duration', 'wporg_learn' ),
		'parent_item_colon'          => __( 'Parent Duration:', 'wporg_learn' ),
		'new_item_name'              => __( 'New Duration', 'wporg_learn' ),
		'add_new_item'               => __( 'Add New Duration', 'wporg_learn' ),
		'edit_item'                  => __( 'Edit Duration', 'wporg_learn' ),
		'update_item'                => __( 'Update Duration', 'wporg_learn' ),
		'view_item'                  => __( 'View Duration', 'wporg_learn' ),
		'separate_items_with_commas' => __( 'Separate durations with commas', 'wporg_learn' ),
		'add_or_remove_items'        => __( 'Add or remove durations', 'wporg_learn' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'wporg_learn' ),
		'popular_items'              => __( 'Popular durations', 'wporg_learn' ),
		'search_items'               => __( 'Search durations', 'wporg_learn' ),
		'not_found'                  => __( 'Not Found', 'wporg_learn' ),
		'no_terms'                   => __( 'No durations', 'wporg_learn' ),
		'items_list'                 => __( 'Durations list', 'wporg_learn' ),
		'items_list_navigation'      => __( 'Durations list navigation', 'wporg_learn' ),
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
		'name'                       => _x( 'Lesson Groups', 'Lesson Plans associated to workshop group.', 'wporg_learn' ),
		'singular_name'              => _x( 'Lesson Group', 'Taxonomy Singular Name', 'wporg_learn' ),
		'menu_name'                  => __( 'Lesson Group', 'wporg_learn' ),
		'all_items'                  => __( 'All lesson groups', 'wporg_learn' ),
		'parent_item'                => __( 'Parent lesson group', 'wporg_learn' ),
		'parent_item_colon'          => __( 'Parent lesson group:', 'wporg_learn' ),
		'new_item_name'              => __( 'New lesson group Name', 'wporg_learn' ),
		'add_new_item'               => __( 'Add Lesson Group', 'wporg_learn' ),
		'edit_item'                  => __( 'Edit lesson group', 'wporg_learn' ),
		'update_item'                => __( 'Update lesson group', 'wporg_learn' ),
		'view_item'                  => __( 'View lesson group', 'wporg_learn' ),
		'separate_items_with_commas' => __( 'Separate lesson groups with commas', 'wporg_learn' ),
		'add_or_remove_items'        => __( 'Add or remove lesson groups', 'wporg_learn' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'wporg_learn' ),
		'popular_items'              => __( 'Popular lesson groups', 'wporg_learn' ),
		'search_items'               => __( 'Search lesson groups', 'wporg_learn' ),
		'not_found'                  => __( 'No lesson groups Found', 'wporg_learn' ),
		'no_terms'                   => __( 'No lesson groups', 'wporg_learn' ),
		'items_list'                 => __( 'Lesson groups list', 'wporg_learn' ),
		'items_list_navigation'      => __( 'Lesson groups list navigation', 'wporg_learn' ),
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
		'name'                       => _x( 'Instruction Types', 'Taxonomy General Name', 'wporg_learn' ),
		'singular_name'              => _x( 'Instruction Type', 'Taxonomy Singular Name', 'wporg_learn' ),
		'menu_name'                  => __( 'Instruction Type', 'wporg_learn' ),
		'all_items'                  => __( 'All Instruction Types', 'wporg_learn' ),
		'parent_item'                => __( 'Parent Instruction Type', 'wporg_learn' ),
		'parent_item_colon'          => __( 'Parent Instruction Type:', 'wporg_learn' ),
		'new_item_name'              => __( 'New Instruction Type Name', 'wporg_learn' ),
		'add_new_item'               => __( 'Add Instruction Type', 'wporg_learn' ),
		'edit_item'                  => __( 'Edit Instruction Type', 'wporg_learn' ),
		'update_item'                => __( 'Update Instruction Type', 'wporg_learn' ),
		'view_item'                  => __( 'View Instruction Type', 'wporg_learn' ),
		'separate_items_with_commas' => __( 'Separate Instruction Types with commas', 'wporg_learn' ),
		'add_or_remove_items'        => __( 'Add or remove Instruction Types', 'wporg_learn' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'wporg_learn' ),
		'popular_items'              => __( 'Popular Instruction Types', 'wporg_learn' ),
		'search_items'               => __( 'Search Instruction Types', 'wporg_learn' ),
		'not_found'                  => __( 'Not Found', 'wporg_learn' ),
		'no_terms'                   => __( 'No Instruction Types', 'wporg_learn' ),
		'items_list'                 => __( 'Instruction Types list', 'wporg_learn' ),
		'items_list_navigation'      => __( 'Instruction Types list navigation', 'wporg_learn' ),
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
		'name'                       => _x( 'Experience Levels', 'Taxonomy General Name', 'wporg_learn' ),
		'singular_name'              => _x( 'Experience Level', 'Taxonomy Singular Name', 'wporg_learn' ),
		'menu_name'                  => __( 'Experience Level', 'wporg_learn' ),
		'all_items'                  => __( 'All Experience Levels', 'wporg_learn' ),
		'parent_item'                => __( 'Parent Experience Level', 'wporg_learn' ),
		'parent_item_colon'          => __( 'Parent Experience Level:', 'wporg_learn' ),
		'new_item_name'              => __( 'New Experience Level Name', 'wporg_learn' ),
		'add_new_item'               => __( 'Add New Experience Level', 'wporg_learn' ),
		'edit_item'                  => __( 'Edit Experience Level', 'wporg_learn' ),
		'update_item'                => __( 'Update Experience Level', 'wporg_learn' ),
		'view_item'                  => __( 'View Experience Level', 'wporg_learn' ),
		'separate_items_with_commas' => __( 'Separate experience levels with commas', 'wporg_learn' ),
		'add_or_remove_items'        => __( 'Add or remove experience levels', 'wporg_learn' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'wporg_learn' ),
		'popular_items'              => __( 'Popular Experience levels', 'wporg_learn' ),
		'search_items'               => __( 'Search Experience Levels', 'wporg_learn' ),
		'not_found'                  => __( 'Not Experience Found', 'wporg_learn' ),
		'no_terms'                   => __( 'No experience levels', 'wporg_learn' ),
		'items_list'                 => __( 'Experience Levels list', 'wporg_learn' ),
		'items_list_navigation'      => __( 'Experience Levels list navigation', 'wporg_learn' ),
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
		'name'                       => _x( 'Series', 'Topic Plans associated to workshop.', 'wporg_learn' ),
		'singular_name'              => _x( 'Series', 'Taxonomy Singular Name', 'wporg_learn' ),
		'menu_name'                  => __( 'Series', 'wporg_learn' ),
		'all_items'                  => __( 'All series', 'wporg_learn' ),
		'new_item_name'              => __( 'New Series Name', 'wporg_learn' ),
		'add_new_item'               => __( 'Add Series', 'wporg_learn' ),
		'edit_item'                  => __( 'Edit Series', 'wporg_learn' ),
		'update_item'                => __( 'Update Series', 'wporg_learn' ),
		'view_item'                  => __( 'View Series', 'wporg_learn' ),
		'separate_items_with_commas' => __( 'Separate Series with commas', 'wporg_learn' ),
		'add_or_remove_items'        => __( 'Add or remove Series', 'wporg_learn' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'wporg_learn' ),
		'popular_items'              => __( 'Popular Series', 'wporg_learn' ),
		'search_items'               => __( 'Search Series', 'wporg_learn' ),
		'not_found'                  => __( 'No Series Found', 'wporg_learn' ),
		'no_terms'                   => __( 'No Series ', 'wporg_learn' ),
		'items_list'                 => __( 'Series list', 'wporg_learn' ),
		'items_list_navigation'      => __( 'Series list navigation', 'wporg_learn' ),
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
		'name'                       => _x( 'Topics', 'Topic Plans associated to workshop.', 'wporg_learn' ),
		'singular_name'              => _x( 'Topic', 'Taxonomy Singular Name', 'wporg_learn' ),
		'menu_name'                  => __( 'Topics', 'wporg_learn' ),
		'all_items'                  => __( 'All topic', 'wporg_learn' ),
		'parent_item'                => __( 'Parent topic', 'wporg_learn' ),
		'parent_item_colon'          => __( 'Parent topic:', 'wporg_learn' ),
		'new_item_name'              => __( 'New Topic Name', 'wporg_learn' ),
		'add_new_item'               => __( 'Add Topic', 'wporg_learn' ),
		'edit_item'                  => __( 'Edit Topic', 'wporg_learn' ),
		'update_item'                => __( 'Update Topic', 'wporg_learn' ),
		'view_item'                  => __( 'View Topic', 'wporg_learn' ),
		'separate_items_with_commas' => __( 'Separate Topic with commas', 'wporg_learn' ),
		'add_or_remove_items'        => __( 'Add or remove Topic', 'wporg_learn' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'wporg_learn' ),
		'popular_items'              => __( 'Popular Topics', 'wporg_learn' ),
		'search_items'               => __( 'Search Topics', 'wporg_learn' ),
		'not_found'                  => __( 'No Topic Found', 'wporg_learn' ),
		'no_terms'                   => __( 'No Topic ', 'wporg_learn' ),
		'items_list'                 => __( 'Topic list', 'wporg_learn' ),
		'items_list_navigation'      => __( 'Topic list navigation', 'wporg_learn' ),
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
