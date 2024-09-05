<?php
/**
 * Question block.
 *
 * @package sensei-pro
 */

namespace Sensei_Pro_Interactive_Blocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Question block class.
 */
class Question_Block {
	/**
	 * Question_Block constructor.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'init' ] );
	}

	/**
	 * Initialize block.
	 */
	public function init() {
		register_block_type_from_metadata(
			SENSEI_IB_PLUGIN_DIR_PATH . 'assets/question/question-block/',
			[
				'editor_script' => 'sensei-interactive-blocks-editor-script',
				'style'         => 'sensei-interactive-blocks-styles',
				'view_script'   => 'sensei-interactive-blocks-frontend-script',
			]
		);
	}
}
