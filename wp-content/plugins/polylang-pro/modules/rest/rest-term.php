<?php
/**
 * @package Polylang-Pro
 */

/**
 * Expose terms language and translations in the REST API.
 *
 * @since 2.2
 */
class PLL_REST_Term extends PLL_REST_Translated_Object {

	/**
	 * Constructor.
	 *
	 * @since 2.2
	 *
	 * @param PLL_REST_API $rest_api      Instance of PLL_REST_API.
	 * @param array        $content_types Array of arrays with taxonomies as keys and options as values.
	 */
	public function __construct( &$rest_api, $content_types ) {
		parent::__construct( $rest_api, $content_types );

		$this->type           = 'term';
		$this->setter_id_name = 'term_id';

		add_filter( 'get_terms_args', array( $this, 'get_terms_args' ) );
		add_filter( 'pre_term_slug', array( $this, 'pre_term_slug' ), 5, 2 );
	}

	/**
	 * Filters the query per language according to the 'lang' parameter.
	 *
	 * @since 2.6.9
	 *
	 * @param array $args WP_Term_Query arguments.
	 * @return array
	 */
	public function get_terms_args( $args ) {
		if ( isset( $args['lang'] ) ) {
			return $args;
		}

		// The first test is necessary to avoid an infinite loop when calling get_languages_list().
		if ( $this->model->is_translated_taxonomy( $args['taxonomy'] ) && isset( $this->request['lang'] ) && in_array( $this->request['lang'], $this->model->get_languages_list( array( 'fields' => 'slug' ) ) ) ) {
			$args['lang'] = $this->request['lang'];
		}

		return $args;
	}

	/**
	 * Get the rest field type for a content type.
	 *
	 * @since 2.3.11
	 *
	 * @param string $type Taxonomy name.
	 * @return string REST API field type.
	 */
	protected function get_rest_field_type( $type ) {
		// Handles the specific case for tags
		return 'post_tag' === $type ? 'tag' : $type;
	}

	/**
	 * Creates the term slug in case the term already exists in another language
	 * to allow it to share the same slugs as terms in other languages.
	 *
	 * @since 3.2
	 *
	 * @param string $slug     The inputed slug of the term being saved, may be empty.
	 * @param string $taxonomy The term taxonomy.
	 * @return string
	 */
	public function pre_term_slug( $slug, $taxonomy ) {
		if ( ! isset( $this->request ) || ! $this->model->is_translated_taxonomy( $taxonomy ) ) {
			return $slug;
		}

		$attributes = $this->request->get_attributes();
		$callback   = $attributes['callback'];

		if ( ! is_array( $callback ) ) {
			return $slug;
		}

		$controller = $callback[0];
		if ( ! $controller instanceof WP_REST_Controller ) {
			return $slug;
		}

		$schema = $controller->get_item_schema();

		if ( $schema['title'] !== $this->get_rest_field_type( $taxonomy ) ) {
			return $slug;
		}

		if ( ! empty( $this->request['lang'] ) ) {
			$lang = $this->request['lang'];
		} elseif ( ! empty( $this->request['id'] ) && is_numeric( $this->request['id'] ) ) { // Update.
			$post_lang = $this->model->term->get_language( (int) $this->request['id'] );
			if ( ! empty( $post_lang ) ) {
				$lang = $post_lang->slug;
			}
		}

		if ( ! empty( $lang ) ) {
			$parent = isset( $this->request['parent'] ) ? $this->request['parent'] : 0;

			if ( empty( $this->request['slug'] ) && empty( $this->request['id'] ) && ! empty( $this->request['name'] ) ) {
				// The term is created without specifying the slug.
				$slug = $this->request['name'];
			} elseif ( ! empty( $this->request['slug'] ) && false === strpos( '___', $this->request['slug'] ) ) {
				// The term is created or updated and the slug is specified.
				$slug = $this->request['slug'];
			}

			if ( ! empty( $slug ) && ! $this->model->term_exists_by_slug( $slug, $lang, $taxonomy, $parent ) ) {
				$slug = sanitize_title( $slug . '___' . $lang );
			}
		}

		return $slug;
	}
}
