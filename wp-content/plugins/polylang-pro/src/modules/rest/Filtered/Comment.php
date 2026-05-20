<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\REST\Filtered;

use PLL_REST_API;
use WP_Comment_Query;

/**
 * Filters comments in the REST API.
 *
 * @since 2.6.9
 * @since 3.8 Renamed from PLL_REST_Comment.
 */
class Comment extends Abstract_Object {
	/**
	 * Constructor.
	 *
	 * @since 2.6.9
	 *
	 * @param PLL_REST_API $rest_api Instance of PLL_REST_API.
	 */
	public function __construct( PLL_REST_API $rest_api ) {
		parent::__construct( $rest_api, array( 'comment' ) );

		add_action( 'parse_comment_query', array( $this, 'parse_comment_query' ), 5 );
	}

	/**
	 * Filters the query per language according to the 'lang' parameter.
	 *
	 * @since 2.6.9
	 *
	 * @param WP_Comment_Query $query Comment query.
	 * @return void
	 */
	public function parse_comment_query( $query ) {
		$lang = $this->request->get_language();
		if ( ! empty( $lang ) && in_array( $lang->slug, $this->model->get_languages_list( array( 'fields' => 'slug' ) ), true ) ) {
			$query->query_vars['lang'] = $lang->slug;
		}
	}
}
