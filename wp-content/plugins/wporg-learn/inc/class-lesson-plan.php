<?php
namespace WPOrg_Learn;

class Lesson_Plan {

/**
  *  Register Lesson Plan Post Type
  */
public static function lesson_plan_post_type() {

	$labels = array(
		'name'                  => _x( 'Lesson Plans', 'Post Type General Name', 'wporg_learn' ),
		'singular_name'         => _x( 'Lesson Plan', 'Post Type Singular Name', 'wporg_learn' ),
		'menu_name'             => __( 'Lesson Plans', 'wporg_learn' ),
		'name_admin_bar'        => __( 'Lesson Plan', 'wporg_learn' ),
		'archives'              => __( 'Lesson Archives', 'wporg_learn' ),
		'attributes'            => __( 'Lesson Attributes', 'wporg_learn' ),
		'parent_item_colon'     => __( 'Parent Lesson:', 'wporg_learn' ),
		'all_items'             => __( 'All Lessons', 'wporg_learn' ),
		'add_new_item'          => __( 'Add New Lesson', 'wporg_learn' ),
		'add_new'               => __( 'Add New', 'wporg_learn' ),
		'new_item'              => __( 'New Lesson Plan', 'wporg_learn' ),
		'edit_item'             => __( 'Edit Lesson Plan', 'wporg_learn' ),
		'update_item'           => __( 'Update Lesson Plan', 'wporg_learn' ),
		'view_item'             => __( 'View Lesson', 'wporg_learn' ),
		'view_items'            => __( 'View Lessons', 'wporg_learn' ),
		'search_items'          => __( 'Search Lesson', 'wporg_learn' ),
		'not_found'             => __( 'Not found', 'wporg_learn' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'wporg_learn' ),
		'featured_image'        => __( 'Featured Image', 'wporg_learn' ),
		'set_featured_image'    => __( 'Set featured image', 'wporg_learn' ),
		'remove_featured_image' => __( 'Remove featured image', 'wporg_learn' ),
		'use_featured_image'    => __( 'Use as featured image', 'wporg_learn' ),
		'insert_into_item'      => __( 'Insert into lesson', 'wporg_learn' ),
		'uploaded_to_this_item' => __( 'Uploaded to this lesson', 'wporg_learn' ),
		'items_list'            => __( 'Lessons list', 'wporg_learn' ),
		'items_list_navigation' => __( 'Lessons list navigation', 'wporg_learn' ),
		'filter_items_list'     => __( 'Filter Lessons list', 'wporg_learn' ),
	);
	$args = array(
		'label'                 => __( 'Lesson Plan', 'wporg_learn' ),
		'description'           => __( 'WordPress.org Training Lesson Plan', 'wporg_learn' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor', 'comments', 'revisions', 'custom-fields' ),
		'taxonomies'            => array( 'duration', 'level', 'audience', 'instruction_type' ),
		'hierarchical'          => true,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'menu_icon'             => 'dashicons-welcome-learn-more',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => 'lesson-plans',
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'page',
		'show_in_rest'          => true,
	);
	register_post_type( 'lesson-plan', $args );
}

/**
  * Register duration Taxonomy
  */
public static function lesson_duration_taxonomy() {

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
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => false,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => false,
		'show_tagcloud'              => false,
		'show_in_rest'               => true,
	);
	register_taxonomy( 'duration', array( 'lesson-plan' ), $args );

}

/**
  * Register Lesson Experience Level Taxonomy
  */
public static function lesson_level_taxonomy() {

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
	register_taxonomy( 'level', array( 'lesson-plan' ), $args );

}

/**
  * Register Lesson Audience Taxonomy
  */
public static function lesson_audience_taxonomy() {

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
	register_taxonomy( 'audience', array( 'lesson-plan' ), $args );

}

/**
  * Register Instruction Type Taxonomy
  */
public static function lesson_instruction_type_taxonomy() {

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
	register_taxonomy( 'instruction_type', array( 'lesson-plan' ), $args );

}

	/**
	 * Append an "Edit on GitHub" link to Lesson Plan document titles
	 */
	public static function filter_the_title_edit_link( $title, $id = null ) {
		// Only apply to the main title for the document
		if ( ! is_singular( 'lesson_plan' )
			|| ! is_main_query()
			|| ! in_the_loop()
			|| is_embed()
			|| $id !== get_queried_object_id() ) {
			return $title;
		}

		$markdown_source = self::get_markdown_edit_link( get_the_ID() );
		if ( ! $markdown_source ) {
			return $title;
		}

		return $title . ' <a class="github-edit" href="' . esc_url( $markdown_source ) . '"><img src="' . esc_url( plugins_url( 'assets/images/github-mark.svg', dirname( __FILE__ ) ) ) . '"> <span>Edit</span></a>';
	}

	/**
	 * Learn lesson plan pages are maintained in the GitHub repo, so the edit
	 * link should ridirect to there.
	 */
	public static function redirect_edit_link_to_github( $link, $post_id, $context ) {
		if ( is_admin() ) {
			return $link;
		}
		$post = get_post( $post_id );
		if ( ! $post ) {
			return $link;
		}

		if ( 'lesson_plan' !== $post->post_type ) {
			return $link;
		}

		$markdown_source = self::get_markdown_edit_link( $post_id );
		if ( ! $markdown_source ) {
			return $link;
		}

		if ( 'display' === $context ) {
			$markdown_source = esc_url( $markdown_source );
		}

		return $markdown_source;
	}

	/**
	 * o2 does inline editing, so we also need to remove the class name that it looks for.
	 *
	 * o2 obeys the edit_post capability for displaying the edit link, so we also need to manually
	 * add the edit link if it isn't there - it always redirects to GitHub, so it doesn't need to
	 * obey the edit_post capability in this instance.
	 */
	public static function redirect_o2_edit_link_to_github( $actions, $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return $actions;
		}

		if ( 'lesson_plan' !== $post->post_type ) {
			return $actions;
		}

		$markdown_source = self::get_markdown_edit_link( $post_id );
		if ( ! $markdown_source ) {
			return $actions;
		}

		/*
		 * Define our own edit post action for o2.
		 *
		 * Notable differences from the original are:
		 * - the 'href' parameter always goes to the GitHub source.
		 * - the 'o2-edit' class is missing, so inline editing is disabled.
		 */
		$edit_action = array(
			'action' => 'edit',
			'href' => $markdown_source,
			'classes' => array( 'edit-post-link' ),
			'rel' => $post_id,
			'initialState' => 'default'
		);

		// Find and replace the existing edit action.
		$replaced = false;
		foreach( $actions as &$action ) {
			if ( 'edit' === $action['action'] ) {
				$action = $edit_action;
				$replaced = true;
				break;
			}
		}
		unset( $action );

		// If there was no edit action replaced, add it in manually.
		if ( ! $replaced ) {
			$actions[30] = $edit_action;
		}

		return $actions;
	}

	private static function get_markdown_edit_link( $post_id ) {
		$markdown_source = Markdown_Import::get_markdown_source( $post_id );
		if ( is_wp_error( $markdown_source ) ) {
			return '';
		}
		if ( 'wptrainingteam.github.io' !== parse_url( $markdown_source, PHP_URL_HOST )
			|| false !== stripos( $markdown_source, '/edit/master/' ) ) {
			return $markdown_source;
		}
		$markdown_source = str_replace( '/lesson-plan', '', $markdown_source);
		$markdown_source = str_replace( 'wptrainingteam.github.io', 'github.com/wptrainingteam', $markdown_source);
		$markdown_source = str_replace( '/README.md', '/edit/dev/README.md', $markdown_source );
		return $markdown_source;
	}

  public static function replace_image_links( $content ) {
    $post_id = get_the_ID();
    $markdown_source = Markdown_Import::get_markdown_source( $post_id );
    if ( is_wp_error( $markdown_source ) ) {
      return $content;
    }
    $markdown_source = str_replace( '/README.md', '', $markdown_source );
    $content = str_replace( '<img src="/images/', '<img src="' . $markdown_source . '/images/', $content );

    return $content;
  }
}
