<?php
/**
 * File containing the Co_Teachers_Meta_Box class.
 *
 * @package sensei-pro
 * @since   1.9.0
 */

namespace Sensei_Pro_Co_Teachers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class initialising Co-Teachers control component.
 */
class Co_Teachers_Meta_Box {

	/**
	 * Class instance.
	 *
	 * @var Co_Teachers_Meta_Box
	 */
	private static $instance;

	/**
	 * Assets loader for JS files.
	 *
	 * @var \Sensei_Pro\Assets
	 */
	private $js_assets;

	/**
	 * Assets loader for CSS files.
	 *
	 * @var \Sensei_Pro\Assets
	 */
	private $css_assets;

	/**
	 * Main Co-Teachers instace.
	 *
	 * @var Co_Teachers
	 */
	private $co_teachers;

	/**
	 * Retrieve the Co_Teachers_Meta_Box instance.
	 */
	public static function instance(): Co_Teachers_Meta_Box {
		if ( ! self::$instance ) {
			self::$instance = new self( Co_Teachers::instance() );
		}

		return self::$instance;
	}

	/**
	 * Class constructor.
	 *
	 * @param Co_Teachers $co_teachers Main Co-Teachers class instance.
	 */
	private function __construct( Co_Teachers $co_teachers ) {
		$this->js_assets   = \Sensei_Pro\Modules\assets_loader( Co_Teachers::MODULE_NAME );
		$this->css_assets  = \Sensei_Pro\Modules\assets_loader( 'style-' . Co_Teachers::MODULE_NAME );
		$this->co_teachers = $co_teachers;
	}

	/**
	 * Initializes the class.
	 */
	public static function init() {
		$instance = self::instance();
		add_action( 'enqueue_block_editor_assets', [ $instance, 'enqueue_frontend_assets' ] );
	}

	/**
	 * Enqueue frontend assets.
	 */
	public function enqueue_frontend_assets() {
		// Only load in admin panel.
		if ( ! is_admin() ) {
			return;
		}

		// Check if we are in the course edit page.
		$screen = get_current_screen();
		if ( ! $screen || 'course' !== $screen->post_type ) {
			return;
		}

		// Check permissions for current course.
		global $post;
		if ( $post && $this->co_teachers->can_current_user_manage_coteachers() ) {
			$this->js_assets->enqueue( 'sensei-co-teachers-meta-box-script', 'admin-co-teachers-meta-box.js', [ 'wp-element', 'wp-dom-ready' ] );
			$this->css_assets->enqueue( 'sensei-co-teachers-meta-box-style', 'admin-co-teachers-meta-box.css', [] );
		}
	}
}
