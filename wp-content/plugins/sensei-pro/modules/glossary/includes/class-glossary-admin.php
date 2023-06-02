<?php
/**
 * File containing the class \Sensei_Pro_Glossary\Glossary_Admin.
 *
 * @package sensei-pro-glossary
 * @since   1.11.0
 */

namespace Sensei_Pro_Glossary;

use WP_Block_Editor_Context;
use WP_Post;

/**
 * The class responsible for handling the glossary in the admin panel.
 *
 * @internal
 */
class Glossary_Admin {
	/**
	 * The post type key.
	 */
	public const POST_TYPE = 'sensei_glossary';

	/**
	 * The allowed Gutenberg block types for the glossary post type.
	 */
	public const ALLOWED_BLOCK_TYPES = [
		/* Text blocks: */
		'core/paragraph',
		'core/heading',
		'core/list',
		'core/list-item',
		'core/quote',
		'core/pullquote',
		'core/code',
		'core/column',
		'core/freeform',
		'core/preformatted',
		'core/table',
		'core/verse',
		/* Media blocks: */
		'core/audio',
		'core/cover',
		'core/file',
		'core/gallery',
		'core/image',
		'core/video',
		'core/embed',
	];

	/**
	 * Initialize the class and add hooks.
	 *
	 * @internal
	 */
	public function init(): void {
		add_action( 'init', [ $this, 'register_post_type' ] );
		add_action( 'admin_menu', [ $this, 'add_menu_item' ] );
		add_action( 'admin_head', [ $this, 'highlight_menu_item' ] );
		add_filter( 'enter_title_here', [ $this, 'set_editor_title_placeholder' ], 10, 2 );
		add_filter( 'write_your_story', [ $this, 'set_editor_body_placeholder' ], 10, 2 );
		add_filter( 'allowed_block_types_all', [ $this, 'allowed_block_types' ], 10, 2 );
	}

	/**
	 * Register the Glossary post type.
	 *
	 * @internal
	 */
	public function register_post_type(): void {
		if ( post_type_exists( self::POST_TYPE ) ) {
			return;
		}

		register_post_type(
			self::POST_TYPE,
			[
				'labels'       => [
					'name'               => __( 'Glossary', 'sensei-pro' ),
					'singular_name'      => __( 'Entry', 'sensei-pro' ),
					'add_new'            => __( 'Add New', 'sensei-pro' ),
					'add_new_item'       => __( 'Add New Entry', 'sensei-pro' ),
					'edit_item'          => __( 'Edit Entry', 'sensei-pro' ),
					'new_item'           => __( 'New Entry', 'sensei-pro' ),
					'view_item'          => __( 'View Entry', 'sensei-pro' ),
					'search_items'       => __( 'Search Glossary', 'sensei-pro' ),
					'not_found'          => __( 'No entries found.', 'sensei-pro' ),
					'not_found_in_trash' => __( 'No entries found in trash.', 'sensei-pro' ),
					'parent_item_colon'  => __( 'Parent Entry:', 'sensei-pro' ),
					'menu_name'          => __( 'Glossary', 'sensei-pro' ),
					'name_admin_bar'     => __( 'Glossary Entry', 'sensei-pro' ),
				],
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => false,
				'show_in_rest' => true, // Enables the Gutenberg editor.
				'hierarchical' => false,
				'rewrite'      => false,
				'supports'     => [ 'title', 'editor', 'revisions' ],
			]
		);
	}

	/**
	 * Adds the glossary to admin menu.
	 *
	 * @internal
	 */
	public function add_menu_item(): void {
		add_submenu_page(
			'sensei',
			__( 'Glossary', 'sensei-pro' ),
			__( 'Glossary', 'sensei-pro' ),
			'edit_courses',
			'edit.php?post_type=' . self::POST_TYPE,
			null,
			5
		);
	}

	/**
	 * Highlight the proper menu item when editing a glossary post.
	 *
	 * @internal
	 */
	public function highlight_menu_item(): void {
		global $parent_file, $submenu_file;

		$screen = get_current_screen();

		if ( $screen && self::POST_TYPE === $screen->id ) {
			$parent_file  = 'sensei'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride -- Intended override used to fix the menu highlight.
			$submenu_file = 'edit.php?post_type=' . self::POST_TYPE; // phpcs:ignore WordPress.WP.GlobalVariablesOverride -- Intended override used to fix the menu highlight.
		}
	}

	/**
	 * Set the editor title placeholder text.
	 *
	 * @internal
	 *
	 * @param string  $placeholder
	 * @param WP_Post $post
	 *
	 * @return string
	 */
	public function set_editor_title_placeholder( string $placeholder, WP_Post $post ): string {
		if ( self::POST_TYPE === $post->post_type ) {
			$placeholder = __( 'Key Word', 'sensei-pro' );
		}

		return $placeholder;
	}

	/**
	 * Set the editor body placeholder text.
	 *
	 * @internal
	 *
	 * @param string  $placeholder
	 * @param WP_Post $post
	 *
	 * @return string
	 */
	public function set_editor_body_placeholder( string $placeholder, WP_Post $post ): string {
		if ( self::POST_TYPE === $post->post_type ) {
			$placeholder = __( 'Enter the description or type / to choose a block', 'sensei-pro' );
		}

		return $placeholder;
	}

	/**
	 * Limit the allowed block types for the glossary post type.
	 *
	 * @internal
	 *
	 * @param bool|string[]           $allowed_block_types
	 * @param WP_Block_Editor_Context $editor_context
	 *
	 * @return bool|array
	 */
	public function allowed_block_types( $allowed_block_types, $editor_context ) {
		if (
			$editor_context instanceof WP_Block_Editor_Context
			&& ! empty( $editor_context->post )
			&& self::POST_TYPE === $editor_context->post->post_type
		) {
			return self::ALLOWED_BLOCK_TYPES;
		}

		return $allowed_block_types;
	}
}
