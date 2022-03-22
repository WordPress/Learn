<?php
/**
 * File containing the class \Sensei_Pro\Upsells class.
 *
 * @package sensei-pro
 * @since   1.0.0
 */

namespace Sensei_Pro;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for removing Sensei upsell nudges.
 *
 * @class Sensei_Pro
 */
final class Upsells {
	/**
	 * Instance of class.
	 *
	 * @var Sensei_Pro
	 */
	private static $instance;

	/**
	 * Initialize the singleton instance.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->assets = new Assets();
	}

	/**
	 * Fetches an instance of the class.
	 *
	 * @return self
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initializes the class and hooks.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		$this->add_php_hooks();

		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_js_hooks' ] );
	}

	/**
	 * Adds PHP hooks for removing upsell UI's.
	 *
	 * @since 1.0.0
	 */
	private function add_php_hooks() {
		add_filter( 'sensei_settings_woocommerce_hide', '__return_true' );
		add_filter( 'sensei_settings_content_drip_hide', '__return_true' );
		add_filter( 'sensei_lesson_content_drip_hide', '__return_true' );
		add_filter( 'sensei_quiz_ordering_question_type_hide', '__return_true' );
	}

	/**
	 * Enqueues JS hooks for removing upsell UI's.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_js_hooks() {
		$this->assets->enqueue( 'sensei-pro-upsell-hooks', 'upsell-hooks.js' );
	}
}
