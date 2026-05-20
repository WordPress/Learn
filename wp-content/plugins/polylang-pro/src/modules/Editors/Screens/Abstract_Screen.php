<?php
/**
 * @package Polylang Pro
 */

namespace WP_Syntex\Polylang_Pro\Editors\Screens;

use PLL_Model;
use PLL_Base;
use WP_Screen;
use PLL_Language;
use PLL_Admin_Block_Editor;

/**
 * Template class to manage editors scripts.
 *
 * @since 3.7
 */
abstract class Abstract_Screen {
	/**
	 * The script suffix, default empty.
	 *
	 * @var string
	 */
	protected $suffix = '';

	/**
	 * @var PLL_Admin_Block_Editor|null
	 */
	protected $block_editor;

	/**
	 * @var PLL_Model
	 */
	protected $model;

	/**
	 * Constructor.
	 *
	 * @since 3.7
	 *
	 * @param PLL_Base $polylang Polylang main object.
	 */
	public function __construct( &$polylang ) {
		if ( ! defined( 'SCRIPT_DEBUG' ) || ! SCRIPT_DEBUG ) {
			$this->suffix = '.min';
		}

		$this->model        = &$polylang->model;
		$this->block_editor = &$polylang->block_editor;
	}

	/**
	 * Adds required hooks.
	 *
	 * @since 3.7
	 *
	 * @return static
	 */
	public function init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );

		return $this;
	}

	/**
	 * Enqueues script for the editors.
	 *
	 * @since 3.7
	 *
	 * @return void
	 */
	public function enqueue(): void {
		$screen = get_current_screen();
		if ( empty( $screen ) ) {
			return;
		}

		if ( $this->can_enqueue_style( $screen ) ) {
			$this->enqueue_style();
		}

		if ( ! $this->screen_matches( $screen ) ) {
			return;
		}

		wp_enqueue_script(
			static::get_handle(),
			plugins_url( $this->get_script_path(), POLYLANG_ROOT_FILE ),
			array(
				'wp-api-fetch',
				'wp-data',
				'wp-i18n',
				'wp-sanitize',
				'lodash',
			),
			POLYLANG_VERSION,
			true
		);

		$editor_lang = $this->get_language();
		if ( ! empty( $editor_lang ) ) {
			$editor_lang = $editor_lang->to_array();
		}
		$pll_settings_script = 'let pll_block_editor_plugin_settings = ' . wp_json_encode(
			/**
			 * Filters settings required by the UI.
			 *
			 * @since 3.6
			 *
			 * @param array $settings.
			 */
			(array) apply_filters(
				'pll_block_editor_plugin_settings',
				array(
					'lang'                     => $editor_lang,
					'nonce'                    => wp_create_nonce( 'pll_language' ),
					'authorizedLanguagesSlugs' => $this->model->languages
						->filter( 'translator' )
						->get_list( array( 'fields' => 'slug' ) ),
				)
			)
		);

		wp_add_inline_script( static::get_handle(), $pll_settings_script, 'before' );
		wp_set_script_translations( static::get_handle(), 'polylang-pro' );

		if ( ! empty( $this->block_editor ) ) {
			$this->block_editor->filter_rest_routes->add_inline_script( static::get_handle() );
		}
	}

	/**
	 * Tells if the given screen matches the type of the current object.
	 *
	 * @since 3.7
	 *
	 * @param WP_Screen $screen The WordPress screen object.
	 * @return bool True is the screen is a match, false otherwise.
	 */
	abstract protected function screen_matches( WP_Screen $screen ): bool;

	/**
	 * Returns the current editor language.
	 *
	 * @since 3.7
	 *
	 * @return PLL_Language|null The language object if found, `null` otherwise.
	 */
	abstract protected function get_language(): ?PLL_Language;

	/**
	 * Returns the screen name to use across all process.
	 *
	 * @since 3.7
	 *
	 * @return string
	 */
	abstract protected function get_screen_name(): string;

	/**
	 * Tells if the given screen is suitable for stylesheet enqueueing.
	 *
	 * @since 3.7
	 *
	 * @param WP_Screen $screen The WordPress screen object.
	 * @return bool
	 */
	protected function can_enqueue_style( WP_Screen $screen ): bool {
		return $this->screen_matches( $screen );
	}

	/**
	 * Returns the main script handle for the editor.
	 * Useful to add inline scripts or to register translations for instance.
	 *
	 * @since 3.7
	 *
	 * @return string The handle.
	 */
	protected function get_handle(): string {
		return "pll_{$this->get_screen_name()}_sidebar";
	}

	/**
	 * Returns the path to the main script for the editor.
	 *
	 * @since 3.7
	 *
	 * @return string The full path.
	 */
	protected function get_script_path(): string {
		return "/js/build/editors/{$this->get_screen_name()}{$this->suffix}.js";
	}

	/**
	 * Enqueues stylesheet commonly used in all editors.
	 * Override to your taste.
	 *
	 * @since 3.7
	 *
	 * @return void
	 */
	protected function enqueue_style(): void {
		wp_enqueue_style(
			'polylang-block-widget-editor-css',
			plugins_url( '/css/build/style' . $this->suffix . '.css', POLYLANG_ROOT_FILE ),
			array( 'wp-components' ),
			POLYLANG_VERSION
		);
	}
}
