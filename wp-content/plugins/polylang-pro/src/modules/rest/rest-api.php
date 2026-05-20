<?php
/**
 * @package Polylang-Pro
 */

use WP_Syntex\Polylang_Pro\REST\V1\Translation;
use WP_Syntex\Polylang_Pro\REST\V1\Untranslated_Posts;
use WP_Syntex\Polylang_Pro\REST\Translated\Post as Translated_Post;
use WP_Syntex\Polylang_Pro\REST\Translated\Term as Translated_Term;
use WP_Syntex\Polylang_Pro\REST\Translated\Attachment as Translated_Attachment;
use WP_Syntex\Polylang_Pro\REST\Translated\Template as Translated_Template;
use WP_Syntex\Polylang_Pro\REST\Filtered\Comment as Filtered_Comment;
use WP_Syntex\Polylang_Pro\REST\Filtered\Post as Filtered_Post;
use WP_Syntex\Polylang_Pro\REST\Filtered\Term as Filtered_Term;
use WP_Syntex\Polylang_Pro\REST\Filtered\Template as Filtered_Template;

/**
 * Setup the REST API endpoints and filters.
 *
 * @since 2.2
 */
class PLL_REST_API {
	/**
	 * @var Translated_Post
	 */
	public $post;

	/**
	 * @var Translated_Term
	 */
	public $term;

	/**
	 * @var Translated_Attachment
	 */
	public $attachment;

	/**
	 * @var Translated_Template|null
	 */
	public $template;

	/**
	 * @var Filtered_Comment
	 */
	public $filtered_comment;

	/**
	 * @var Filtered_Post
	 */
	public $filtered_post;

	/**
	 * @var Filtered_Term
	 */
	public $filtered_term;

	/**
	 * @var Filtered_Template
	 */
	public $filtered_template;

	/**
	 * @var Untranslated_Posts
	 */
	public $untranslated_posts;

	/**
	 * @var Translation
	 */
	public $translation;

	/**
	 * @var \PLL_Admin_Links|null
	 */
	public $links;

	/**
	 * @var \PLL_Model
	 */
	public $model;

	/**
	 * @var \WP_Syntex\Polylang\REST\Request
	 */
	public $request;

	/**
	 * Constructor.
	 *
	 * @since 2.2
	 *
	 * @param \PLL_Base $polylang Instance of `\PLL_Base`.
	 */
	public function __construct( &$polylang ) {
		$this->links   = &$polylang->links;
		$this->model   = &$polylang->model;
		$this->request = &$polylang->request;

		$post_types = array_diff(
			array_intersect(
				$this->model->get_translated_post_types(),
				get_post_types( array( 'show_in_rest' => true ) )
			),
			array( 'attachment' )
		);

		/**
		 * Filters post types passed to Translated_Post constructor.
		 *
		 * @since 2.2.1
		 * @since 3.8 The legacy format with options as arrays is deprecated. Use an indexed array of strings instead.
		 *
		 * @param array $post_types An indexed array of post type names.
		 */
		$post_types = apply_filters( 'pll_rest_api_post_types', $post_types );
		$post_types = $this->sanitize_translatable_types( $post_types );
		$this->post = new Translated_Post( $this, $post_types );

		$taxonomies = array_intersect(
			$this->model->get_translated_taxonomies(),
			get_taxonomies( array( 'show_in_rest' => true ) )
		);

		/**
		 * Filters taxonomies passed to Translated_Term constructor.
		 *
		 * @since 2.2.1
		 * @since 3.8 The legacy format with options as arrays is deprecated. Use an indexed array of strings instead.
		 *
		 * @param array $taxonomies An indexed array of taxonomy names.
		 */
		$taxonomies = apply_filters( 'pll_rest_api_taxonomies', $taxonomies );
		$taxonomies = $this->sanitize_translatable_types( $taxonomies );
		$this->term = new Translated_Term( $this, $taxonomies );

		if ( $this->model->options->get( 'media_support' ) ) {
			$this->attachment = new Translated_Attachment( $this );
		}

		$this->filtered_comment  = new Filtered_Comment( $this );
		$this->filtered_post     = new Filtered_Post( $this, array_keys( $post_types ) );
		$this->filtered_term     = new Filtered_Term( $this, array_keys( $taxonomies ) );
		$this->filtered_template = new Filtered_Template( $this );

		$this->untranslated_posts = new Untranslated_Posts( $this->model, $post_types );
		$this->untranslated_posts->register_routes();

		$this->translation = new Translation( $polylang );
		$this->translation->register_routes();
	}

	/**
	 * Sanitizes translatable types to ensure backward compatibility with legacy filter formats.
	 * Third-party plugins or users may still use the legacy format where options are passed as arrays.
	 *
	 * @since 3.8
	 *
	 * @param array $types Translatable types from filters (post types or taxonomies).
	 * @return string[] Sanitized types, associative array with type names as keys and values.
	 */
	private function sanitize_translatable_types( $types ): array {
		$sanitized = array();

		foreach ( $types as $type => $options ) {
			if ( is_string( $options ) ) {
				$sanitized[ $options ] = $options;
			} elseif ( is_string( $type ) && is_array( $options ) ) {
				_deprecated_argument(
					'pll_rest_api_post_types / pll_rest_api_taxonomies',
					'3.8',
					'Passing options as arrays for ' . esc_html( $type ) . ' is deprecated. These filters now use a simple array of post types or taxonomies.'
				);
				$sanitized[ $type ] = $type;
			} else {
				_doing_it_wrong(
					'pll_rest_api_post_types / pll_rest_api_taxonomies',
					'Invalid format. Expected an array of post types or taxonomies',
					'3.8'
				);
			}
		}

		return $sanitized;
	}
}
