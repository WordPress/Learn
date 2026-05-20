<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\REST\Filtered;

use PLL_Query;
use PLL_Language;
use PLL_REST_API;
use PLL_FSE_Tools;
use PLL_FSE_Template_Slug;
use WP_Block_Template;
use WP_Query;

/**
 * Filters templates by language in the REST API.
 *
 * @since 3.8
 */
class Template extends Abstract_Object {
	/**
	 * Constructor.
	 *
	 * @since 3.8
	 *
	 * @param PLL_REST_API $rest_api Instance of PLL_REST_API.
	 */
	public function __construct( PLL_REST_API $rest_api ) {
		parent::__construct( $rest_api, PLL_FSE_Tools::get_template_post_types() );

		add_action( 'parse_query', array( $this, 'parse_query' ), 1 );
	}

	/**
	 * Filters templates by language.
	 *
	 * @since 3.2
	 * @since 3.8 Moved from PLL_FSE_REST_Template.
	 *
	 * @param WP_Query $query WP_Query object.
	 * @return void
	 */
	public function parse_query( $query ) {
		if ( ! empty( $query->query_vars['post_name__in'] ) && is_array( $query->query_vars['post_name__in'] ) ) {
			// Do not filter query for a single item.
			return;
		}

		if ( isset( $query->query_vars['lang'] ) && empty( $query->query_vars['lang'] ) ) {
			// We've been asking not to filter by language.
			return;
		}

		if ( ! PLL_FSE_Tools::is_template_query( $query ) ) {
			// Not a template part query.
			return;
		}

		// Filter a templates list query by the default language.
		$lang = $this->request->get_language();
		if ( empty( $lang ) ) {
			$lang = $this->model->get_default_language();
		}

		if ( empty( $lang ) ) {
			return;
		}

		// Since it's a template part query, take care of the ones stored as files with the next hook.
		add_filter( 'get_block_templates', array( $this, 'filter_template_part_list' ), 10, 3 );

		$pll_query = new PLL_Query( $query, $this->model );
		$pll_query->set_language( $lang );
		$pll_query->filter_query( $lang );
	}

	/**
	 * Filters template part list according to the REST request parameters.
	 * Filtering by language is already done on a `WP_Query` level, see {`self::parse_query()`}.
	 *
	 * @since 3.3.2
	 * @since 3.8 Moved from PLL_FSE_REST_Template.
	 *
	 * @param WP_Block_Template[] $templates     Array of found block templates.
	 * @param array               $query         Arguments to retrieve templates.
	 * @param string              $template_type 'wp_template' or 'wp_template_part'.
	 * @return WP_Block_Template[] Array of filtered block templates.
	 */
	public function filter_template_part_list( $templates, $query, $template_type ) {
		if ( isset( $query['wp_id'] ) ) {
			return $templates;
		}

		if ( 'wp_template_part' !== $template_type ) {
			return $templates;
		}

		$lang = $this->request->get_language();

		if ( empty( $lang ) ) {
			return $templates;
		}

		$with_untranslated = $this->request->get_param( 'include_untranslated' );

		if ( ! empty( $with_untranslated ) ) {
			$templates = array_merge( $templates, $this->get_untranslated_template_parts( $lang ) );
		}

		return $this->remove_unwanted_template_part_files( $templates, $lang );
	}

	/**
	 * Filters out template part files if they are already existing translations of them in the list.
	 *
	 * @since 3.3.2
	 * @since 3.8 Moved from PLL_FSE_REST_Template.
	 *
	 * @param WP_Block_Template[] $templates     Array of found block templates.
	 * @param PLL_Language        $current_lang  Current language object.
	 * @return WP_Block_Template[] Array of filtered block templates.
	 */
	private function remove_unwanted_template_part_files( array $templates, PLL_Language $current_lang ) {
		$templates_to_remove = array();

		// First, let's find template part objects with a language.
		foreach ( $templates as $template ) {
			$template_slug = new PLL_FSE_Template_Slug(
				$template->slug,
				array( $current_lang->slug )
			);

			if ( ! empty( $template->wp_id ) && ! empty( $this->model->post->get_language( $template->wp_id ) ) ) {
				// Current template is custom and has a language.
				$templates_to_remove[] = $template_slug->get_template_slug();
			}
		}

		// Then remove duplicates stored as files.
		foreach ( $templates as $i => $template ) {
			if ( empty( $template->wp_id ) && in_array( $template->slug, $templates_to_remove, true ) ) {
				// Duplicated template part stored in a file.
				unset( $templates[ $i ] );
				continue;
			}
		}

		return $templates;
	}

	/**
	 * Returns template parts without translation in the given language.
	 *
	 * @since 3.3.2
	 * @since 3.8 Moved from PLL_FSE_REST_Template.
	 *
	 * @param PLL_Language $lang The language to check against.
	 * @return WP_Block_Template[] Array of block template parts objects.
	 */
	private function get_untranslated_template_parts( PLL_Language $lang ) {
		$def_lang = $this->model->get_default_language();

		if ( empty( $def_lang ) ) {
			// No default language.
			return array();
		}

		if ( $lang->slug === $def_lang->slug ) {
			// Untranslated template parts are not available for the default language.
			return array();
		}

		$untranslated_posts          = $this->model->post->get_untranslated( 'wp_template_part', $lang, $def_lang );
		$untranslated_template_parts = array();

		foreach ( $untranslated_posts as $untranslated_post ) {
			$untranslated_template_part = _build_block_template_result_from_post( $untranslated_post );

			if ( is_wp_error( $untranslated_template_part ) ) {
				continue;
			}

			$untranslated_template_parts[] = $untranslated_template_part;
		}

		return $untranslated_template_parts;
	}
}
