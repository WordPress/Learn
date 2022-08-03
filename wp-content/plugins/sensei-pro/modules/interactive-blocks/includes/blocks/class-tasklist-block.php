<?php
/**
 * Task_List block.
 *
 * @package sensei-pro
 */

namespace Sensei_Pro_Interactive_Blocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TaskList block class.
 */
class TaskList_Block {
	/**
	 * Task_List_Block constructor.
	 */
	public function __construct() {
		register_block_type_from_metadata(
			SENSEI_IB_PLUGIN_DIR_PATH . 'assets/tasklist-block/',
			[
				'editor_script' => 'sensei-interactive-blocks-editor-script',
				'view_script'   => 'sensei-interactive-blocks-frontend-script',
				'style'         => 'sensei-interactive-blocks-styles',
			]
		);
	}
}
