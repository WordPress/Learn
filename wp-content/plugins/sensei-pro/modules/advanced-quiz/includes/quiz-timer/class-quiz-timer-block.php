<?php
/**
 * File containing the Quiz_Timer_Block class.
 *
 * @package sensei-beograd
 * @since
 */

namespace Sensei_Pro_Advanced_Quiz;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \Sensei_Blocks;

/**
 * Displays the quiz timer in the frontend.
 */
class Quiz_Timer_Block {

	/**
	 * Quiz_Timer_Block constructor.
	 */
	public function __construct() {
		Sensei_Blocks::register_sensei_block(
			'sensei-lms/quiz-timer',
			[
				'render_callback' => [ $this, 'render' ],
			]
		);
	}

	/**
	 * Renders the block.
	 *
	 * @param array $attributes The block attributes.
	 *
	 * @access private
	 *
	 * @return string The block HTML.
	 */
	public function render( array $attributes = [] ): string {
		return Quiz_Timer::QUIZ_TIMER_HTML;
	}
}
