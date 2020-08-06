<?php
namespace WPOrg_Learn;

class Workshop {

	/**
	 *  Register Workshop Post Type
	*/
	public static function workshop_post_type() {
		$labels = array(
			'name'                  => _x( 'Workshops', 'Post Type General Name', 'wporg_learn' ),
			'singular_name'         => _x( 'Workshop', 'Post Type Singular Name', 'wporg_learn' ),
			'menu_name'             => __( 'Workshops', 'wporg_learn' ),
			'name_admin_bar'        => __( 'Workshop', 'wporg_learn' ),
			'archives'              => __( 'Workshop Archives', 'wporg_learn' ),
			'attributes'            => __( 'Workshop Attributes', 'wporg_learn' ),
			'parent_item_colon'     => __( 'Parent Workshop:', 'wporg_learn' ),
			'all_items'             => __( 'All Workshops', 'wporg_learn' ),
			'add_new_item'          => __( 'Add New Workshop', 'wporg_learn' ),
			'add_new'               => __( 'Add New', 'wporg_learn' ),
			'new_item'              => __( 'New Workshop', 'wporg_learn' ),
			'edit_item'             => __( 'Edit Workshop', 'wporg_learn' ),
			'update_item'           => __( 'Update Workshop', 'wporg_learn' ),
			'view_item'             => __( 'View Workshop', 'wporg_learn' ),
			'view_items'            => __( 'View Workshops', 'wporg_learn' ),
			'search_items'          => __( 'Search Workshop', 'wporg_learn' ),
			'not_found'             => __( 'Not found', 'wporg_learn' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'wporg_learn' ),
			'featured_image'        => __( 'Featured Image', 'wporg_learn' ),
			'set_featured_image'    => __( 'Set featured image', 'wporg_learn' ),
			'remove_featured_image' => __( 'Remove featured image', 'wporg_learn' ),
			'use_featured_image'    => __( 'Use as featured image', 'wporg_learn' ),
			'insert_into_item'      => __( 'Insert into workshop', 'wporg_learn' ),
			'uploaded_to_this_item' => __( 'Uploaded to this workshop', 'wporg_learn' ),
			'items_list'            => __( 'Workshops list', 'wporg_learn' ),
			'items_list_navigation' => __( 'Workshops list navigation', 'wporg_learn' ),
			'filter_items_list'     => __( 'Filter Workshops list', 'wporg_learn' ),
		);
		$args = array(
			'label'                 => __( 'Workshop', 'wporg_learn' ),
			'description'           => __( 'WordPress.org Training Workshop', 'wporg_learn' ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'editor', 'comments', 'revisions', 'custom-fields' ),
			'taxonomies'            => array( 'lesson_group', 'topic', 'category' ),
			'hierarchical'          => true,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'has_archive'           => 'workshops',
			'menu_position'         => 6,
			'menu_icon'             => 'dashicons-category',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'page',
			'show_in_rest'          => true

		);
		register_post_type( 'workshop', $args );
	}

	/**
	 * Register Workshop Grouping Taxonomy
	*/
	public static function lesson_workshop_taxonomy() {
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
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
			'show_tagcloud'              => false,
			'show_in_rest'               => true,
		);

		register_taxonomy( 'lesson_group', array( 'lesson-plan' ), $args );
	}

	/**
	 * Register Workshop Topics Taxonomy
	*/
	public static function workshop_topics_taxonomy() {
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
			'labels'                     => $labels,
			'hierarchical'               => false,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
			'show_tagcloud'              => false,
			'show_in_rest'               => true,
		);

		register_taxonomy( 'topic', array( 'wporg_workshop' ), $args );
	}

}
