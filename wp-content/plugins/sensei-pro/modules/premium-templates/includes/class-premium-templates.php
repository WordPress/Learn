<?php
/**
 * Sensei Premium Templates extension.
 *
 * @package sensei-pro
 * @since   1.7.0
 */

namespace Sensei_Pro_Premium_Templates;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sensei Premium Templates extension main class.
 */
class Premium_Templates {

	/**
	 * Module name.
	 *
	 * @var string
	 */
	const MODULE_NAME = 'premium-templates';

	/**
	 * Class instance.
	 *
	 * @var Premium_Templates
	 */
	private static $instance;

	/**
	 * The path to the root of the premium-templates module.
	 *
	 * @var string
	 */
	private $module_path;

	/**
	 * The assets manager.
	 *
	 * @var \Sensei_Pro\Assets
	 */
	private $assets;

	/**
	 * Retrieve the premium templates instance.
	 */
	public static function instance(): Premium_Templates {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Class constructor.
	 */
	private function __construct() {
		// Silence is golden.
	}

	/**
	 * Initializes the class and adds all filters and actions.
	 *
	 * @param \Sensei_Pro\Assets $assets      The assets manager.
	 * @param string             $module_path The path to the root of the premium-templates module.
	 */
	public static function init( \Sensei_Pro\Assets $assets, string $module_path ) {
		$instance              = self::instance();
		$instance->assets      = $assets;
		$instance->module_path = $module_path;

		add_filter( 'sensei_learning_mode_block_templates', [ $instance, 'update_pro_templates' ] );
		add_action( 'sensei_course_learning_mode_load_theme', [ $instance, 'enqueue_learning_mode_pro_styles' ] );

		// register admin styles.
		add_action( 'admin_enqueue_scripts', [ $instance, 'add_editor_styles' ] );
	}

	/**
	 * Enqueue Learning Mode styles in the admin for the Site Editor and Lesson Editor.
	 */
	public function add_editor_styles() {
		if ( ! is_admin() || ! function_exists( 'get_current_screen' ) ) {
			return;
		}

		$screen           = get_current_screen();
		$is_lesson_editor = 'lesson' === $screen->post_type && 'post' === $screen->base;
		$is_site_editor   = 'site-editor' === $screen->id;

		if ( $is_lesson_editor || $is_site_editor ) {
			$this->assets->enqueue(
				'learning-mode-pro-editor-styles',
				'learning-mode-pro.editor.css'
			);
		}
	}

	/**
	 * Update the templates with the values of matching premium templates.
	 *
	 * @param \Sensei_Course_Theme_Template[] $templates The Learning Mode block templates.
	 */
	public function update_pro_templates( array $templates ): array {

		$premium_templates      = $this->get_premium_templates();
		$premium_template_names = array_keys( $premium_templates );

		foreach ( $templates as $template_name => $template ) {
			if ( in_array( $template_name, $premium_template_names, true ) ) {

				$premium_template = $premium_templates[ $template_name ];

				foreach ( $premium_template as $key => $value ) {
					$template->$key = $value;
				}

				$templates[ $template_name ] = $template;
			}
		}

		return $templates;
	}

	/**
	 * Load styles for Learning Mode.
	 *
	 * @access private
	 *
	 * @return void
	 */
	public function enqueue_learning_mode_pro_styles() {
		$this->assets->enqueue( 'sensei-pro-learning-mode', 'learning-mode-pro.css' );
	}

	/**
	 * Get the premium templates.
	 */
	public function get_premium_templates(): array {

		$file_extension = 'html';

		if ( version_compare( '4.19.1', Sensei()->version ) < 0 ) {
			$file_extension = 'php';
		}
		return [
			'modern'     =>
				[
					'content' => [
						'lesson' => "{$this->module_path}/templates/modern/lesson." . $file_extension,
						'quiz'   => '',
					],
					'styles'  => [],
					'upsell'  => false,
				],

			'video'      =>
				[
					'content' => [
						'lesson' => "{$this->module_path}/templates/video/lesson." . $file_extension,
						'quiz'   => '',
					],
					'styles'  => [],
					'upsell'  => false,
				],

			'video-full' =>
				[
					'content' => [
						'lesson' => "{$this->module_path}/templates/video-full/lesson." . $file_extension,
						'quiz'   => '',
					],
					'styles'  => [],
					'upsell'  => false,
				],
		];
	}
}
