<?php
/**
 * @package Polylang-Pro
 */

/**
 * Filters comments in the REST API.
 *
 * @since 2.6.9
 */
class PLL_REST_Comment extends PLL_REST_Filtered_Object {

	/**
	 * Constructor.
	 *
	 * @since 2.6.9
	 *
	 * @param PLL_REST_API $rest_api Instance of PLL_REST_API.
	 */
	public function __construct( &$rest_api ) {
		parent::__construct( $rest_api, array( 'comment' => array() ) );

		$this->type = 'comment';

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
		if ( isset( $this->request['lang'] ) && in_array( $this->request['lang'], $this->model->get_languages_list( array( 'fields' => 'slug' ) ) ) ) {
			$query->query_vars['lang'] = $this->request['lang'];
		}
	}
}
