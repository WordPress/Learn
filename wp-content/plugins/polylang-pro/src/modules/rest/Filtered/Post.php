<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\REST\Filtered;

use WP_Query;
use PLL_Query;
use PLL_REST_API;

/**
 * Filters posts by language in the REST API.
 *
 * @since 3.8
 */
class Post extends Abstract_Object {
	/**
	 * Constructor.
	 *
	 * @since 3.8
	 *
	 * @param PLL_REST_API $rest_api      Instance of PLL_REST_API.
	 * @param array        $content_types Array of post types to filter.
	 */
	public function __construct( PLL_REST_API $rest_api, array $content_types ) {
		parent::__construct( $rest_api, $content_types );

		add_action( 'parse_query', array( $this, 'parse_query' ), 1 );
	}

	/**
	 * Filters the query per language according to the 'lang' parameter from the REST request.
	 *
	 * @since 2.6.9
	 * @since 3.8 Moved from PLL_REST_Post.
	 *
	 * @param WP_Query $query WP_Query object.
	 * @return void
	 */
	public function parse_query( $query ) {
		if ( $this->can_filter_query( $query ) ) {
			$lang = $this->request->get_language();
			if ( empty( $lang ) ) {
				return;
			}
			$pll_query = new PLL_Query( $query, $this->model );
			$pll_query->query->set( 'lang', $lang->slug ); // Set query vars "lang" with the REST parameter value; fix #405 and #384
			$pll_query->filter_query( $lang ); // fix #493
		}
	}

	/**
	 * Tells whether or not the given query is filterable by language.
	 *
	 * @since 3.2
	 * @since 3.8 Moved from PLL_REST_Post.
	 *
	 * @param WP_Query $query The query to check.
	 * @return bool True if filterable by language. False if the query is already filtered,
	 *                   no language has been passed in the request or the post type is not supported.
	 */
	private function can_filter_query( $query ) {
		if ( ! $this->request->is_read_only() ) {
			// Don't filter by language during an edition request or if there is no request.
			return false;
		}

		$query_post_types           = ! empty( $query->query['post_type'] ) ? (array) $query->query['post_type'] : array( 'post' );
		$allowed_queried_post_types = array_intersect( $query_post_types, $this->content_types );

		return empty( $query->get( 'lang' ) ) && ! empty( $this->request->get_language() ) && ! empty( $allowed_queried_post_types );
	}
}
