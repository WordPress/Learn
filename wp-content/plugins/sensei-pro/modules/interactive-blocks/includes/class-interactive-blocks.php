<?php
/**
 * Sensei Interactive Blocks extension.
 *
 * @package sensei-pro
 */

namespace Sensei_Pro_Interactive_Blocks;

use Sensei_Interactive_Blocks_Sensei_Home\Sensei_Home;
use Sensei_Interactive_Blocks_Sensei_Home\Sensei_LMS_Home;
use Sensei_Pro_Interactive_Blocks\Tutor_Chat\Tutor_Chat_Rest_Api;
use Sensei_Pro_Interactive_Blocks\Tutor_Chat\Tutor_Chat_Service;
use WP_HTML_Tag_Processor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sensei Interactive Blocks extension main class.
 */
class Interactive_Blocks {
	const MODULE_NAME = 'interactive-blocks';

	/**
	 * Tutor Chat REST API.
	 *
	 * @var Tutor_Chat_Rest_Api
	 */
	public $tutor_chat_rest_api;

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
	public static function instance(): Interactive_Blocks {
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
		$instance                      = self::instance();
		$instance->assets_provider     = $assets_provider;
		$instance->tutor_chat_rest_api = new Tutor_Chat_Rest_Api( new Tutor_Chat_Service() );

		$instance->include_dependencies();
		$instance->init_blocks();

		add_action( 'init', [ $instance, 'register_assets' ] );
		add_action( 'wp_enqueue_scripts', [ $instance, 'enqueue_frontend_assets' ] );
		add_action( 'rest_api_init', [ $instance, 'init_rest_api_endpoints' ] );

		// Add the required attribute to the Videopress block.
		add_action( 'render_block', [ $instance, 'add_required_attribute_to_videopress_block' ], 10, 2 );

		// Add Sensei LMS blocks category.
		if ( is_wp_version_compatible( '5.8' ) ) {
			add_filter( 'block_categories_all', [ $instance, 'sensei_block_categories' ] );
		} else {
			add_filter( 'block_categories', [ $instance, 'sensei_block_categories' ] );
		}

		add_action( 'plugins_loaded', [ $instance, 'load_sensei_home' ], 100 );
	}

	/**
	 * Load Sensei Home.
	 *
	 * @internal
	 */
	public function load_sensei_home() {
		if ( class_exists( 'Sensei_Home' ) && class_exists( 'Sensei_Interactive_Blocks_Sensei_Home\Sensei_LMS_Home' ) ) {
			// Sensei LMS is active. We just need to integrate with it.
			Sensei_LMS_Home::instance()->init();

			return;
		}

		if ( ! class_exists( 'Sensei_Home' ) && class_exists( 'Sensei_Interactive_Blocks_Sensei_Home\Sensei_Home' ) ) {
			$assets_provider = new Assets_Provider(
				SENSEI_IB_PLUGIN_DIR_URL,
				SENSEI_IB_PLUGIN_DIR_PATH,
				SENSEI_IB_VERSION,
				'sensei-pro',
				Sensei_Home::MODULE_NAME
			);
			( new Sensei_Home( $assets_provider ) )->init();
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
		new Interactive_Video_Block();
		new Accordion_Block();
		new Tutor_AI_Block();
	}

	/**
	 * Register assets.
	 */
	public function register_assets() {
		$this->assets_provider->register( 'sensei-interactive-blocks-editor-script', 'interactive-blocks-editor.js' );

		$this->assets_provider->register( 'interactive-blocks-tutor-ai', 'interactive-blocks-tutor-ai.js', [ 'sensei-interactive-blocks-editor-script' ] );

		$this->maybe_register_video_apis();

		// YouTube and Vimeo APIs are used only by Interactive Video Block. When we support array
		// in the `view_script` while registering the block, we can migrate these dependencies to
		// there.
		$this->assets_provider->register( 'sensei-interactive-blocks-frontend-script', 'interactive-blocks-frontend.js', [ 'sensei-youtube-iframe-api', 'sensei-vimeo-iframe-api' ], true );
		$this->assets_provider->register(
			'sensei-interactive-blocks-styles',
			'interactive-blocks-styles.css',
			[ 'sensei-pro-shared-module-sensei-fontawesome', 'wp-components' ]
		);
		$this->enqueue_inline_script();
	}

	/**
	 * It registers the video API scripts, in case it wasn't registered yet - Sensei LMS registers
	 * it, but it might not be activated if the user is using only Sensei Blocks.
	 */
	private function maybe_register_video_apis() {
		if ( ! wp_script_is( 'sensei-youtube-iframe-api', 'registered' ) || ! wp_script_is( 'sensei-vimeo-iframe-api', 'registered' ) ) {
			wp_register_script( 'sensei-youtube-iframe-api', 'https://www.youtube.com/iframe_api', [], 'unversioned', false );
			wp_register_script( 'sensei-vimeo-iframe-api', 'https://player.vimeo.com/api/player.js', [], 'unversioned', false );

			wp_add_inline_script(
				'sensei-youtube-iframe-api',
				'window.senseiYouTubeIframeAPIReady = new Promise( ( resolve ) => {
					const previousYouTubeIframeAPIReady =
						window.onYouTubeIframeAPIReady !== undefined
							? window.onYouTubeIframeAPIReady
							: () => {};
					window.onYouTubeIframeAPIReady = () => {
						resolve();
						previousYouTubeIframeAPIReady();
					};
				} )',
				'before'
			);
		}
	}

	/**
	 * Enqueue frontend assets.
	 */
	public function enqueue_frontend_assets() {
		$post = get_post();

		if ( ! $post ) {
			return;
		}

		wp_add_inline_script( 'sensei-interactive-blocks-frontend-script', "window.sensei = window.sensei || {};  window.sensei.postId = '$post->ID';", 'before' );

		$this->maybe_enqueue_lesson_video_assets();
	}

	/**
	 * Enqueue assets for video blocks on lessons because that assets contain the Required Videos feature
	 * and it's not enqueued through the block registration as the other Sensei Interactive Blocks.
	 */
	private function maybe_enqueue_lesson_video_assets() {
		$post            = get_post();
		$video_blocks    = [ 'core/video', 'core/embed' ];
		$has_video_block = false;

		if ( 'lesson' !== $post->post_type ) {
			return;
		}

		foreach ( $video_blocks as $block ) {
			$has_video_block |= has_block( $block, $post );
		}

		if ( $has_video_block ) {
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

		wp_add_inline_script( 'sensei-interactive-blocks-frontend-script', 'window.senseiProIsUserLoggedIn=' . wp_json_encode( is_user_logged_in() ) . ';', 'before' );
	}

	/**
	 * Include required files.
	 */
	private function include_dependencies() {
		include_once __DIR__ . '/blocks/class-accordion-block.php';
		include_once __DIR__ . '/blocks/class-question-block.php';
		include_once __DIR__ . '/blocks/class-flashcard-block.php';
		include_once __DIR__ . '/blocks/class-hotspots-block.php';
		include_once __DIR__ . '/blocks/class-tasklist-block.php';
		include_once __DIR__ . '/blocks/class-tutor-ai-block.php';
		include_once __DIR__ . '/blocks/class-interactive-video-block.php';
	}

	/**
	 * Add Sensei LMS block category.
	 *
	 * @access private
	 *
	 * @param array $categories Current categories.
	 *
	 * @return array Filtered categories.
	 */
	public function sensei_block_categories( $categories ) {
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

	/**
	 * Initialize REST API endpoints.
	 */
	public function init_rest_api_endpoints() {
		$this->tutor_chat_rest_api->register_routes();
	}

	/**
	 * Add the required attribute to the videopress block.
	 * For other blocks, it's happening on the client.
	 *
	 * @param string $block_content The block content.
	 * @param array  $block         The block data.
	 *
	 * @return string The updated block content.
	 */
	public function add_required_attribute_to_videopress_block( $block_content, $block ) {
		// Check if it's the videopress/video block.
		if ( 'videopress/video' !== $block['blockName'] ) {
			return $block_content;
		}

		$processor = new WP_HTML_Tag_Processor( $block_content );

		// Check if the first tag exists.
		if ( $processor->next_tag() ) {
			// Set required attribute to the element.
			$is_required = boolval( ( isset( $block['attrs']['required'] ) && $block['attrs']['required'] ) );
			if ( $is_required ) {
				$processor->set_attribute( 'data-sensei-is-required', 'true' );
			} else {
				$processor->set_attribute( 'data-sensei-is-not-required', 'true' );
			}
		}

		return $processor->get_updated_html();
	}
}
