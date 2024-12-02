<?php
/**
 * @package Polylang-Pro
 */

/**
 * Setup the block editor plugin
 *
 * @since 2.6
 */
class PLL_Block_Editor_Plugin {
	/**
	 * @var PLL_Model
	 */
	protected $model;

	/**
	 * @var PLL_CRUD_Posts
	 */
	protected $posts;

	/**
	 * @var array
	 */
	protected $options;

	/**
	 * @var PLL_Language|false
	 */
	protected $curlang;

	/**
	 * @var PLL_Admin_Block_Editor|null
	 */
	protected $block_editor;

	/**
	 * Constructor
	 *
	 * @since 2.6
	 *
	 * @param PLL_Frontend|PLL_Admin|PLL_Settings|PLL_REST_Request $polylang Polylang object.
	 */
	public function __construct( &$polylang ) {
		$this->model        = &$polylang->model;
		$this->posts        = &$polylang->posts;
		$this->options      = &$polylang->options;
		$this->curlang      = &$polylang->curlang;
		$this->block_editor = &$polylang->block_editor; // Used to get a shared instance of `PLL_Filter_REST_Route` and call a single instance through the plugin.

		add_filter( 'block_editor_rest_api_preload_paths', array( $this, 'filter_preload_paths' ), 50, 2 );
		add_filter( 'widget_types_to_hide_from_legacy_widget_block', array( $this, 'filter_legacy_widgets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Filters preload paths based on the context (block editor for posts, site editor or widget editor for instance).
	 *
	 * @since 3.4
	 *
	 * @param (string|string[])[]     $preload_paths Preload paths.
	 * @param WP_Block_Editor_Context $context       Editor context.
	 * @return array Filtered preload paths.
	 */
	public function filter_preload_paths( $preload_paths, $context ) {
		if ( ! $context instanceof WP_Block_Editor_Context || empty( $this->block_editor ) ) {
			return $preload_paths;
		}

		if ( $context->post instanceof WP_Post && ! $this->model->is_translated_post_type( $context->post->post_type ) ) {
			return $preload_paths;
		}

		$preload_paths = (array) $preload_paths;

		// Do nothing if in post editor since `PLL_Admin_Block_Editor` has already filtered.
		if ( ! $this->is_edit_post_context( $context ) ) {
			$lang = ! empty( $this->curlang ) ? $this->curlang->slug : null;

			if ( empty( $lang ) || $this->is_edit_widgets_context( $context, $preload_paths ) ) {
				// WP 6.0+: widget screen filtered by default language. See `pllDefaultLanguage` JS var added.
				$lang = $this->model->options['default_lang'];
			}

			$preload_paths = $this->block_editor->filter_rest_routes->add_query_parameters(
				$preload_paths,
				array(
					'lang' => $lang,
				)
			);

			if ( $this->is_edit_site_context( $context, $preload_paths ) ) {
				// User data required for the site editor (WP already adds it to the post block editor).
				$preload_paths[] = '/wp/v2/users/me';
			}
		}

		$preload_paths[] = '/pll/v1/languages';

		return $preload_paths;
	}

	/**
	 * Enqueue scripts for the block editor plugin.
	 *
	 * @since 2.6
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		$screen = get_current_screen();
		if ( empty( $screen ) ) {
			return;
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		$this->enqueue_style_for_specific_screen( $screen, $suffix );

		// Enqueue scripts for widget screen
		if ( $this->is_widget_screen( $screen ) ) {
			$script_filename = '/js/build/widget-editor-plugin' . $suffix . '.js';
			$script_handle   = 'pll_widget-editor-plugin';
			wp_enqueue_script(
				$script_handle,
				plugins_url( $script_filename, POLYLANG_BASENAME ),
				array(
					'wp-api-fetch',
					'wp-data',
					'lodash',
				),
				POLYLANG_VERSION,
				true
			);

			$default_lang_script = 'const pllDefaultLanguage = "' . $this->options['default_lang'] . '";';
			wp_add_inline_script(
				$script_handle,
				$default_lang_script,
				'before'
			);

			if ( ! empty( $this->block_editor ) ) {
				$this->block_editor->filter_rest_routes->add_inline_script( $script_handle );
			}

			// Translated strings used in JS code
			wp_set_script_translations( $script_handle, 'polylang-pro' );

			return;
		}

		// Enqueue scripts for post screen and in block editor context
		if ( $this->is_translatable_post_screen( $screen ) && $this->is_block_editor( $screen ) ) {
			$script_filename = '/js/build/block-editor-plugin' . $suffix . '.js';
			$script_handle   = 'pll_block-editor-plugin';
			wp_register_script(
				$script_handle,
				plugins_url( $script_filename, POLYLANG_ROOT_FILE ),
				array(
					'wp-api-fetch',
					'wp-data',
					'wp-sanitize',
					'lodash',
				),
				POLYLANG_VERSION,
				true
			);

			// Set default language according to the context if no language is defined yet.
			$editor_lang = $this->get_editor_language();
			if ( ! empty( $editor_lang ) ) {
				$editor_lang = $editor_lang->to_array();
			}
			$pll_settings = 'let pll_block_editor_plugin_settings = ' . wp_json_encode(
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
						'lang' => $editor_lang,
					)
				)
			);

			wp_add_inline_script( $script_handle, $pll_settings, 'before' );

			if ( ! empty( $this->block_editor ) ) {
				$this->block_editor->filter_rest_routes->add_inline_script( $script_handle );
			}

			wp_enqueue_script( $script_handle );

			$script_filename = '/js/build/sidebar' . $suffix . '.js';
			wp_enqueue_script(
				'pll_sidebar',
				plugins_url( $script_filename, POLYLANG_ROOT_FILE ),
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

			// Translated strings used in JS code
			wp_set_script_translations( 'pll_sidebar', 'polylang-pro' );
		}
	}

	/**
	 * Enqueue style for a specific screen.
	 *
	 * @since 3.1
	 *
	 * @param  WP_Screen $screen The current screen.
	 * @param  string    $suffix The file suffix.
	 * @return void
	 */
	private function enqueue_style_for_specific_screen( $screen, $suffix ) {
		// Enqueue specific styles for block and widget editor UI
		if ( $this->is_widget_screen( $screen ) || $this->is_widget_customizer_screen( $screen ) ||
			( $this->is_translatable_post_screen( $screen ) && $this->is_block_editor( $screen ) ) ) {
			wp_enqueue_style(
				'polylang-block-widget-editor-css',
				plugins_url( '/css/build/style' . $suffix . '.css', POLYLANG_ROOT_FILE ),
				array( 'wp-components' ),
				POLYLANG_VERSION
			);
		}
	}

	/**
	 * Checks if we're in the context of post or site editor screen.
	 *
	 * @since 3.1
	 *
	 * @param  WP_Screen $screen The current screen.
	 * @return bool              True if post screen, false otherwise.
	 */
	private function is_translatable_post_screen( $screen ) {
		return ( 'post' === $screen->base && $this->model->is_translated_post_type( $screen->post_type ) ) ||
				( 'site-editor' === $screen->base && $this->model->is_translated_post_type( 'wp_template_part' ) ) ||
				( 'appearance_page_gutenberg-edit-site' === $screen->base && $this->model->is_translated_post_type( 'wp_template_part' ) );
	}

	/**
	 * Check if we're in the context of a widget screen.
	 *
	 * @since 3.1
	 *
	 * @param  WP_Screen $screen The current screen.
	 * @return bool              True if widget screen, false otherwise.
	 */
	private function is_widget_screen( $screen ) {
		return 'widgets' === $screen->base && function_exists( 'wp_use_widgets_block_editor' ) && wp_use_widgets_block_editor();
	}

	/**
	 * Check if we're in the context of a block editor.
	 *
	 * @since 3.1
	 *
	 * @param  WP_Screen $screen The current screen.
	 * @return bool              True if block editor, false otherwise.
	 */
	private function is_block_editor( $screen ) {
		return method_exists( $screen, 'is_block_editor' ) && $screen->is_block_editor();
	}

	/**
	 * Check if we're in the context of a widget customizer screen.
	 *
	 * @since 3.2
	 *
	 * @param  WP_Screen $screen The current screen.
	 * @return bool              True if widget customizer screen, false otherwise.
	 */
	private function is_widget_customizer_screen( $screen ) {
		return 'customize' === $screen->base;
	}

	/**
	 * Returns the language to use in the editor.
	 *
	 * @since 3.2
	 *
	 * @return PLL_Language|null
	 */
	private function get_editor_language() {
		global $post;

		if ( ! empty( $this->curlang ) && PLL_FSE_Tools::is_site_editor() ) {
			return $this->curlang;
		}

		if ( ! empty( $post ) && $this->model->is_translated_post_type( $post->post_type ) ) {
			$this->posts->set_default_language( $post->ID );
			$post_lang = $this->model->post->get_language( $post->ID );
			return ! empty( $post_lang ) ? $post_lang : null;
		}

		return null;
	}

	/**
	 * Method that allow legacy widgets in widget block editor previously removed by WP and hide legacy Polylang widget.
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
	 * Tells if we're in the post editor context.
	 *
	 * @since 3.5
	 *
	 * @param WP_Block_Editor_Context $context Editor context.
	 * @return bool
	 */
	private function is_edit_post_context( WP_Block_Editor_Context $context ): bool {
		if ( property_exists( $context, 'name' ) ) {
			// WP 6.0+.
			return 'core/edit-post' === $context->name;
		}

		/*
		 * Backward compatibility with WP < 6.0 where `WP_Block_Editor_Context::$name` doesn't exist yet:
		 * A post is passed only in the 'core/edit-post' context (still true, so far).
		 */
		return $context->post instanceof WP_Post;
	}

	/**
	 * Tells if we're in the widgets editor context.
	 *
	 * @since 3.5
	 *
	 * @param WP_Block_Editor_Context $context       Editor context.
	 * @param (string|string[])[]     $preload_paths Preload paths.
	 * @return bool
	 */
	private function is_edit_widgets_context( WP_Block_Editor_Context $context, array $preload_paths ): bool {
		if ( property_exists( $context, 'name' ) ) {
			// WP 6.0+.
			return 'core/edit-widgets' === $context->name;
		}

		/*
		 * Backward compatibility with WP < 6.0 where `WP_Block_Editor_Context::$name` doesn't exist yet:
		 * Sniff preload paths.
		 * Search for:
		 *  - '/wp/v2/sidebars?context=edit&per_page=-1',
		 *  - '/wp/v2/widgets?context=edit&per_page=-1&_embed=about'.
		 *
		 * @see wp-admin/widgets-form-blocks.php
		 */
		return $this->match_paths( array( 'sidebars', 'widgets' ), $preload_paths );
	}

	/**
	 * Tells if we're in the site editor context.
	 *
	 * @since 3.5
	 *
	 * @param WP_Block_Editor_Context $context       Editor context.
	 * @param (string|string[])[]     $preload_paths Preload paths.
	 * @return bool
	 */
	private function is_edit_site_context( WP_Block_Editor_Context $context, array $preload_paths ): bool {
		if ( property_exists( $context, 'name' ) ) {
			// WP 6.0+.
			return 'core/edit-site' === $context->name;
		}

		/*
		 * Backward compatibility with WP < 6.0 where `WP_Block_Editor_Context::$name` doesn't exist yet:
		 * Sniff preload paths.
		 * Search for:
		 *  - '/wp/v2/types/wp_template?context=edit',
		 *  - '/wp/v2/types/wp_template-part?context=edit',
		 *  - '/wp/v2/templates?context=edit&per_page=-1',
		 *  - '/wp/v2/template-parts?context=edit&per_page=-1',
		 *  - '/wp/v2/themes?context=edit&status=active',
		 *  - '/wp/v2/global-styles/{$active_global_styles_id}?context=edit'.
		 *
		 * @see wp-admin/site-editor.php
		 */
		return $this->match_paths(
			array(
				'types/wp_template',
				'types/wp_template-part',
				'templates',
				'template-parts',
				'themes',
				'global-styles/\d+',
			),
			$preload_paths
		);
	}

	/**
	 * Tells if a given list of URIs matches a given list of preload paths.
	 * Works only for paths in the form of `/wp/v2/{URI}?context=edit`.
	 * This is used for backward compatibility with WP < 6.0.
	 *
	 * @since 3.5
	 *
	 * @param string[]            $uris_to_find   List of URIs to find in `$paths_haystack`.
	 * @param (string|string[])[] $paths_haystack List of preload paths to search in.
	 * @return bool
	 */
	private function match_paths( array $uris_to_find, array $paths_haystack ): bool {
		$pattern  = sprintf( '@^/wp/v2/(?:%s)\?(?:.+&)?context=edit(?:&|$)@m', implode( '|', $uris_to_find ) );
		$haystack = implode( "\n", array_filter( $paths_haystack, 'is_string' ) );

		// We expect a precise number of matches but a plugin could add more.
		return preg_match_all( $pattern, $haystack, $matches ) >= count( $uris_to_find );
	}
}
