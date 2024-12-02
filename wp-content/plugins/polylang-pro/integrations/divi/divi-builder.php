<?php
/**
 * @package Polylang-Pro
 */

/**
 * Manages compatibility with the Divi Builder.
 *
 * @since 2.3
 */
class PLL_Divi_Builder {
	/**
	 * Constructor.
	 *
	 * @since 2.3
	 */
	public function __construct() {
		add_filter( 'pll_copy_post_metas', array( $this, 'divi_builder_copy_post_metas' ), 10, 2 );
	}

	/**
	 * Allow to copy Divi Builder data when creating a translation.
	 *
	 * @since 2.1
	 *
	 * @param array $metas List of custom fields names.
	 * @param bool  $sync  True if it is synchronization, false if it is a copy.
	 * @return array
	 */
	public function divi_builder_copy_post_metas( $metas, $sync ) {
		$divi_metas = array(
			'_et_pb_post_hide_nav',
			'_et_pb_page_layout',
			'_et_pb_side_nav',
			'_et_pb_use_builder',
			'_et_pb_ab_bounce_rate_limit',
			'_et_pb_ab_stats_refresh_interval',
			'_et_pb_old_content',
			'_et_pb_enable_shortcode_tracking',
			'_et_pb_custom_css',
			'_et_pb_light_text_color',
			'_et_pb_dark_text_color',
			'_et_pb_content_area_background_color',
			'_et_pb_section_background_color',
		);

		return $sync ? $metas : array_merge( $metas, $divi_metas );
	}
}
