<?php
/**
 * Flashcards block.
 *
 * @package sensei-pro
 */

namespace Sensei_Pro_Interactive_Blocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Flashcard block class.
 */
class Flashcard_Block {
	/**
	 * Flashcard_Block constructor.
	 */
	public function __construct() {
		register_block_type_from_metadata(
			SENSEI_IB_PLUGIN_DIR_PATH . 'assets/flashcard-block/',
			[
				'editor_script' => 'sensei-interactive-blocks-editor-script',
				'view_script'   => 'sensei-interactive-blocks-frontend-script',
				'style'         => 'sensei-interactive-blocks-styles',
			]
		);
	}
}
