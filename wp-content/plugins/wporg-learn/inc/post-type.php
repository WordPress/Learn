<?php

namespace WPOrg_Learn\Post_Type;

use function WordPressdotorg\Locales\get_locale_name_from_code;

defined( 'WPINC' ) || die();

/**
 * Actions and filters.
 */
add_action( 'init', __NAMESPACE__ . '\register' );
add_filter( 'jetpack_copy_post_post_types', __NAMESPACE__ . '\jetpack_copy_post_post_types' );
add_filter( 'jetpack_sitemap_post_types', __NAMESPACE__ . '\jetpack_sitemap_post_types' );
add_filter( 'jetpack_page_sitemap_other_urls', __NAMESPACE__ . '\jetpack_page_sitemap_other_urls' );

/**
 * Register all post types.
 */
function register() {
	register_lesson_plan();
	register_workshop();
}

/**
 * Register a Lesson Plan post type.
 */
function register_lesson_plan() {
	$labels = array(
		'name'                  => _x( 'Lesson Plans', 'Post Type General Name', 'wporg_learn' ),
		'singular_name'         => _x( 'Lesson Plan', 'Post Type Singular Name', 'wporg_learn' ),
		'menu_name'             => __( 'Lesson Plans', 'wporg_learn' ),
		'name_admin_bar'        => __( 'Lesson Plan', 'wporg_learn' ),
		'archives'              => __( 'Lesson Plan Archives', 'wporg_learn' ),
		'attributes'            => __( 'Lesson Plan Attributes', 'wporg_learn' ),
		'parent_item_colon'     => __( 'Parent Lesson Plan:', 'wporg_learn' ),
		'all_items'             => __( 'All Lesson Plans', 'wporg_learn' ),
		'add_new_item'          => __( 'Add New Lesson Plan', 'wporg_learn' ),
		'add_new'               => __( 'Add New', 'wporg_learn' ),
		'new_item'              => __( 'New Lesson Plan', 'wporg_learn' ),
		'edit_item'             => __( 'Edit Lesson Plan', 'wporg_learn' ),
		'update_item'           => __( 'Update Lesson Plan', 'wporg_learn' ),
		'view_item'             => __( 'View Lesson Plan', 'wporg_learn' ),
		'view_items'            => __( 'View Lesson Plans', 'wporg_learn' ),
		'search_items'          => __( 'Search Lesson Plans', 'wporg_learn' ),
		'not_found'             => __( 'No lesson plans found.', 'wporg_learn' ),
		'not_found_in_trash'    => __( 'No lesson plans found in Trash.', 'wporg_learn' ),
		'featured_image'        => __( 'Featured image', 'wporg_learn' ),
		'set_featured_image'    => __( 'Set featured image', 'wporg_learn' ),
		'remove_featured_image' => __( 'Remove featured image', 'wporg_learn' ),
		'use_featured_image'    => __( 'Use as featured image', 'wporg_learn' ),
		'insert_into_item'      => __( 'Insert into lesson plan', 'wporg_learn' ),
		'uploaded_to_this_item' => __( 'Uploaded to this lesson plan', 'wporg_learn' ),
		'items_list'            => __( 'Lesson plans list', 'wporg_learn' ),
		'items_list_navigation' => __( 'Lesson plans list navigation', 'wporg_learn' ),
		'filter_items_list'     => __( 'Filter lesson plans list', 'wporg_learn' ),
	);

	$args   = array(
		'label'               => __( 'Lesson Plan', 'wporg_learn' ),
		'description'         => __( 'WordPress.org Training Lesson Plan', 'wporg_learn' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', 'comments', 'revisions', 'custom-fields' ),
		'taxonomies'          => array( 'duration', 'level', 'audience', 'instruction_type' ),
		'hierarchical'        => true,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => 5,
		'menu_icon'           => 'dashicons-clipboard',
		'show_in_admin_bar'   => true,
		'show_in_nav_menus'   => true,
		'can_export'          => true,
		'has_archive'         => 'lesson-plans',
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => array( 'lesson_plan', 'lesson_plans' ),
		'map_meta_cap'        => true,
		'show_in_rest'        => true,
	);

	register_post_type( 'lesson-plan', $args );
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
		'search_items'          => __( 'Search Workshops', 'wporg_learn' ),
		'not_found'             => __( 'No workshops found.', 'wporg_learn' ),
		'not_found_in_trash'    => __( 'No workshops found in Trash.', 'wporg_learn' ),
		'featured_image'        => __( 'Featured image', 'wporg_learn' ),
		'set_featured_image'    => __( 'Set featured image', 'wporg_learn' ),
		'remove_featured_image' => __( 'Remove featured image', 'wporg_learn' ),
		'use_featured_image'    => __( 'Use as featured image', 'wporg_learn' ),
		'insert_into_item'      => __( 'Insert into workshop', 'wporg_learn' ),
		'uploaded_to_this_item' => __( 'Uploaded to this workshop', 'wporg_learn' ),
		'items_list'            => __( 'Workshops list', 'wporg_learn' ),
		'items_list_navigation' => __( 'Workshops list navigation', 'wporg_learn' ),
		'filter_items_list'     => __( 'Filter workshops list', 'wporg_learn' ),
	);

	$supports = array(
		'comments',
		'custom-fields',
		'editor',
		'excerpt',
		'revisions',
		'thumbnail',
		'title',
		'wporg-internal-notes',
	);

	$args = array(
		'label'               => __( 'Workshop', 'wporg_learn' ),
		'description'         => __( 'WordPress.org Training Workshop', 'wporg_learn' ),
		'labels'              => $labels,
		'supports'            => $supports,
		'taxonomies'          => array( 'topic' ),
		'hierarchical'        => true,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'has_archive'         => 'workshops',
		'menu_position'       => 6,
		'menu_icon'           => 'dashicons-desktop',
		'show_in_admin_bar'   => true,
		'show_in_nav_menus'   => true,
		'can_export'          => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => array( 'workshop', 'workshops' ),
		'map_meta_cap'        => true,
		'show_in_rest'        => true,
		'rewrite'             => array( 'slug' => 'workshop' ),
		'template'            => generate_workshop_template_structure(),
	);

	register_post_type( 'wporg_workshop', $args );
}

/**
 * Create an array representation of a workshop's content template.
 *
 * ⚠️ Note that if this template structure changes, the content in views/content-workshop.php
 * will also need to be updated.
 *
 * @return array
 */
function generate_workshop_template_structure() {
	$template = array(
		// Description
		array(
			'core/paragraph',
			array(
				'placeholder' => __( 'Describe what the workshop is about.', 'wporg-learn' ),
			),
		),

		// Learning outcomes
		array(
			'core/heading',
			array(
				'level'   => '2',
				'content' => __( 'Learning outcomes', 'wporg-learn' ),
			),
		),
		array(
			'core/list',
			array(
				'className' => 'workshop-page_list',
				'ordered'   => true,
			),
		),

		// Comprehension questions
		array(
			'core/heading',
			array(
				'level'   => '2',
				'content' => __( 'Comprehension questions', 'wporg-learn' ),
			),
		),
		array(
			'core/list',
			array(
				'className' => 'workshop-page_list',
				'ordered'   => true,
			),
		),

		// Transcript
		array(
			'core/heading',
			array(
				'className' => 'transcript',
				'anchor'    => 'transcript',
				'level'     => '2',
				'content'   => __( 'Transcript', 'wporg-learn' ),
			),
		),
		array(
			'core/paragraph',
			array(
				'placeholder' => __( 'Copy the transcript from Otter. See handbook for instructions.', 'wporg-learn' ),
			),
		),
	);

	return $template;
}

/**
 * Enable Jetpack's Copy Post module for more post types.
 *
 * @param array $post_types
 *
 * @return mixed
 */
function jetpack_copy_post_post_types( $post_types ) {
	$post_types[] = 'lesson-plan';
	$post_types[] = 'wporg_workshop';

	return $post_types;
}

/**
 * Register our post types with Jetpack Sitemaps.
 *
 * @link https://developer.jetpack.com/hooks/jetpack_sitemap_post_types/
 *
 * @param array $post_types
 * @return array
 */
function jetpack_sitemap_post_types( $post_types ) {
	$post_types[] = 'lesson-plan';
	$post_types[] = 'wporg_workshop';

	return $post_types;
}

/**
 * Register our post type archives with Jetpack Sitemaps.
 *
 * @link https://developer.jetpack.com/hooks/jetpack_page_sitemap_other_urls/
 *
 * @param array $urls
 * @return array
 */
function jetpack_page_sitemap_other_urls( $urls ) {
	foreach ( array( 'wporg_workshop', 'lesson-plan' ) as $post_type ) {
		$url = get_post_type_archive_link( $post_type );
		if ( ! $url ) {
			continue;
		}

		$latest_post = get_posts( array(
			'post_type'   => $post_type,
			'numberposts' => 1,
			'orderby'     => 'date',
			'order'       => 'DESC',
		) );
		if ( ! $latest_post ) {
			continue;
		}

		$urls[] = array(
			'loc'     => $url,
			'lastmod' => gmdate(
				'Y-m-d\TH:i:s\Z',
				strtotime( $latest_post[0]->post_modified_gmt )
			),
		);
	}

	return $urls;
}
