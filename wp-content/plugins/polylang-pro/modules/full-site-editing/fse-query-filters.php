<?php
/**
 * @package Polylang-Pro
 */

defined( 'ABSPATH' ) || exit;

/**
 * A class that filters the queries to retrieve the templates in the right language.
 *
 * @since 3.2
 */
class PLL_FSE_Query_Filters extends PLL_FSE_Abstract_Module implements PLL_Module_Interface {

	/**
	 * Returns the module's name.
	 *
	 * @since 3.2
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'fse_query_filters';
	}

	/**
	 * Sub-module init.
	 *
	 * @since 3.2
	 *
	 * @return self
	 */
	public function init() {
		/**
		 * Filter the query to allow the use of translated template parts.
		 * - After `PLL_Frontend->parse_query()` ('parse_query' prio 6).
		 * - After `PLL_Admin_Filters_Post->parse_query()` ('parse_query' prio 10).
		 */
		add_action( 'pre_get_posts', array( $this, 'translate_template_query' ), 10000 );
		return $this;
	}

	/**
	 * Filters the query to allow the use of translated template parts.
	 *
	 * @since 3.2
	 *
	 * @param  WP_Query $query Reference to the WP_Query object.
	 * @return void
	 */
	public function translate_template_query( &$query ) {
		if ( ! $query instanceof WP_Query || empty( $query->query_vars['post_name__in'] ) || ! is_array( $query->query_vars['post_name__in'] ) ) {
			// We need a template name.
			return;
		}

		if ( ! PLL_FSE_Tools::is_template_query( $query ) ) {
			// Not a template part query.
			return;
		}

		$def_lang = $this->model->get_default_language();

		if ( empty( $def_lang ) ) {
			// No default language.
			return;
		}

		// Get the requested language.
		$requested_lang = $this->get_requested_language( $query );

		if ( empty( $requested_lang['language'] ) ) {
			// No language found in the query.
			return;
		}

		if ( $requested_lang['language']->slug === $def_lang->slug ) {
			// No new template parts to look for when the current language is the default one.
			return;
		}

		// Prefix the template names.
		$slugs = array();

		foreach ( $query->query_vars['post_name__in'] as $slug ) {
			// /!\ Add the suffixed slug before the not-suffixed one: see orderby.
			$slugs[] = ( new PLL_FSE_Template_Slug( $slug, $this->get_languages_slugs() ) )->update_language( $requested_lang['language']->slug );
			$slugs[] = $slug;
		}

		$query->query_vars['post_name__in'] = $slugs;

		// Make sure we retrieve the template we want by returning the suffixed template first.
		$query->query_vars['orderby'] = 'post_name__in';

		/**
		 * Remove the language query:
		 * - We are requesting 'header___xx' in current language and 'header' in default language.
		 * - Each template name is unique within a theme (which translates as a tax query). Example:
		 *     'tax_query' => [
		 *         [
		 *             'taxonomy' => 'wp_theme',
		 *             'field'    => 'slug',
		 *             'terms'    => 'twentytwentytwo',
		 *         ],
		 *    ],
		 */
		if ( 'tax_query' === $requested_lang['source'] ) {
			unset( $query->tax_query->queries[ $requested_lang['index'] ], $query->query_vars['lang'] ); // phpcs:ignore WordPressVIPMinimum.Hooks.PreGetPosts.PreGetPosts
		} else {
			unset( $query->query_vars['tax_query'][ $requested_lang['index'] ] );
		}
	}

	/**
	 * Returns the language from the request.
	 *
	 * @since 3.2
	 *
	 * @param  WP_Query $query Reference to the `WP_Query` object.
	 * @return array {
	 *     An array containing the language object and its array index from the tax query.
	 *
	 *     @type PLL_Language|false $language Language object, false if no valid language has been found.
	 *     @type int|false          $index    Position where the language query can be found in the tax query.
	 *                                        False if no language query has been found.
	 *     @type string             $source   Location of the language:
	 *                                        - `tax_query` for `$query->tax_query->queries`.
	 *                                        - `query_vars` for `$query->query_vars['tax_query']`.
	 *                                        - An empty string if no valid language has been found.
	 * }
	 */
	private function get_requested_language( WP_Query &$query ) {
		$sources = array(
			'tax_query'  => $query->tax_query->queries,
			'query_vars' => isset( $query->query_vars['tax_query'] ) ? $query->query_vars['tax_query'] : array(),
		);

		foreach ( $sources as $source_name => $tax_queries ) {
			if ( empty( $tax_queries ) || ! is_array( $tax_queries ) ) {
				continue;
			}

			$found = $this->get_requested_language_in_tax_query( $tax_queries );

			if ( false === $found['index'] ) {
				continue;
			}

			// Found it.
			$found['source'] = $source_name;

			return $found;
		}

		return array(
			'language' => false,
			'index'    => false,
			'source'   => '',
		);
	}

	/**
	 * Returns the language from the given taxonomy query.
	 *
	 * @since 3.2
	 *
	 * @param array $tax_query An array of taxonomy queries.
	 * @return array {
	 *     An array containing the language object and its array index from the tax query.
	 *
	 *     @type PLL_Language|false $language Language object, false if no valid language has been found.
	 *     @type int|false          $index    Position where the language query can be found in the tax query.
	 *                                        False if no language query has been found.
	 * }
	 */
	private function get_requested_language_in_tax_query( array $tax_query ) {
		foreach ( $tax_query as $i => $query ) {
			if ( empty( $query['taxonomy'] ) || 'language' !== $query['taxonomy'] ) {
				// This isn't the taxonomy you're looking for.
				continue;
			}

			// We found our `language` query.
			$no_lang = array(
				'language' => false,
				'index'    => $i,
			);

			if ( empty( $query['terms'] ) ) {
				// No terms.
				return $no_lang;
			}

			$field = ! empty( $query['field'] ) ? $query['field'] : 'slug';

			switch ( $field ) {
				case 'term_id':
				case 'term_taxonomy_id':
					// Term ID.
					if ( is_array( $query['terms'] ) ) {
						$query_lang_id = (int) reset( $query['terms'] );
					} elseif ( is_numeric( $query['terms'] ) ) {
						$query_lang_id = (int) $query['terms'];
					} else {
						// Wrong type.
						return $no_lang;
					}

					if ( $query_lang_id <= 0 ) {
						// Invalid ID.
						return $no_lang;
					}

					if ( 'term_taxonomy_id' === $field ) {
						$query_lang_id = "tt:{$query_lang_id}";
					}
					break;

				default:
					// Lang slug.
					if ( is_array( $query['terms'] ) ) {
						$query_lang_id = reset( $query['terms'] );
					} else {
						$query_lang_id = $query['terms'];
					}

					if ( ! is_string( $query_lang_id ) ) {
						// Invalid ID.
						return $no_lang;
					}
			}

			return array(
				'language' => $this->model->get_language( $query_lang_id ),
				'index'    => $i,
			);
		} //end foreach

		return array(
			'language' => false,
			'index'    => false,
		);
	}
}
