<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\REST\Filtered;

use PLL_REST_API;

/**
 * Abstract class to allow to filter posts, terms or comments in the REST API.
 *
 * @since 2.6.9
 * @since 3.8 Renamed from PLL_REST_Filtered_Object.
 */
abstract class Abstract_Object {
	/**
	 * @var \PLL_Model
	 */
	public $model;

	/**
	 * Array of content types to filter by language.
	 *
	 * @var array
	 */
	protected $content_types;

	/**
	 * REST request stored for internal usage.
	 *
	 * @var \WP_Syntex\Polylang\REST\Request
	 */
	protected $request;

	/**
	 * Constructor
	 *
	 * @since 2.6.9
	 *
	 * @param PLL_REST_API $rest_api      Instance of PLL_REST_API.
	 * @param array        $content_types Array of content types to filter by language.
	 */
	public function __construct( PLL_REST_API $rest_api, array $content_types ) {
		$this->model         = $rest_api->model;
		$this->request       = $rest_api->request;
		$this->content_types = $content_types;

		foreach ( $content_types as $content_type ) {
			add_filter( "rest_{$content_type}_collection_params", array( $this, 'collection_params' ) );
		}
	}

	/**
	 * Exposes the 'lang' param for posts and terms
	 *
	 * @since 2.2
	 *
	 * @param array $query_params JSON Schema-formatted collection parameters.
	 * @return array
	 */
	public function collection_params( $query_params ) {
		$query_params['lang'] = array(
			'description' => __( 'Limit results to a specific language. By default, results are not filtered by language.', 'polylang-pro' ),
			'type'        => 'string',
			'enum'        => array_merge( array( '' ), $this->model->get_languages_list( array( 'fields' => 'slug' ) ) ),
			'default'     => '',
		);
		return $query_params;
	}
}
