<?php
/**
 * File containing the class \Sensei_WC_Paid_Courses\Frontend\Shortcodes.
 *
 * @package sensei-wc-paid-courses
 * @since   1.0.0
 */

namespace Sensei_WC_Paid_Courses\Frontend;

use Sensei_WC_Paid_Courses\Frontend\Shortcodes\Unpurchased_Courses;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for frontend functionality related to shortcodes.
 *
 * @class Sensei_WC_Paid_Courses\Frontend\Shortcodes
 */
final class Shortcodes {
	/**
	 * Instance of class.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Shortcodes constructor. Prevents other instances from being created outside of `Shortcodes::instance()`.
	 */
	private function __construct() {}

	/**
	 * Initializes the class and adds all filters and actions related to the frontend.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		add_filter( 'sensei_shortcode_classes', [ $this, 'add_shortcodes' ] );
	}

	/**
	 * Add the shortcodes to Sensei's shortcode handler.
	 *
	 * @since 1.0.0
	 *
	 * @param array $shortcode_classes {
	 *     Array of shortcodes and their handler classes.
	 *
	 *     @type string ${$shortcode_slug}  Shortcode class name that implements Sensei_Shortcode_Interface.
	 * }
	 * @return array
	 */
	public function add_shortcodes( $shortcode_classes ) {
		$this->include_dependencies();
		$shortcode_classes['sensei_unpurchased_courses'] = Unpurchased_Courses::class;
		return $shortcode_classes;
	}

	/**
	 * Loads shortcode files. This fires on `init` when `Sensei_Shortcode_Interface` interface is ready.
	 */
	private function include_dependencies() {
		include_once __DIR__ . '/shortcodes/class-unpurchased-courses.php';
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
}
