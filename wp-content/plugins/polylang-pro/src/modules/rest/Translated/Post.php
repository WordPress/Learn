<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\REST\Translated;

use WP_Post;
use PLL_REST_API;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Syntex\Polylang_Pro\REST\Translated\Abstract_Object;

/**
 * Expose posts language and translations in the REST API
 *
 * @since 3.8
 */
class Post extends Abstract_Object {
	/**
	 * Constructor
	 *
	 * @since 2.2
	 *
	 * @param PLL_REST_API $rest_api      Instance of PLL_REST_API.
	 * @param array        $content_types Array of arrays with post types as keys and options as values.
	 */
	public function __construct( PLL_REST_API $rest_api, array $content_types ) {
		parent::__construct( $rest_api, $content_types );

		foreach ( array_keys( $content_types ) as $post_type ) {
			add_filter( "rest_prepare_{$post_type}", array( $this, 'prepare_response' ), 10, 3 );
		}

		add_filter( 'rest_pre_dispatch', array( $this, 'save_language_and_translations' ), 10, 3 );
	}

	/**
	 * Allows to share the post slug across languages.
	 * Modifies the REST response accordingly.
	 *
	 * @since 2.3
	 *
	 * @param WP_REST_Response $response The response object.
	 * @param WP_Post          $post     Post object.
	 * @param WP_REST_Request  $request  Request object.
	 * @return WP_REST_Response
	 *
	 * @phpstan-param WP_REST_Request<array> $request
	 */
	public function prepare_response( $response, $post, $request ) {
		global $wpdb;

		if ( ! in_array( $request->get_method(), array( 'POST', 'PUT', 'PATCH' ), true ) ) {
			return $response;
		}

		$data = $response->get_data();

		if ( ! is_array( $data ) || empty( $data['slug'] ) ) {
			return $response;
		}

		$params     = $request->get_params();
		$attributes = $request->get_attributes();

		if ( ! empty( $params['slug'] ) ) {
			$requested_slug = $params['slug'];
		} elseif ( is_array( $attributes['callback'] ) && 'create_item' === $attributes['callback'][1] ) {
			// Allow sharing slug by default when creating a new post.
			$requested_slug = sanitize_title( $post->post_title );
		}

		if ( ! isset( $requested_slug ) || $post->post_name === $requested_slug ) {
			return $response;
		}

		$slug = wp_unique_post_slug( $requested_slug, $post->ID, $post->post_status, $post->post_type, $post->post_parent );

		if ( $slug === $data['slug'] || ! $wpdb->update( $wpdb->posts, array( 'post_name' => $slug ), array( 'ID' => $post->ID ) ) ) {
			return $response;
		}

		$data['slug'] = $slug;
		$response->set_data( $data );

		return $response;
	}

	/**
	 * Sets language and saves translations during REST requests.
	 *
	 * @since 3.4
	 *
	 * @param mixed           $result  Response to replace the requested version with.
	 * @param WP_REST_Server  $server  Server instance.
	 * @param WP_REST_Request $request Request used to generate the response.
	 * @return mixed
	 *
	 * @phpstan-param WP_REST_Request<array> $request
	 */
	public function save_language_and_translations( $result, $server, $request ) {
		if ( ! current_user_can( 'edit_posts' ) || ! pll_is_edit_rest_request( $request ) || ! $this->is_save_post_request( $request ) ) {
			return $result;
		}

		$id           = $request->get_param( 'id' );
		$lang         = $request->get_param( 'lang' );
		$translations = $request->get_param( 'translations' );

		if ( ! is_numeric( $id ) ) {
			return $result;
		}

		$post = get_post( (int) $id );
		if ( ! $post instanceof WP_Post ) {
			return $result;
		}

		if ( is_string( $lang ) ) {
			$error = $this->set_language( $lang, $post );

			if ( is_wp_error( $error ) ) {
				return $error;
			}
		}

		if ( is_array( $translations ) ) {
			$this->save_translations( $translations, $post );
		}

		return $result;
	}

	/**
	 * Check if the request is a REST API post type request for saving
	 *
	 * @since 2.7.3
	 * @since 3.4 $post_id parameter removed.
	 *
	 * @param WP_REST_Request $request Request used to generate the response.
	 * @return bool True if the request saves a post.
	 *
	 * @phpstan-param WP_REST_Request<array> $request
	 */
	public function is_save_post_request( $request ) {
		$post_type_rest_bases = wp_list_pluck( get_post_types( array( 'show_in_rest' => true ), 'objects' ), 'rest_base' );

		// Some rest_base could be not defined and WordPress return false. The post type name is taken as rest_base.
		$post_type_rest_bases = array_merge(
			array_filter( $post_type_rest_bases ), // Get rest_base really defined.
			array_keys(  // Otherwise rest_base equals to the post type name.
				array_filter(
					$post_type_rest_bases,
					function ( $value ) {
						return ! $value;
					}
				)
			)
		);

		// Pattern to verify the request route.
		$post_type_pattern = '#(' . implode( '|', array_values( $post_type_rest_bases ) ) . ')/(?P<id>[\d]+)#';
		return preg_match( "$post_type_pattern", $request->get_route() ) && 'PUT' === $request->get_method();
	}

	/**
	 * Returns the current object type, e.g. 'post'.
	 *
	 * @since 3.8
	 *
	 * @return string
	 */
	protected function get_type(): string {
		return 'post';
	}

	/**
	 * Returns the database identifier for the item.
	 *
	 * @since 3.8
	 *
	 * @param array|object $item Item array or object, usually a post or term.
	 * @return int The database identifier, 0 if not found.
	 */
	protected function get_db_id( $item ): int {
		if ( is_array( $item ) ) {
			return $item['ID'] ?? 0;
		}

		return $item->ID ?? 0;
	}
}
