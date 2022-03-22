<?php
/**
 * Sensei Advanced Quiz extension.
 *
 * @package sensei-pro
 */

namespace Sensei_Pro_Advanced_Quiz;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Sensei_Pro_Advanced_Quiz\Advanced_Questions\Ordering_Question_Type;
use Sensei_Assets;

/**
 * Sensei Advanced Quiz extension main class.
 */
class Advanced_Quiz {
	const MODULE_NAME = 'advanced-quiz';
	/**
	 * Script and stylesheet loading.
	 *
	 * @var Sensei_Assets
	 */
	private $assets;

	/**
	 * Quiz timer instance variable.
	 *
	 * @var Quiz_Timer
	 */
	private $timer;

	/**
	 * Class instance.
	 *
	 * @var Advanced_Quiz
	 */
	private static $instance;

	/**
	 * Module directory.
	 *
	 * @var string
	 */
	private $advanced_quiz_dir;

	/**
	 * Initialize ordering question.
	 */
	public static function instance() : Advanced_Quiz {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize the class singleton.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->advanced_quiz_dir = dirname( __DIR__ );
	}

	/**
	 * Initializes the class and adds all filters and actions.
	 *
	 * @since 1.0.0
	 */
	public static function init() {
		$instance = self::instance();

		$instance->include_dependencies();

		new Ordering_Question_Type( self::MODULE_NAME );
		new Advanced_Quiz_Block_Initializer();

		$instance->timer  = new Quiz_Timer( self::MODULE_NAME );
		$instance->assets = \Sensei_Pro\Modules\assets_loader( self::MODULE_NAME );

		add_action( 'enqueue_block_editor_assets', [ $instance, 'enqueue_block_editor_assets' ] );
		add_action( 'wp_enqueue_scripts', [ $instance, 'enqueue_frontend_assets' ] );
	}

	/**
	 * Enqueue block assets.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_block_editor_assets() {
		$this->assets->enqueue( 'sensei-advanced-quiz-timer', 'blocks/quiz-timer.js' );
		$this->assets->enqueue( 'sensei-advanced-quiz-blocks', 'blocks/blocks.css' );
	}

	/**
	 * Enqueue frontend assets.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_frontend_assets() {

		if ( ! is_single() || get_post_type() !== 'quiz' ) {
			return;
		}

		$this->assets->enqueue( 'sensei-advanced-quiz-frontend-style', 'advanced-quiz-frontend.css' );
	}

	/**
	 * Include required files.
	 */
	private function include_dependencies() {
		include_once __DIR__ . '/advanced-questions/class-ordering-question-rest-extensions.php';
		include_once __DIR__ . '/advanced-questions/class-ordering-question-grader.php';
		require_once __DIR__ . '/advanced-questions/class-ordering-question-type.php';
		include_once __DIR__ . '/quiz-timer/class-quiz-timer-block.php';
		require_once __DIR__ . '/class-advanced-quiz-block-initializer.php';
		include_once __DIR__ . '/quiz-timer/class-quiz-timer.php';
	}
}
