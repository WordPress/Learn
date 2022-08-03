<?php
/**
 * Image Hotspots block.
 *
 * @package sensei-pro
 */

namespace Sensei_Pro_Interactive_Blocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hotspots block class.
 */
class Hotspots_Block {
	/**
	 * Question_Block constructor.
	 */
	public function __construct() {
		register_block_type_from_metadata(
			SENSEI_IB_PLUGIN_DIR_PATH . 'assets/hotspots-block/',
			[
				'editor_script' => 'sensei-interactive-blocks-editor-script',
				'view_script'   => 'sensei-interactive-blocks-frontend-script',
				'style'         => 'sensei-interactive-blocks-styles',
			]
		);
	}
}
