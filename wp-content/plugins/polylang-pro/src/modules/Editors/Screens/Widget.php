<?php
/**
 * @package Polylang Pro
 */

namespace WP_Syntex\Polylang_Pro\Editors\Screens;

use WP_Screen;
use PLL_Language;

/**
 * Class to manage Widget editor scripts.
 */
class Widget extends Abstract_Screen {
	/**
	 * Adds required hooks.
	 *
	 * @since 3.7
	 *
	 * @return static
	 */
	public function init() {
		add_filter( 'widget_types_to_hide_from_legacy_widget_block', array( $this, 'filter_legacy_widgets' ) );

		return parent::init();
	}

	/**
	 * Tells if the given screen can enqueue stylesheet for the customizer.
	 *
	 * @since 3.7
	 *
	 * @param WP_Screen $screen The WordPress screen object.
	 * @return bool
	 */
	protected function can_enqueue_style( WP_Screen $screen ): bool {
		return 'customize' === $screen->base || parent::can_enqueue_style( $screen );
	}

	/**
	 * Method that allows legacy widgets in widget block editor previously removed by WP and hide legacy Polylang widget.
	 *
	 * @since 3.2
	 *
	 * @param array $widget_ids An array of hidden widget ids.
	 * @return array
	 */
	public function filter_legacy_widgets( $widget_ids ) {
		$widgets_to_show = array( 'custom_html' );
		$widget_ids = array_diff( $widget_ids, $widgets_to_show );

		$widgets_to_hide = array( 'polylang' );
		$widget_ids = array_merge( $widget_ids, $widgets_to_hide );

		return $widget_ids;
	}


	/**
	 * Tells whether the given screen is the Widget edtitor or not.
	 *
	 * @since 3.7
	 *
	 * @param  WP_Screen $screen The current screen.
	 * @return bool True if Widget editor screen, false otherwise.
	 */
	protected function screen_matches( WP_Screen $screen ): bool {
		return (
			'widgets' === $screen->base
			&& function_exists( 'wp_use_widgets_block_editor' )
			&& wp_use_widgets_block_editor()
			&& $screen->is_block_editor()
		);
	}

	/**
	 * Returns the language to use in the Widget editor.
	 *
	 * @since 3.7
	 *
	 * @return PLL_Language|null
	 */
	protected function get_language(): ?PLL_Language {
		$language = $this->model->languages->get_default();

		return $language instanceof PLL_Language ? $language : null;
	}

	/**
	 * Returns the screen name for the Widget editor to use across all process.
	 *
	 * @since 3.7
	 *
	 * @return string
	 */
	protected function get_screen_name(): string {
		return 'widget';
	}
}
