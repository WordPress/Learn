<?php
/**
 * @package Polylang-Pro
 */

/**
 * Expose terms language and translations in the REST API
 *
 * @since 2.2
 */
class PLL_REST_Post extends PLL_REST_Translated_Object {
	/**
	 * @var PLL_Filters_Sanitization
	 */
	public $filters_sanitization;

	/**
	 * Constructor
	 *
	 * @since 2.2
	 *
	 * @param PLL_REST_API $rest_api      Instance of PLL_REST_API.
	 * @param array        $content_types Array of arrays with post types as keys and options as values.
	 */
	public function __construct( &$rest_api, $content_types ) {
		parent::__construct( $rest_api, $content_types );

		$this->type           = 'post';
		$this->setter_id_name = 'ID';

		add_action( 'parse_query', array( $this, 'parse_query' ), 1 );
		add_action( 'add_attachment', array( $this, 'set_media_language' ) );

		foreach ( array_keys( $content_types ) as $post_type ) {
			add_filter( "rest_prepare_{$post_type}", array( $this, 'prepare_response' ), 10, 3 );
		}

		add_filter( 'rest_pre_dispatch', array( $this, 'save_language_and_translations' ), 10, 3 );
		add_filter( 'rest_pre_dispatch', array( $this, 'register_rest_translation_table_field' ), 10, 3 );
		// Use rest_pre_dispatch_filter to get the right language locale and initialize correctly sanitization filters.
		add_filter( 'rest_pre_dispatch', array( $this, 'set_filters_sanitization' ), 10, 3 );
	}

	/**
	 * Filters the query per language according to the 'lang' parameter from the REST request.
	 *
	 * @since 2.6.9
	 *
	 * @param WP_Query $query WP_Query object.
	 * @return void
	 */
	public function parse_query( $query ) {
		if ( $this->can_filter_query( $query ) ) {
			$pll_query = new PLL_Query( $query, $this->model );
			$pll_query->query->set( 'lang', $this->request['lang'] ); // Set query vars "lang" with the REST parameter value; fix #405 and #384
			$pll_query->filter_query( $this->model->get_language( $this->request['lang'] ) ); // fix #493
		}
	}

	/**
	 * Tells whether or not the given query is filterable by language.
	 *
	 * @since 3.2
	 *
	 * @param WP_Query $query The query to check.
	 * @return bool True if filterable by language. False if the query is already filtered,
	 *                   no language has been passed in the request or the post type is not supported.
	 */
	protected function can_filter_query( $query ) {
		$query_post_types           = ! empty( $query->query['post_type'] ) ? (array) $query->query['post_type'] : array( 'post' );
		$allowed_post_types         = array_keys( $this->content_types );
		$allowed_queried_post_types = array_intersect( $query_post_types, $allowed_post_types );

		return empty( $query->get( 'lang' ) ) && ! empty( $this->request['lang'] ) && ! empty( $allowed_queried_post_types );
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
	 */
	public function prepare_response( $response, $post, $request ) {
		global $wpdb;

		if ( ! in_array( $request->get_method(), array( 'POST', 'PUT' ), true ) ) {
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

		if ( is_string( $lang ) ) {
			$this->model->post->set_language( (int) $id, $lang );
		}

		if ( is_array( $translations ) ) {
			$this->save_translations( $translations, get_post( (int) $id ) );
		}

		return $result;
	}

	/**
	 * Registers the `translations_table` REST field only for block editor requests.
	 *
	 * @since 3.4
	 *
	 * @param mixed           $result  Response to replace the requested version with.
	 * @param WP_REST_Server  $server  Server instance.
	 * @param WP_REST_Request $request Request used to generate the response.
	 * @return mixed
	 */
	public function register_rest_translation_table_field( $result, $server, $request ) {
		if (
			! current_user_can( 'edit_posts' )
			|| ! $this->is_allowed_namespace( $request->get_route() )
			|| ! pll_is_edit_rest_request( $request )
			) {
			return $result;
		}

		foreach ( array_keys( $this->content_types ) as $post_type ) {
			register_rest_field(
				$this->get_rest_field_type( $post_type ),
				'translations_table',
				array(
					'get_callback' => array( $this, 'get_translations_table' ),
					'schema'       => array(
						'translations_table' => __( 'Translations table', 'polylang-pro' ),
						'type'               => 'object',
					),
				)
			);
		}

		return $result;
	}

	/**
	 * Initialize sanitization filters with the correct language locale.
	 *
	 * @see WP_REST_Server::dispatch()
	 *
	 * @since 2.9
	 *
	 * @param mixed           $result  Response to replace the requested version with. Can be anything
	 *                                 a normal endpoint can return, or null to not hijack the request.
	 * @param WP_REST_Server  $server  Server instance.
	 * @param WP_REST_Request $request Request used to generate the response.
	 * @return mixed
	 */
	public function set_filters_sanitization( $result, $server, $request ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return $result;
		}

		$id   = $request->get_param( 'id' );
		$lang = $request->get_param( 'lang' );

		if ( is_string( $lang ) && ! empty( $lang ) ) {
			$language = $this->model->get_language( sanitize_key( $lang ) );
		} elseif ( is_numeric( $id ) && ! empty( $id ) ) {
			// Otherwise we need to get the language from the post itself.
			$language = $this->model->post->get_language( (int) $id );
		}

		if ( ! empty( $language ) ) {
			$this->filters_sanitization = new PLL_Filters_Sanitization( $language->locale );
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
		$post_type_pattern = '#(' . implode( '|', array_values( $post_type_rest_bases ) ) . ')/' . $request->get_param( 'id' ) . '#';
		return preg_match( "$post_type_pattern", $request->get_route() ) && 'PUT' === $request->get_method();
	}

	/**
	 * Returns the post translations table
	 *
	 * @since 2.6
	 *
	 * @param array $object Post array.
	 * @return array
	 */
	public function get_translations_table( $object ) {
		$return = array();

		// When we come from a post new creation
		$from_post_id = $this->get_from_post_id();

		foreach ( $this->model->get_languages_list() as $language ) {
			// If the request isn't from a source post creation, then get the translated post in the correct language.
			if ( ! empty( $from_post_id ) ) {
				$tr_id = $this->model->post->get( $from_post_id, $language );
			} else {
				$tr_id = (int) $this->model->post->get_translation( $object[ $this->getter_id_name ], $language );
			}

			$return[ $language->slug ] = $this->get_translation_table_data( $object[ $this->getter_id_name ], $tr_id, $language );

			/**
			 * Filters the REST translations table.
			 *
			 * @since 2.6
			 *
			 * @param array        $row      Datas in a translations table row
			 * @param int          $id       Source post id.
			 * @param PLL_Language $language Translation language
			 */
			$return = apply_filters( 'pll_rest_translations_table', $return, $object[ $this->getter_id_name ], $language );
		}

		return $return;
	}

	/**
	 * Generates links, language information and translated posts for a given language into a translation table.
	 *
	 * @since 3.2
	 *
	 * @param int          $id       The id of the existing post to get datas for the translations table element.
	 * @param int          $tr_id    The id of the translated post for the given language if exists.
	 * @param PLL_Language $language The given language object.
	 * @return array The translation data of the given language.
	 */
	public function get_translation_table_data( $id, $tr_id, $language ) {
		$translation_data = array(
			'lang'            => $language,
			'caps'            => array(
				'add'    => false,
				'edit'   => false,
				'delete' => false,
			),
			'links'           => array(
				'add_link' => '',
			),
			'site_editor'     => array(
				'edit_link' => '',
			),
			'block_editor'    => array(
				'edit_link' => '',
			),
			'translated_post' => array(),
		);

		// When no post exist in DB, we need to return a non-empty value in the add_link item.
		$post_type = get_post_type( $id );
		if ( ! empty( $post_type ) ) {
			$type = get_post_type_object( $post_type );
			$translation_data['caps']['add'] = ! empty( $type ) && current_user_can( $type->cap->create_posts );
			if ( $translation_data['caps']['add'] ) {
				$translation_data['links']['add_link'] = $this->links->get_new_post_translation_link( $id, $language, 'keep ampersand' );
			}
		}

		// If a translation of the given post exist in the desired language, then we can add the edit link and the translated post information.
		if ( ! empty( $tr_id ) ) {
			$translation_data['caps']['edit'] = current_user_can( 'edit_post', $tr_id );
			if ( $translation_data['caps']['edit'] ) {
				$translation_data['site_editor']['edit_link']  = $this->get_site_editor_edit_post_link( $tr_id );
				$translation_data['block_editor']['edit_link'] = (string) get_edit_post_link( $tr_id, 'keep ampersand' );
			}

			// Verify the user can delete post to add the delete link.
			$translation_data['caps']['delete'] = current_user_can( 'delete_post', $tr_id );

			$translated_post = get_post( $tr_id, ARRAY_A );
			$translation_data['translated_post'] = array(
				'id'    => $translated_post['ID'],
				'title' => $translated_post['post_title'],
			);
		}

		return $translation_data;
	}

	/**
	 * Returns the post id of the post that we come from to create a translation.
	 *
	 * @since 3.2
	 * @since 3.4.5 Returns the source post ID sooner for a REST request.
	 *
	 * @return int The post id of the original post.
	 */
	public function get_from_post_id() {
		if ( $this->request instanceof WP_REST_Request ) {
			$from_post = $this->request->get_param( 'from_post' );

			if ( ! empty( $from_post ) ) {
				return is_int( $from_post ) ? $from_post : 0;
			}
		}

		return isset( $_GET['from_post'] ) ? (int) $_GET['from_post'] : 0; // phpcs:ignore WordPress.Security.NonceVerification
	}

	/**
	 * Assigns the language to the edited media.
	 *
	 * When a media is edited in the block image, a new media is created and we need to set the language from the original one.
	 *
	 * @see https://make.wordpress.org/core/2020/07/20/editing-images-in-the-block-editor/ the new WordPress 5.5 feature: Editing Images in the Block Editor.
	 *
	 * @since 2.8
	 *
	 * @param int $post_id Post id.
	 * @return void
	 */
	public function set_media_language( $post_id ) {
		if ( empty( $this->request['id'] ) || $post_id === $this->request['id'] ) {
			return;
		}
		$lang = $this->model->post->get_language( intval( $this->request['id'] ) );
		if ( ! empty( $lang ) ) {
			$this->model->post->set_language( $post_id, $lang );
		}
	}

	/**
	 * Returns edit post link for site editor.
	 *
	 * @since 3.4.5
	 *
	 * @param int $post_id ID of the post to get edit link from.
	 * @return string|null The edit post link for the given post. Null if none found.
	 */
	protected function get_site_editor_edit_post_link( $post_id ) {
		$post_type = (string) get_post_type( $post_id );
		if ( empty( $post_type ) ) {
			return (string) get_edit_post_link( $post_id, 'keep ampersand' );
		}

		return add_query_arg(
			array(
				'postId'   => $post_id,
				'postType' => $post_type,
				'canvas'   => 'edit',
			),
			admin_url( 'site-editor.php' )
		);
	}

	/**
	 * Tells whether or not the given route is in an allowed namespace for the `translation_table` REST field.
	 *
	 * @since 3.5
	 *
	 * @param string $route The route to check.
	 * @return bool True if in an allowed namespace, false otherwise.
	 */
	protected function is_allowed_namespace( $route ) {
		return (bool) preg_match( '@^/wp/v2/@', $route );
	}
}
