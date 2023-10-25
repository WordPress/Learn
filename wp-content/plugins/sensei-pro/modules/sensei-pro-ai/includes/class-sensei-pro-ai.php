<?php
/**
 * File containing the classSensei_Pro_AI.
 *
 * @package sensei-pro-ai
 * @since   1.14.0
 */

namespace Sensei_Pro_AI;

use Sensei_Pro\AI_API_Client;
use Sensei_Pro\Assets;
use Sensei_Pro_AI\Rest_Api\Controllers\Chat_GPT_Controller;
use Sensei_Pro_AI\Rest_Api\Controllers\Course_Outline_Controller;
use Sensei_Pro_AI\Services\Course_Outline_Service;
use Sensei_Pro_AI\Services\Question_Generator_Service;
use function Sensei_Pro\Modules\assets_loader;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sensei Pro AI.
 *
 * @since 1.14.0
 */
class Sensei_Pro_AI {

	const MODULE_NAME = 'sensei-pro-ai';

	/**
	 * User meta key to indicate if the user has already used quiz generation AI.
	 *
	 * @var string
	 */
	const USER_QUESTION_AI_USED_META_KEY = 'sensei_pro_question_ai_used';

	/**
	 * Course Outline REST API Controller.
	 *
	 * @var Course_Outline_Controller
	 */
	public $course_outline_controller;

	/**
	 * Chat GPT REST API Controller.
	 *
	 * @var Chat_GPT_Controller
	 */
	public $chat_gpt_controller;

	/**
	 * Instance of class.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Assets helper.
	 *
	 * @var Assets
	 */
	private $assets;

	/**
	 * Sensei_Pro_AI constructor. Prevents other instances from being created outside of `Sensei_Pro_AI::instance()`.
	 */
	private function __construct() {
		$this->assets = assets_loader( self::MODULE_NAME );
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
	 * @since 1.14.0
	 */
	public static function init() {
		$instance = self::instance();

		$instance->chat_gpt_controller       = new Chat_GPT_Controller( new Question_Generator_Service() );
		$instance->course_outline_controller = new Course_Outline_Controller( new Course_Outline_Service( new AI_API_Client() ) );

		add_action( 'enqueue_block_editor_assets', [ $instance, 'enqueue_editor_assets' ] );

		// Set up REST API endpoints.
		add_action( 'rest_api_init', [ $instance, 'init_rest_api_endpoints' ], 1 );

		// Disable upsell.
		add_filter( 'sensei_ai_quiz_generation_available', '__return_true' );

		add_action( 'init', [ $instance, 'register_user_meta_field' ] );
	}

	/**
	 * Initialize REST API endpoints.
	 */
	public function init_rest_api_endpoints() {
		$this->chat_gpt_controller->register_routes();
		$this->course_outline_controller->register_routes();
	}

	/**
	 * Enqueue scripts.
	 */
	public function enqueue_editor_assets() {
		$screen = get_current_screen();

		if ( ! in_array( $screen->id, [ 'lesson' ], true ) ) {
			return;
		}

		$this->assets->enqueue( 'sensei-pro-ai-script', 'js/sensei-gpt.js' );
		$this->assets->enqueue( 'sensei-pro-ai-styles', 'css/sensei-gpt.css' );
	}

	/**
	 * Register user meta field for indicating question generation AI first use.
	 *
	 * @since 1.14.0
	 *
	 * @access private
	 *
	 * @return void
	 */
	public function register_user_meta_field() {
		register_meta(
			'user',
			self::USER_QUESTION_AI_USED_META_KEY,
			[
				'type'         => 'boolean',
				'single'       => true,
				'show_in_rest' => true,
				'default'      => false,
			]
		);
	}
}
