<?php
/**
 * File containing the class Advanced_Quiz_Block_Initializer.
 *
 * @package sensei-beograd
 */

namespace Sensei_Pro_Advanced_Quiz;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Sensei_Blocks_Initializer;

/**
 * Class Advanced_Quiz_Block_Initializer
 */
class Advanced_Quiz_Block_Initializer extends Sensei_Blocks_Initializer {
	/**
	 * Course_Theme constructor.
	 */
	public function __construct() {
		parent::__construct( null );
	}

	/**
	 * Initializes the blocks.
	 */
	public function initialize_blocks() {
		new Quiz_Timer_Block();
	}

	/**
	 * Enqueue frontend and editor assets.
	 *
	 * @access private
	 */
	public function enqueue_block_assets() {
	}

	/**
	 * Enqueue editor assets.
	 *
	 * @access private
	 */
	public function enqueue_block_editor_assets() {
	}
}
