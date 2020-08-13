<?php

namespace WPOrg_Learn\Post_Types;

defined( 'WPINC' ) || die();

/**
 * Register all post types.
 */
function register() {
	register_workshop();
}

/**
 * Register a Workshop post type.
 */
function register_workshop() {
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

	$video_template_part = array( 'core/group',
		array( 'className' => 'workshop-page_video' ),
		array( array( 'core-embed/wordpress-tv' ) ),
	);

	$outcome_template_part = array( 'core/group',
		array( 'className' => 'workshop-page_list' ),
		array(
			array( 'core/heading', array(
				'level'   => '2',
				'content' => __( 'Learning outcomes', 'wporg-learn' ),
			),
		),
			array( 'core/list', array(
				'ordered' => true,
			 ),
		),
		),
	);

	$comprehension_template_part = array( 'core/group',
		array( 'className' => 'workshop-page_list' ),
		array(
			array( 'core/heading', array(
				'level'   => '2',
				'content' => __( 'Comprehension questions', 'wporg-learn' ),
			),
		),
			array( 'core/list', array(
				'ordered' => true,
			),
		),
		),
	);

	$sidebar_template_part = array( 'core/group',
		array( 'className' => 'workshop-page_sidebar' ),
		array(
			array( 'wporg-learn/workshop-details' ),
			array( 'core/button', array(
				'text'         => __( 'Join a Group Discussion', 'wporg-learn' ),
				'url'          => 'https://www.meetup.com/learn-wordpress-discussions/events/',
				'borderRadius' => 5,
				'className'    => 'is-style-secondary-full-width',
			),
		),
			array( 'core/paragraph', array(
				'className' => 'terms',
				'content'   => sprintf(
					__( 'You must agree to our <a href="%s">Code of Conduct</a> in order to participate.', 'wporg-learn' ),
					'https://learn.wordpress.org/code-of-conduct/'
				),
			),
		),
		),
	);

	$args = array(
		'label'               => __( 'Workshop', 'wporg_learn' ),
		'description'         => __( 'WordPress.org Training Workshop', 'wporg_learn' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', 'comments', 'revisions', 'custom-fields', 'thumbnail', 'excerpt' ),
		'taxonomies'          => array( 'level', 'topic' ),
		'hierarchical'        => true,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'has_archive'         => 'workshops',
		'menu_position'       => 6,
		'menu_icon'           => 'dashicons-category',
		'show_in_admin_bar'   => true,
		'show_in_nav_menus'   => true,
		'can_export'          => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'page',
		'show_in_rest'        => true,
		'template_lock'       => 'all',
		'rewrite'             => array( 'slug' => 'workshop' ),
		'template'            => array(
			array( 'core/group',
			array( 'className' => 'workshop-page_content' ),
				array(
					$video_template_part,
					array( 'core/columns', array(), array(
						array( 'core/column', array( 'width' => 66.66 ), array(
							array( 'core/paragraph', array(
								'placeholder' => __( 'Describe what the workshop is about', 'wporg-learn' ),
							),
						),
							$outcome_template_part,
							$comprehension_template_part,
						),
					),
						array( 'core/column', array( 'width' => 33.333 ), array(
							$sidebar_template_part,
						),
					),
					),
				),
				),
			),
			array( 'core/separator', array() ),
		),
	);

	register_post_type( 'wporg_workshop', $args );
}
