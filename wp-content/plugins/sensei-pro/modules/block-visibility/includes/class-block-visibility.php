<?php
/**
 * Sensei Block Visibility class.
 *
 * @package sensei-pro
 * @since 1.5.0
 */

namespace Sensei_Pro_Block_Visibility;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_Block_Type_Registry;

/**
 * Sensei Block Visibility class.
 */
class Block_Visibility {
	/**
	 * Class instance.
	 *
	 * @var Block_Visibility
	 */
	private static $instance;

	/**
	 * Block visibility instance.
	 */
	public static function instance() : Block_Visibility {
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
	 */
	public static function init() {
		$instance = self::instance();

		add_action( 'init', [ $instance, 'enqueue_assets' ] );

		$instance->init_dependencies();

	}

	/**
	 * Enqueue assets.
	 */
	public function enqueue_assets() {
		$assets_url = SENSEI_PRO_PLUGIN_DIR_URL . 'assets/dist/block-visibility';
		wp_enqueue_script( 'sensei-block-visibility-script', "{$assets_url}/script.js", [ 'wp-components', 'wp-block-editor', 'wp-plugins', 'wp-edit-post' ], SENSEI_PRO_VERSION, false );
		wp_enqueue_style( 'sensei-block-visibility-style', "{$assets_url}/style.css", [ 'wp-components', 'wp-block-editor' ], SENSEI_PRO_VERSION, false );
	}

	/**
	 * Include required classes and initiate them.
	 */
	private function init_dependencies() {
		include_once __DIR__ . '/class-visibility-options.php';
		include_once __DIR__ . '/class-visibility-enforcer.php';

		Visibility_Enforcer::init( Visibility_Options::init() );
	}
}
