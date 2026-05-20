<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\REST\Filtered;

use PLL_REST_API;

/**
 * Filters terms by language in the REST API.
 *
 * @since 3.8
 */
class Term extends Abstract_Object {
	/**
	 * Constructor.
	 *
	 * @since 3.8
	 *
	 * @param PLL_REST_API $rest_api      Instance of PLL_REST_API.
	 * @param array        $content_types Array of taxonomies to filter.
	 */
	public function __construct( PLL_REST_API $rest_api, array $content_types ) {
		parent::__construct( $rest_api, $content_types );

		add_filter( 'get_terms_args', array( $this, 'get_terms_args' ) );
	}

	/**
	 * Filters the query per language according to the 'lang' parameter.
	 *
	 * @since 2.6.9
	 * @since 3.8 Moved from PLL_REST_Term.
	 *
	 * @param array $args WP_Term_Query arguments.
	 * @return array
	 */
	public function get_terms_args( $args ) {
		if ( isset( $args['lang'] ) ) {
			return $args;
		}

		// The first test is necessary to avoid an infinite loop when calling get_languages_list().
		$lang = $this->request->get_language();
		if ( ! empty( array_intersect( $args['taxonomy'] ?? array(), $this->content_types ) ) && ! empty( $lang ) && in_array( $lang->slug, $this->model->get_languages_list( array( 'fields' => 'slug' ) ), true ) ) {
			$args['lang'] = $lang->slug;
		}

		return $args;
	}
}
