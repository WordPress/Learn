<?php
/**
 * @package Polylang-Pro
 */

/**
 * Filters widgets blocks by language on frontend.
 *
 * @since 3.1
 */
class PLL_Frontend_Filters_Widgets_Blocks extends PLL_Frontend_Filters_Widgets {
	/**
	 * Method that handles the removal of widgets in the sidebars depending on their display language.
	 *
	 * @since 3.1
	 *
	 * @param array  $widget_data      An array containing the widget data.
	 * @param array  $sidebars_widgets An associative array of sidebars and their widgets.
	 * @param string $sidebar          Sidebar name.
	 * @param int    $key              Widget number.
	 * @return array                   An associative array of sidebars and their widgets.
	 */
	public function handle_widget_in_sidebar_callback( $widget_data, $sidebars_widgets, $sidebar, $key ) {
		// Remove the widget if not visible in the current language (blocks in legacy widget).
		if ( ! empty( $widget_data['settings'][ $widget_data['number'] ]['content'] ) ) {
			$parser = new WP_Block_Parser();
			$parser->parse( $widget_data['settings'][ $widget_data['number'] ]['content'] );
			if ( is_array( $parser->output ) ) {
				foreach ( $parser->output as $output ) {
					if ( isset( $output['attrs'] ) ) {
						if ( array_key_exists( 'pll_lang', $output['attrs'] ) ) {
							$lang_to_be_displayed = $output['attrs']['pll_lang'];

							if ( $this->curlang->slug !== $lang_to_be_displayed ) {
								unset( $sidebars_widgets[ $sidebar ][ $key ] );
							}
						}
					}
				}
			}
		}
		return $sidebars_widgets;
	}
}
