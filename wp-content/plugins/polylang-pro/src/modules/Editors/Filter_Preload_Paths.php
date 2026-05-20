<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Editors;

use WP_Post;
use PLL_Base;
use PLL_Model;
use PLL_Language;
use PLL_Admin_Block_Editor;
use WP_Block_Editor_Context;

/**
 * Class to filter REST preload paths.
 *
 * @since 3.7
 */
class Filter_Preload_Paths {
	/**
	 * @var PLL_Model
	 */
	protected $model;

	/**
	 * @var PLL_Language|false|null
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
	 * @param PLL_Base $polylang Polylang object.
	 */
	public function __construct( PLL_Base &$polylang ) {
		$this->model        = &$polylang->model;
		$this->curlang      = &$polylang->curlang;
		$this->block_editor = &$polylang->block_editor;
	}

	/**
	 * Adds required hooks.
	 *
	 * @since 3.7
	 *
	 * @return self
	 */
	public function init(): self {
		add_filter( 'block_editor_rest_api_preload_paths', array( $this, 'filter_preload_paths' ), 50, 2 );
		add_filter( 'pll_filtered_rest_routes', array( $this, 'filter_navigation_fallback_route' ) );

		return $this;
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
		if ( 'core/edit-post' !== $context->name ) {
			$lang = ! empty( $this->curlang ) ? $this->curlang->slug : null;

			if ( empty( $lang ) || 'core/edit-widgets' === $context->name ) {
				// WP 6.0+: widget screen filtered by default language. See `WP_Syntex\Polylang_Pro\Editors\Screens\Widget::get_language()`.
				$lang = $this->model->options['default_lang'];
			}

			$preload_paths = $this->block_editor->filter_rest_routes->add_query_parameters(
				$preload_paths,
				array(
					'lang' => $lang,
				)
			);
		}

		$preload_paths[] = '/pll/v1/languages';

		return $preload_paths;
	}

	/**
	 * Adds navigation fallback REST route to the filterable ones.
	 *
	 * @since 3.7
	 *
	 * @param string[] $routes Filterable REST routes.
	 * @return string[] Filtered filterable REST routes.
	 */
	public function filter_navigation_fallback_route( $routes ) {
		$routes['navigation-fallback'] = 'wp-block-editor/v1/navigation-fallback';

		return $routes;
	}
}
