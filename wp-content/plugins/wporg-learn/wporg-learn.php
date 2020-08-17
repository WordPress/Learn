<?php
/**
 * Plugin name: WordPress.org Learn
 * Description: Functionality for learn.wordpress.org. See also the wporg-learn-2020 theme.
 * Version:     1.0.0
 * Author:      WordPress.org
 * Author URI:  http://wordpress.org/
 * License:     GPLv2 or later
 */

require_once dirname( __FILE__ ) . '/inc/class-shortcodes.php';
require_once dirname( __FILE__ ) . '/inc/class-lesson-plan.php';
require_once dirname( __FILE__ ) . '/inc/blocks.php';
require_once dirname( __FILE__ ) . '/inc/post-meta.php';
require_once dirname( __FILE__ ) . '/inc/post-type.php';
require_once dirname( __FILE__ ) . '/inc/taxonomy.php';

/**
 * Registry of actions and filters
 */
add_action( 'init', 'WPORG_Learn\Post_Type\register' );
add_action( 'init', 'WPORG_Learn\Post_Meta\register' );
add_action( 'init', 'WPORG_Learn\Taxonomy\register' );
add_action( 'init', array( 'WPOrg_Learn\Shortcodes', 'action_init' ) );
add_filter( 'the_title', array( 'WPOrg_Learn\Lesson_Plan', 'filter_the_title_edit_link' ), 10, 2 );
add_filter( 'get_edit_post_link', array( 'WPOrg_Learn\Lesson_Plan', 'redirect_edit_link_to_github' ), 10, 3 );
add_filter( 'o2_filter_post_actions', array( 'WPOrg_Learn\Lesson_Plan', 'redirect_o2_edit_link_to_github' ), 11, 2 );
add_action( 'init', array( 'WPORG_Learn\Lesson_Plan', 'lesson_plan_post_type' ) );
add_filter( 'the_content', array( 'WPORG_Learn\Lesson_Plan', 'replace_image_links' ) );

add_action( 'init', 'WPORG_Learn\Blocks\workshop_details_init' );
add_action( 'enqueue_block_editor_assets', 'WPORG_Learn\Blocks\enqueue_block_style_assets' );
add_action( 'wp_enqueue_scripts', 'WPORG_Learn\Blocks\enqueue_block_style_assets' );
add_action( 'add_meta_boxes', 'WPORG_Learn\Post_Meta\add_workshop_metaboxes' );
add_action( 'save_post_wporg_workshop', 'WPORG_Learn\Post_Meta\save_workshop_metabox_fields', 10, 2 );
add_filter( 'excerpt_length', 'theme_slug_excerpt_length', 999 );

require_once dirname( __FILE__ ) . '/inc/class-markdown-import.php';
/**
 * Markdown Import
 *
 * This functionality has been disabled as of 2020-08-12. All of the lesson plans have been imported
 * to learn.wordpress.org and can be updated via the WP admin interface. Leaving this here for now
 * in case we need to re-activate for some reason.
 *
add_action( 'init', array( 'WPOrg_Learn\Markdown_Import', 'action_init' ) );
add_action( 'wporg_learn_manifest_import', array( 'WPOrg_Learn\Markdown_Import', 'action_wporg_learn_manifest_import' ) );
add_action( 'wporg_learn_markdown_import', array( 'WPOrg_Learn\Markdown_Import', 'action_wporg_learn_markdown_import' ) );
add_action( 'load-post.php', array( 'WPOrg_Learn\Markdown_Import', 'action_load_post_php' ) );
add_action( 'edit_form_after_title', array( 'WPOrg_Learn\Markdown_Import', 'action_edit_form_after_title' ) );
add_action( 'save_post', array( 'WPOrg_Learn\Markdown_Import', 'action_save_post' ) );
add_filter( 'cron_schedules', array( 'WPOrg_Learn\Markdown_Import', 'filter_cron_schedules' ) );
 */

/**
 * Filter the excerpt length to 50 words.
 *
 * @param int $length Excerpt length.
 * @return int (Maybe) modified excerpt length.
 */
function theme_slug_excerpt_length( $length ) {
	global $post;

	if ( is_admin() ) {
		return $length;
	}

	if ( 'workshop' === $post->post_type ) {
		return 35;
	}

	return 25;
}

add_action( 'wp_head', function() {
	?>
	<style>
		pre code {
			line-height: 16px;
		}
		a.github-edit {
			margin-left: .5em;
			font-size: .5em;
			vertical-align: top;
			display: inline-block;
			border: 1px solid #eeeeee;
			border-radius: 2px;
			background: #eeeeee;
			padding: .5em .6em .4em;
			color: black;
			margin-top: 0.1em;
		}
		a.github-edit > * {
			opacity: 0.6;
		}
		a.github-edit:hover > * {
			opacity: 1;
			color: black;
		}
		a.github-edit img {
			height: .8em;
		}
		.single-handbook div.table-of-contents {
			margin: 0;
			float: none;
			padding: 0;
			border: none;
			box-shadow: none;
			width: auto;
		}
		.single-handbook div.table-of-contents:after {
			content: " ";
			display: block;
			clear: both;
		}
		.single-handbook .table-of-contents h2 {
			display: none;
		}
		.single-handbook div.table-of-contents ul {
			padding: 0;
			margin-top: 0.4em;
			margin-bottom: 1.1em;
		}
		.single-handbook div.table-of-contents > ul li {
			display: inline-block;
			padding: 0;
			font-size: 12px;
		}
		.single-handbook div.table-of-contents > ul li a:after {
			content: "|";
			display: inline-block;
			width: 20px;
			text-align: center;
			color: #eeeeee
		}
		.single-handbook div.table-of-contents > ul li:last-child a:after {
			content: "";
		}
		.single-handbook div.table-of-contents ul ul {
			display: none;
		}
		.single-handbook #secondary {
			max-width: 240px;
		}
	</style>
	<?php
});
