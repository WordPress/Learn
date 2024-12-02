<?php
/**
 * @package Polylang-Pro
 */

defined( 'ABSPATH' ) || exit;

/**
 * Copy the title, content and excerpt from the source when creating a new post translation
 * in the classic editor.
 *
 * @since 1.9
 */
class PLL_Duplicate extends PLL_Metabox_User_Button {
	/**
	 * Used to manage user meta.
	 *
	 * @var PLL_Toggle_User_Meta
	 */
	protected $user_meta;

	/**
	 * Constructor
	 *
	 * @since 1.9
	 */
	public function __construct() {
		$this->user_meta = new PLL_Toggle_User_Meta( PLL_Duplicate_Action::META_NAME );

		$args = array(
			'position'   => 'before_post_translations',
			'activate'   => __( 'Activate content duplication', 'polylang-pro' ),
			'deactivate' => __( 'Deactivate content duplication', 'polylang-pro' ),
			'class'      => 'dashicons-before dashicons-admin-page',
		);

		parent::__construct( 'pll-duplicate', $args );
	}
}
