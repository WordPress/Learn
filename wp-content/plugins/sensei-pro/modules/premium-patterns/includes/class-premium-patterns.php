<?php
/**
 * Sensei Premium Patterns extension.
 *
 * @package sensei-pro
 * @since   1.3.0
 */

namespace Sensei_Pro_Premium_Patterns;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sensei Premium Patterns extension main class.
 */
class Premium_Patterns {

	/**
	 * Class instance.
	 *
	 * @var Premium_Patterns
	 */
	private static $instance;

	/**
	 * Retrieve the premium patterns instance.
	 */
	public static function instance(): Premium_Patterns {
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
	 */
	public static function init() {
		$instance = self::instance();

		add_action( 'init', [ $instance, 'register_category' ] );
		add_action( 'current_screen', [ $instance, 'register_block_patterns' ] );
	}

	/**
	 * Registers premium block patterns.
	 *
	 * @param WP_Screen $current_screen Current WP_Screen object.
	 *
	 * @access private
	 */
	public function register_block_patterns( $current_screen ) {
		$post_type = $current_screen->post_type;

		if ( 'course' === $post_type ) {
			$this->register_course_block_patterns();
		} elseif ( 'lesson' === $post_type ) {
			$this->register_lesson_block_patterns();
		}
	}

	/**
	 * Registers block patterns category.
	 */
	public function register_category() {
		// Registering Sensei LMS category in case it has not been already registered.
		register_block_pattern_category(
			$this->get_patterns_category(),
			[ 'label' => __( 'Sensei LMS', 'sensei-pro' ) ]
		);
	}


	/**
	 * Register premium course block patterns.
	 */
	private function register_course_block_patterns() {
		// No premium patterns for courses yet.
	}

	/**
	 * Register premium lesson block patterns.
	 */
	private function register_lesson_block_patterns() {
		register_block_pattern(
			'sensei-lms/flashcards',
			[
				'title'         => __( 'Flashcards', 'sensei-pro' ),
				'categories'    => [ $this->get_patterns_category() ],
				'blockTypes'    => [ $this->get_post_content_block_type_name() ],
				'viewportWidth' => 800,
				'content'       => $this->load_template( __DIR__ . '/templates/lesson/flashcards.php' ),
			]
		);

		register_block_pattern(
			'sensei-lms/checklist',
			[
				'title'         => __( 'Tasklist', 'sensei-pro' ),
				'categories'    => [ $this->get_patterns_category() ],
				'blockTypes'    => [ $this->get_post_content_block_type_name() ],
				'viewportWidth' => 800,
				'content'       => $this->load_template( __DIR__ . '/templates/lesson/checklist.php' ),
			]
		);

		register_block_pattern(
			'sensei-lms/timed-quiz',
			[
				'title'         => __( 'Timed Quiz', 'sensei-pro' ),
				'categories'    => [ $this->get_patterns_category() ],
				'blockTypes'    => [ $this->get_post_content_block_type_name() ],
				'viewportWidth' => 800,
				'content'       => $this->load_template( __DIR__ . '/templates/lesson/timed-quiz.php' ),
			]
		);
	}

	/**
	 * Returns the pattern category.
	 * By default, the same one used in Sensei Core for the free patterns – with a fallback for backwards compatibility.
	 *
	 * @return string
	 */
	private function get_patterns_category() {
		return class_exists( '\Sensei_Block_Patterns' ) ? \Sensei_Block_Patterns::get_patterns_category_name() : 'sensei-lms';
	}

	/**
	 * Returns the pattern block type.
	 * By default, the same one used in Sensei Core for the free patterns – with a fallback for backwards compatibility.
	 *
	 * @return string
	 */
	private function get_post_content_block_type_name() {
		return class_exists( '\Sensei_Block_Patterns' ) ? \Sensei_Block_Patterns::get_post_content_block_type_name() : 'sensei-lms/post-content';
	}

	/**
	 * Given a file path returns the contents of rendering it.
	 *
	 * @param string $file_path File path to the template.
	 * @return string
	 */
	private function load_template( $file_path ) {
		ob_start();
		require $file_path;

		return ob_get_clean();
	}
}
