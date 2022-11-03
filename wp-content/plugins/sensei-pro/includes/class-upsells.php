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
 * @class Upsells
 */
final class Upsells {
	/**
	 * Instance of class.
	 *
	 * @var Upsells
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
		add_filter( 'sensei_student_groups_hide', '__return_true' );
		// Sensei Home: Disable promotional banner.
		add_filter( 'sensei_home_promo_banner_show', '__return_false' );
		// Sensei Home: Disable upsell CTA for "Create support ticket" in help section.
		add_filter( 'sensei_home_support_ticket_creation_upsell_show', '__return_false' );
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
