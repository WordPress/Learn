<?php
/**
 * Sensei Block Visibility class.
 *
 * @package sensei-pro
 * @since   1.5.0
 */

namespace Sensei_Pro_Block_Visibility;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Sensei_Pro\Assets;

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
	 * Assets helper.
	 *
	 * @var Assets
	 */
	private $assets;

	/**
	 * Block visibility instance.
	 */
	public static function instance(): Block_Visibility {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize the class singleton.
	 */
	private function __construct() {
		$this->assets = new Assets();
	}

	/**
	 * Initializes the class and adds all filters and actions.
	 */
	public static function init() {
		$instance = self::instance();

		add_action( 'enqueue_block_editor_assets', [ $instance, 'enqueue_assets' ] );

		$instance->init_dependencies();

	}

	/**
	 * Enqueue assets.
	 */
	public function enqueue_assets() {
		$screen = get_current_screen();

		if ( ! in_array( $screen->id, [ 'course', 'lesson', 'site-editor' ], true ) ) {
			return;
		}

		$this->assets->enqueue( 'sensei-block-visibility-script', 'block-visibility/script.js' );
		$this->assets->enqueue( 'sensei-block-visibility-style', 'block-visibility/style.css' );

		Visibility_Options::instance()->enqueue_inline_scripts();
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
