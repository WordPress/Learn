<?php
/**
 * File containing the class Tailored_Course_Outline.
 *
 * @package sensei-pro-tailored-course-outline
 * @since   1.16.0
 */

namespace Sensei_Pro_Tailored_Course_Outline;

use Sensei_Pro\Assets;
use function Sensei_Pro\Modules\assets_loader;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sensei Pro AI.
 *
 * @since 1.16.0
 */
class Tailored_Course_Outline {

	const MODULE_NAME = 'tailored-course-outline';

	/**
	 * Instance of class.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * JavaScript Assets helper.
	 *
	 * @var Assets
	 */
	private $js;


	/**
	 * CSS Assets helper.
	 *
	 * @var Assets
	 */
	private $css;

	/**
	 * Tailored_Course_Outline constructor. Prevents other instances from being created outside of `Tailored_Course_Outline::instance()`.
	 */
	private function __construct() {
		// 2 asset loader instances are required to load css files from js files.

		$this->js  = assets_loader( self::MODULE_NAME );
		$this->css = assets_loader( 'style-' . self::MODULE_NAME );
	}

	/**
	 *
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
	 * Initialize the module.
	 *
	 * @since 1.16.0
	 */
	public static function init() {
		$instance = self::instance();

		add_action( 'enqueue_block_editor_assets', [ $instance, 'enqueue_editor_assets' ] );
		add_action( 'init', [ $instance, 'register_post_meta' ] );
	}

	/**
	 * Register the course post meta
	 *
	 * @since 1.16.0
	 */
	public function register_post_meta() {

		register_post_meta(
			'course',
			'sensei_course_audience',
			[
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'default'       => '',
				'auth_callback' => [ $this, 'course_meta_auth_callback' ],
			]
		);

		register_post_meta(
			'course',
			'sensei_course_skill_level',
			[
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'default'       => '',
				'auth_callback' => [ $this, 'course_meta_auth_callback' ],
			]
		);
	}


	/**
	 * Post meta auth callback.
	 *
	 * @access private
	 *
	 * @param bool   $allowed True if allowed to view the meta field by default, false otherwise.
	 * @param string $meta_key Meta key.
	 * @param int    $post_id Lesson ID.
	 *
	 * @return bool Whether the user can edit the post meta.
	 */
	public function course_meta_auth_callback( $allowed, $meta_key, $post_id ) {
		return current_user_can( 'edit_post', $post_id );
	}

	/**
	 * Enqueue scripts.
	 */
	public function enqueue_editor_assets() {
		if ( ! is_admin() || ! function_exists( 'get_current_screen' ) ) {
			return;
		}

		$screen             = get_current_screen();
		$is_course_editor   = 'course' === $screen->post_type;
		$is_feature_enabled = Sensei()->feature_flags->is_enabled( 'course_outline_ai' );

		if ( $is_course_editor && $is_feature_enabled ) {
			$this->css->enqueue( 'style-tailored-course-outline', 'tailored-course-outline-plugin.css' );

			$this->js->enqueue( 'tailored-course-outline', 'tailored-course-outline-plugin.js' );
			$this->js->enqueue( 'tailored-course-outline-toolbar', 'tailored-course-outline-toolbar.js' );
		}
	}
}
