<?php
/**
 * @package Polylang-Pro
 */

defined( 'ABSPATH' ) || exit;

/**
 * Copy the title, content and excerpt from the source when creating a new post translation
 * in the block editor.
 * Exposes the pll_duplicate_content user meta in the REST API.
 *
 * @since 2.6
 */
class PLL_Duplicate_REST {
	/**
	 * Used to manage user meta.
	 *
	 * @var PLL_Toggle_User_Meta
	 */
	protected $user_meta;

	/**
	 * Constructor
	 *
	 * @since 2.6
	 */
	public function __construct() {
		$this->user_meta = new PLL_Toggle_User_Meta( PLL_Duplicate_Action::META_NAME );

		register_rest_field(
			'user',
			$this->user_meta->get_meta_name(),
			array(
				'get_callback'    => array( $this->user_meta, 'get' ),
				'update_callback' => array( $this->user_meta, 'update' ),
			)
		);

		add_filter( 'block_editor_settings_all', array( $this, 'remove_template' ), 10, 2 );
	}

	/**
	 * Avoids that the post template overwrites our duplicated content.
	 *
	 * @since 3.2
	 *
	 * @param array                   $editor_settings      Default editor settings.
	 * @param WP_Block_Editor_Context $block_editor_context The current block editor context.
	 * @return array
	 */
	public function remove_template( $editor_settings, $block_editor_context ) {
		if (
			isset( $block_editor_context->post ) &&
			$block_editor_context->post instanceof WP_Post &&
			! empty( $block_editor_context->post->post_content ) &&
			'post-new.php' === $GLOBALS['pagenow'] &&
			isset( $_GET['from_post'], $_GET['new_lang'], $_GET['_wpnonce'] ) &&
			wp_verify_nonce( $_GET['_wpnonce'], 'new-post-translation' )
		) {
			unset( $editor_settings['template'], $editor_settings['templateLock'] );
		}
		return $editor_settings;
	}
}
