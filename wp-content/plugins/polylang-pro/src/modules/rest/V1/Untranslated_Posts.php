<?php
/**
 * @package Polylang Pro
 */

namespace WP_Syntex\Polylang_Pro\REST\V1;

use WP_Post;
use WP_Error;
use PLL_Model;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Syntex\Polylang\REST\Abstract_Controller;

/**
 * Class for the untranslated posts REST API endpoint.
 *
 * @since 3.8
 */
class Untranslated_Posts extends Abstract_Controller {
	/**
	 * @var PLL_Model
	 */
	private $model;

	/**
	 * @var array
	 */
	private $post_types;

	/**
	 * @var array
	 */
	protected $schema;

	/**
	 * Constructor.
	 *
	 * @since 3.8
	 *
	 * @param PLL_Model $model The model.
	 * @param array     $post_types The post types.
	 */
	public function __construct( PLL_Model $model, array $post_types ) {
		$this->model      = $model;
		$this->post_types = $post_types;
	}

	/**
	 * Registers the routes for languages.
	 *
	 * @since 3.8
	 *
	 * @return void
	 */
	public function register_routes(): void {
		register_rest_route(
			'pll/v1',
			'/untranslated-posts',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
				'schema' => array( $this, 'get_public_schema' ),
			)
		);
	}

	/**
	 * Retrieves the parameters defined for the untranslated posts REST API endpoint.
	 *
	 * @since 2.6.0
	 * @since 3.8 Moved from PLL_REST_API and renamed.
	 *
	 * @return array REST API endpoint parameters.
	 */
	public function get_collection_params() {
		$language_slugs = $this->model->get_languages_list( array( 'fields' => 'slug' ) );

		return array(
			'type'            => array(
				'description' => __( 'Limit results to items of an object type.', 'polylang-pro' ),
				'type'        => 'string',
				'required'    => true,
				'enum'        => array_keys( $this->post_types ),
			),
			'untranslated_in' => array(
				'description' => __( 'Limit results to untranslated items in a language.', 'polylang-pro' ),
				'type'        => 'string',
				'required'    => true,
				'enum'        => $language_slugs,
			),
			'lang'            => array(
				'description' => __( 'Limit results to items in a language.', 'polylang-pro' ),
				'type'        => 'string',
				'required'    => true,
				'enum'        => $language_slugs,
			),
			'context'         => array(
				'description' => __( 'Scope under which the request is made; determines fields present in response.', 'polylang-pro' ),
				'type'        => 'string',
				'required'    => false,
				'enum'        => array( 'edit', 'view' ),
				'default'     => 'view',
			),
			'search'          => array(
				'description' => __( 'Limit results to those matching a string.', 'polylang-pro' ),
				'type'        => 'string',
			),
			'include'         => array(
				'description'       => __( 'Add this post\'s translation to results.', 'polylang-pro' ),
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			),
		);
	}

	/**
	 * Returns the schema for the public endpoint.
	 *
	 * @since 3.8
	 *
	 * @return array The schema.
	 */
	public function get_public_schema(): array {
		if ( ! empty( $this->schema ) ) {
			return $this->schema;
		}

		$this->schema = array(
			'$schema' => 'http://json-schema.org/draft-04/schema#',
			'title'   => 'untranslated-posts',
			'type'    => 'array',
			'items'   => array(
				'type'       => 'object',
				'properties' => array(
					'id'    => array(
						'type'        => 'integer',
						'description' => __( 'Unique identifier for the post.', 'polylang-pro' ),
					),
					'title' => array(
						'type'       => 'object',
						'properties' => array(
							'raw'      => array(
								'type'        => 'string',
								'description' => __( 'The raw title of the post.', 'polylang-pro' ),
							),
							'rendered' => array(
								'type'        => 'string',
								'description' => __( 'The rendered title of the post.', 'polylang-pro' ),
							),
						),
					),
				),
			),
		);

		return $this->schema;
	}

	/**
	 * Returns a list of posts not translated in a language.
	 *
	 * @since 2.6.0
	 * @since 3.8 Moved from PLL_REST_API.
	 *
	 * @param WP_REST_Request $request REST API request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 *
	 * @phpstan-param WP_REST_Request<array> $request
	 */
	public function get_items( $request ) {
		$return = array();

		$type            = $request->get_param( 'type' ); // Check that's a valid post type is made in `get_items_permissions_check()`.
		$untranslated_in = $this->model->get_language( $request->get_param( 'untranslated_in' ) );
		$lang            = $this->model->get_language( $request->get_param( 'lang' ) );
		$search          = $request->get_param( 'search' );

		if ( ! $untranslated_in ) {
			return $this->get_invalid_param_error( 'unstranslated_in' );
		}

		if ( ! $lang ) {
			return $this->get_invalid_param_error( 'lang' );
		}

		if ( ! is_string( $search ) ) {
			$search = '';
		}

		$untranslated_posts = $this->model->post->get_untranslated( $type, $untranslated_in, $lang, $search );

		// Add current translation in list.
		$post_id = $this->model->post->get_translation( (int) $request->get_param( 'include' ), $lang );
		if ( $post_id ) {
			$post = get_post( $post_id );
			if ( $post instanceof WP_Post ) {
				array_unshift( $untranslated_posts, $post );
			}
		}

		// Format output.
		foreach ( $untranslated_posts as $post ) {
			$return[] = array(
				'id'    => $post->ID,
				'title' => array(
					'raw'      => $post->post_title,
					'rendered' => get_the_title( $post->ID ),
				),
			);
		}

		return rest_ensure_response( $return );
	}

	/**
	 * Checks if a given request has access to read untranslated posts.
	 *
	 * @since 3.8
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 *
	 * @phpstan-param WP_REST_Request<array> $request
	 */
	public function get_items_permissions_check( $request ) {
		$post_type_object = get_post_type_object( $request->get_param( 'type' ) );

		if ( empty( $post_type_object ) ) {
			return $this->get_invalid_param_error( 'type' );
		}

		if ( 'edit' === $request['context'] && ! current_user_can( $post_type_object->cap->edit_posts ) ) {
			return new WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to edit posts in this post type.', 'polylang-pro' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Returns a WP_Error object for a given argument.
	 *
	 * @since 3.8
	 *
	 * @param string $arg The name of the invalid argument.
	 * @return WP_Error
	 */
	private function get_invalid_param_error( string $arg ): WP_Error {
		return new WP_Error(
			'rest_invalid_param',
			/* translators: %s is the name of a REST API argument.*/
			sprintf( __( 'Missing or invalid "%s" argument.', 'polylang-pro' ), $arg ),
			array( 'status' => 400 )
		);
	}
}
