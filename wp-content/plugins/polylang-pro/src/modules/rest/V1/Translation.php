<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\REST\V1;

use WP_Post;
use WP_Error;
use PLL_Base;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Controller;
use PLL_Sync_Post_Model;
use WP_REST_Posts_Controller;
use WP_Syntex\Polylang\Capabilities\Capabilities;

/**
 * Translation feature for the REST API.
 * Only supports post duplication for now.
 *
 * @since 3.8
 */
class Translation extends WP_REST_Controller {
	/**
	 * @var \PLL_Model
	 */
	private $model;

	/**
	 * Post duplicator.
	 *
	 * @var PLL_Sync_Post_Model
	 */
	private $sync_post_model;

	/**
	 * Constructor.
	 *
	 * @since 3.8
	 *
	 * @param PLL_Base $polylang Polylang's base object.
	 */
	public function __construct( PLL_Base $polylang ) {
		$this->namespace       = 'pll/v1';
		$this->rest_base       = 'translation';
		$this->model           = $polylang->model;
		$this->sync_post_model = $polylang->sync_post_model;
	}

	/**
	 * Registers the route.
	 *
	 * @since 3.8
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			"/{$this->rest_base}",
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
				),
				'schema'      => array( $this, 'get_public_item_schema' ),
				'allow_batch' => array( 'v1' => true ),
			)
		);
	}

	/**
	 * Duplicates a post.
	 *
	 * @since 3.8
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 *
	 * @phpstan-template T of array
	 * @phpstan-param WP_REST_Request<T> $request
	 */
	public function create_item( $request ) {
		/** @var WP_Post $from_post Ensured in `self::create_item_permissions_check()`. */
		$from_post = get_post(
			$request->get_param( 'from_post' )
		);

		if ( ! $from_post instanceof WP_Post ) {
			return new WP_Error(
				'rest_post_invalid_id',
				__( 'Invalid post ID.', 'polylang-pro' ),
				array( 'status' => 404 )
			);
		}

		if ( ! $this->model->is_translated_post_type( $from_post->post_type ) ) {
			return new WP_Error(
				'rest_invalid_param',
				__( 'Invalid parameter: the post is not translatable.', 'polylang-pro' ),
				array( 'status' => 400 ),
			);
		}

		/** @var \PLL_Language $language Ensured by the JSON schema. */
		$language = $this->model->languages->get(
			$request->get_param( 'lang' )
		);

		$duplicated_post_id = $this->sync_post_model->copy(
			$from_post->ID,
			$language->slug
		);

		if ( empty( $duplicated_post_id ) ) {
			return new WP_Error(
				'pll_rest_duplication_failed',
				__( 'Failed to duplicate the post.', 'polylang-pro' ),
				array( 'status' => 500 )
			);
		}

		/** @var WP_Post $duplicated_post */
		$duplicated_post = get_post( $duplicated_post_id );

		return $this->prepare_item_for_response(
			$duplicated_post,
			$request
		);
	}

	/**
	 * Checks if a user has permission to duplicate a post.
	 *
	 * @since 3.8
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool True if the current user has permission, false otherwise.
	 *
	 * @phpstan-template T of array
	 * @phpstan-param WP_REST_Request<T> $request
	 */
	public function create_item_permissions_check( $request ) {
		$language = $this->model->languages->get( $request->get_param( 'lang' ) );

		if ( empty( $language ) ) {
			return false;
		}

		return current_user_can( 'edit_posts' )
			&& current_user_can( 'read_post', $request->get_param( 'from_post' ) )
			&& Capabilities::get_user()->can_translate( $language );
	}

	/**
	 * Retrieves an array of endpoint arguments from the item schema for the controller.
	 *
	 * @since 3.8
	 *
	 * @param string $method The request method.
	 * @return array Endpoint arguments.
	 */
	public function get_endpoint_args_for_item_schema( $method = WP_REST_Server::CREATABLE ) {
		if ( WP_REST_Server::CREATABLE !== $method ) {
			return array();
		}

		return parent::get_endpoint_args_for_item_schema( $method );
	}

	/**
	 * Retrieves the item schema for the controller.
	 *
	 * @since 3.8
	 *
	 * @return array Item schema.
	 */
	public function get_item_schema(): array {
		if ( $this->schema ) {
			return $this->add_additional_fields_schema( $this->schema );
		}

		$this->schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'translation',
			'type'       => 'object',
			'properties' => array(
				'from_post' => array(
					'type'        => 'integer',
					'required'    => true,
					'description' => __( 'The ID of the post to translate.', 'polylang-pro' ),
				),
				'lang'      => array(
					'type'        => 'string',
					'enum'        => $this->model->languages->get_list( array( 'fields' => 'slug' ) ),
					'required'    => true,
					'description' => __( 'The language to translate the post to.', 'polylang-pro' ),
				),
				'action'    => array(
					'type'        => 'string',
					'enum'        => array( 'duplicate' ),
					'required'    => true,
					'description' => __( 'The action to perform.', 'polylang-pro' ),
				),
			),
		);

		return $this->add_additional_fields_schema( $this->schema );
	}

	/**
	 * Prepares an item for the response.
	 *
	 * @since 3.8
	 *
	 * @param WP_Post         $item The item to prepare.
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response|WP_Error The prepared item.
	 *
	 * @phpstan-template T of array
	 * @phpstan-param WP_REST_Request<T> $request
	 */
	public function prepare_item_for_response( $item, $request ) {
		$post_type_object = get_post_type_object( $item->post_type );
		$posts_controller = $post_type_object ? $post_type_object->get_rest_controller() : null;

		if ( ! $posts_controller instanceof WP_REST_Controller ) {
			$posts_controller = new WP_REST_Posts_Controller( $item->post_type );
		}

		if ( 'wp_template_part' === $item->post_type ) {
			// `WP_REST_Templates_Controller::prepare_item_for_response()` expects a `WP_Block_Template` object.
			$item = _build_block_template_result_from_post( $item );
		}

		$response = $posts_controller->prepare_item_for_response( $item, $request );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response->set_status( 201 );

		return $response;
	}
}
