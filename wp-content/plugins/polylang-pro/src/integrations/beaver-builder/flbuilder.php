<?php
/**
 * @package Polylang-Pro
 */

/**
 * Manages compatibility with Beaver Builder.
 *
 * @since 2.3
 */
class PLL_FLBuilder {
	/**
	 * Constructor.
	 *
	 * @since 2.3
	 */
	public function __construct() {
		add_filter( 'pll_copy_post_metas', array( $this, 'fl_builder_copy_post_metas' ), 10, 2 );
	}

	/**
	 * Allow to copy Beaver Builder data when creating a translation.
	 *
	 * @since 1.9.1
	 *
	 * @param array $metas List of custom fields names.
	 * @param bool  $sync  True if it is synchronization, false if it is a copy.
	 * @return array
	 */
	public function fl_builder_copy_post_metas( $metas, $sync ) {
		$bb_metas = array(
			'_fl_builder_draft',
			'_fl_builder_draft_settings',
			'_fl_builder_data',
			'_fl_builder_data_settings',
			'_fl_builder_enabled',
		);

		return $sync ? $metas : array_merge( $metas, $bb_metas );
	}
}
