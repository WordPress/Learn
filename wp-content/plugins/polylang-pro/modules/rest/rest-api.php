<?php
/**
 * @package Polylang-Pro
 */

/**
 * Setup the REST API endpoints and filters
 *
 * @since 2.2
 */
class PLL_REST_API {
	/**
	 * @var PLL_REST_Post
	 */
	public $post;

	/**
	 * @var PLL_REST_Term
	 */
	public $term;

	/**
	 * @var PLL_FSE_REST_Template|null
	 */
	public $template;

	/**
	 * @var PLL_REST_Comment
	 */
	public $comment;

	/**
	 * @var PLL_Admin_Links
	 */
	public $links;

	/**
	 * @var PLL_Model
	 */
	public $model;

	/**
	 * List of translatable post types.
	 *
	 * @var array
	 */
	protected $post_types;

	/**
	 * Constructor.
	 *
	 * @since 2.2
	 *
	 * @param object $polylang Instance of PLL_REST_Request.
	 */
	public function __construct( &$polylang ) {
		$this->links = &$polylang->links;
		$this->model = &$polylang->model;
		add_action( 'rest_api_init', array( $this, 'init' ) );
	}

	/**
	 * Init filters and new endpoints.
	 *
	 * @since 2.2
	 *
	 * @return void
	 */
	public function init() {
		$this->post_types = array_fill_keys( array_intersect( $this->model->get_translated_post_types(), get_post_types( array( 'show_in_rest' => true ) ) ), array() );

		/**
		 * Filters post types and their options passed to PLL_Rest_Post contructor.
		 *
		 * @since 2.2.1
		 *
		 * @param array $post_types An array of arrays with post types as keys and options as values.
		 */
		$this->post_types = apply_filters( 'pll_rest_api_post_types', $this->post_types );
		$this->post = new PLL_REST_Post( $this, $this->post_types );

		$taxonomies = array_fill_keys( array_intersect( $this->model->get_translated_taxonomies(), get_taxonomies( array( 'show_in_rest' => true ) ) ), array() );

		/**
		 * Filters taxonomies and their options passed to PLL_Rest_Term constructor.
		 *
		 * @since 2.2.1
		 *
		 * @param array $taxonomies An array of arrays with taxonomies as keys and options as values.
		 */
		$taxonomies = apply_filters( 'pll_rest_api_taxonomies', $taxonomies );
		$this->term = new PLL_REST_Term( $this, $taxonomies );

		$this->comment = new PLL_REST_Comment( $this );

		register_rest_route(
			'pll/v1',
			'/languages',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_languages_list' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			'pll/v1',
			'/untranslated-posts',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_untranslated_posts' ),
				'permission_callback' => '__return_true',
				'args'                => $this->get_untranslated_posts_collection_params(),
			)
		);
	}

	/**
	 * Retrieves the query params for the untranslated posts collection.
	 *
	 * @since 2.6.0
	 *
	 * @return array Collection parameters.
	 */
	public function get_untranslated_posts_collection_params() {
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
				'required'    => true,
				'enum'        => array( 'edit' ),
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
	 * Returns a list of posts not translated in a language.
	 *
	 * @since 2.6.0
	 *
	 * @param WP_REST_Request $request REST API request.
	 * @return array
	 */
	public function get_untranslated_posts( WP_REST_Request $request ) {
		$return = array();

		$type            = $request->get_param( 'type' );
		$untranslated_in = $this->model->get_language( $request->get_param( 'untranslated_in' ) );
		$lang            = $this->model->get_language( $request->get_param( 'lang' ) );
		$search          = $request->get_param( 'search' );

		if ( ! is_string( $type ) || empty( $untranslated_in ) || empty( $lang ) ) {
			return array();
		}

		if ( ! is_string( $search ) ) {
			$search = '';
		}

		$untranslated_posts = $this->model->post->get_untranslated( $type, $untranslated_in, $lang, $search );

		// Add current translation in list.
		if ( $post_id = $this->model->post->get_translation( $request->get_param( 'include' ), $lang ) ) {
			$post = get_post( $post_id );
			if ( $post instanceof WP_Post ) {
				array_unshift( $untranslated_posts, $post );
			}
		}

		// Format output.
		foreach ( $untranslated_posts as $post ) {
			$values = array(
				'id'    => $post->ID,
				'title' => array(
					'raw'      => $post->post_title,
					'rendered' => get_the_title( $post->ID ),
				),
			);
			if ( pll_is_edit_rest_request( $request ) ) {
				$values['block_editor']['edit_link']    = get_edit_post_link( $post, 'keep ampersand' );
				$values['caps'] = array(
					'edit'   => current_user_can( 'edit_post', $post->ID ),
					'delete' => current_user_can( 'delete_post', $post->ID ),
				);
			}
			$return[] = $values;
		}

		return $return;
	}

	/**
	 * Returns the list of available languages specifying the default language.
	 *
	 * @since 3.2
	 *
	 * @return array[] List of PLL_Language objects.
	 * @phpstan-return array<int, array<string, mixed>>
	 */
	public function get_languages_list() {
		$languages                   = $this->model->get_languages_list();
		$languages_with_default_lang = array();

		foreach ( $languages as $language ) {
			$languages_with_default_lang[] = $language->to_array();
		}

		return array_values( $languages_with_default_lang );
	}
}
