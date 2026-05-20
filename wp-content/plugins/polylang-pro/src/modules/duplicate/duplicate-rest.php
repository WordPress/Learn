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
class PLL_Duplicate_REST extends PLL_Toggle_User_Button_REST {
	/**
	 * @var PLL_Admin_Links
	 */
	private $links;

	/**
	 * Constructor
	 *
	 * @since 2.6
	 *
	 * @param PLL_REST_Request|PLL_Admin $polylang Polylang object.
	 */
	public function __construct( &$polylang ) {
		$this->links = new PLL_Admin_Links( $polylang );

		parent::__construct( $polylang->model, new PLL_Toggle_User_Meta( PLL_Duplicate_Action::META_NAME ) );

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
		if ( empty( $block_editor_context->post ) || ! $block_editor_context->post instanceof WP_Post || empty( $block_editor_context->post->post_content ) ) {
			return $editor_settings;
		}

		$data = $this->links->get_data_from_new_post_translation_request();

		if ( empty( $data ) ) {
			return $editor_settings;
		}

		unset( $editor_settings['template'], $editor_settings['templateLock'] );

		return $editor_settings;
	}

	/**
	 * Returns the description of the schema for the current user meta.
	 *
	 * @since 3.8
	 *
	 * @return string The description of the schema.
	 */
	protected function get_schema_description(): string {
		return __( 'Whether the duplicate feature is enabled or not per post type for the current user.', 'polylang-pro' );
	}
}
