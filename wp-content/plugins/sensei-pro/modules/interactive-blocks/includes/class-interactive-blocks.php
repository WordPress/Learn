<?php
/**
 * Sensei Interactive Blocks extension.
 *
 * @package sensei-pro
 */

namespace Sensei_Pro_Interactive_Blocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sensei Interactive Blocks extension main class.
 */
class Interactive_Blocks {
	const MODULE_NAME = 'interactive-blocks';

	/**
	 * Script and stylesheet loading.
	 *
	 * @var Assets_Provider
	 */
	private $assets_provider;

	/**
	 * Class instance.
	 *
	 * @var Interactive_Blocks
	 */
	private static $instance;

	/**
	 * Interactive blocks instance.
	 */
	public static function instance() : Interactive_Blocks {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize the class singleton.
	 */
	private function __construct() {}

	/**
	 * Initializes the class and adds all filters and actions.
	 *
	 * @param Assets_Provider $assets_provider The assets' provider.
	 */
	public static function init( Assets_Provider $assets_provider ) {
		$instance                  = self::instance();
		$instance->assets_provider = $assets_provider;

		$instance->include_dependencies();
		$instance->init_blocks();

		add_action( 'init', [ $instance, 'register_assets' ] );
		add_action( 'wp_enqueue_scripts', [ $instance, 'enqueue_frontend_assets' ] );

		// Add Sensei LMS blocks category.
		if ( is_wp_version_compatible( '5.8' ) ) {
			add_filter( 'block_categories_all', [ $instance, 'sensei_block_categories' ], 10, 2 );
		} else {
			add_filter( 'block_categories', [ $instance, 'sensei_block_categories' ], 10, 2 );
		}
	}

	/**
	 * Initialize blocks.
	 */
	private function init_blocks() {
		new Question_Block();
		new Flashcard_Block();
		new Hotspots_Block();
		new TaskList_Block();
	}

	/**
	 * Register assets.
	 */
	public function register_assets() {
		$this->assets_provider->register( 'sensei-interactive-blocks-editor-script', 'interactive-blocks-editor.js' );
		$this->assets_provider->register( 'sensei-interactive-blocks-frontend-script', 'interactive-blocks-frontend.js' );
		$this->assets_provider->register(
			'sensei-interactive-blocks-styles',
			'interactive-blocks-styles.css',
			[ 'sensei-pro-shared-module-sensei-fontawesome', 'wp-components' ]
		);
		$this->enqueue_inline_script();
	}

	/**
	 * Enqueue frontend assets - It's enqueued by default as part of the `view_script` arg
	 * when registering the blocks. It's a workaround for WP 5.7.
	 */
	public function enqueue_frontend_assets() {

		$blocks        = [ 'sensei-pro/question', 'sensei-pro/flashcard', 'sensei-pro/hotspots', 'sensei-pro/task-list', 'core/video', 'core/embed' ];
		$has_any_block = false;
		$post          = get_post();

		foreach ( $blocks as $block ) {
			$has_any_block |= has_block( $block, $post );
		}

		if ( $has_any_block ) {
			$post_id = get_post()->ID;
			wp_add_inline_script( 'sensei-interactive-blocks-frontend-script', "window.sensei = window.sensei || {};  window.sensei.postId = '$post_id';", 'before' );
			$this->assets_provider->enqueue_style( 'sensei-interactive-blocks-styles' );
			$this->assets_provider->enqueue_script( 'sensei-interactive-blocks-frontend-script' );
		}
	}

	/**
	 * Adds inline script.
	 * Currently only tells if the Required Blocks feature
	 * should be enabled or not.
	 */
	public function enqueue_inline_script() {
		$supports_required = 'false';
		if ( defined( 'SENSEI_PRO_PLUGIN_FILE' ) ) {
			$supports_required = 'true';
		}
		$script = "window.sensei = window.sensei || {}; window.sensei.supportsRequired=$supports_required;";
		wp_add_inline_script(
			'sensei-interactive-blocks-editor-script',
			$script,
			'before'
		);
		wp_add_inline_script(
			'sensei-interactive-blocks-frontend-script',
			$script,
			'before'
		);
	}

	/**
	 * Include required files.
	 */
	private function include_dependencies() {
		include_once __DIR__ . '/blocks/class-question-block.php';
		include_once __DIR__ . '/blocks/class-flashcard-block.php';
		include_once __DIR__ . '/blocks/class-hotspots-block.php';
		include_once __DIR__ . '/blocks/class-tasklist-block.php';
	}

	/**
	 * Add Sensei LMS block category.
	 *
	 * @access private
	 *
	 * @param array                             $categories Current categories.
	 * @param \WP_Post|\WP_Block_Editor_Context $context    Either the WP Post (pre-WP 5.8) or the context object.
	 *
	 * @return array Filtered categories.
	 */
	public function sensei_block_categories( $categories, $context ) {
		$category_name = 'sensei-lms';

		// Get the sensei categories.
		$sensei_categories = array_filter(
			$categories,
			function ( $category ) use ( $category_name ) {
				return $category['slug'] === $category_name;
			}
		);

		// If the category already present then return as is.
		if ( count( $sensei_categories ) ) {
			return $categories;
		}

		// Merge the sensei-lms category with the rest.
		return array_merge(
			[
				[
					'slug'  => $category_name,
					'title' => __( 'Sensei LMS', 'sensei-pro' ),
				],
			],
			$categories
		);
	}
}
