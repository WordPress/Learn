<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\REST\Translated;

use PLL_REST_API;
use WP_REST_Controller;
use WP_Syntex\Polylang_Pro\REST\Translated\Abstract_Object;

/**
 * Expose terms language and translations in the REST API.
 *
 * @since 3.8
 */
class Term extends Abstract_Object {
	/**
	 * Constructor.
	 *
	 * @since 2.2
	 *
	 * @param PLL_REST_API $rest_api      Instance of PLL_REST_API.
	 * @param array        $content_types Array of arrays with taxonomies as keys and options as values.
	 */
	public function __construct( PLL_REST_API $rest_api, array $content_types ) {
		parent::__construct( $rest_api, $content_types );

		add_filter( 'pre_term_slug', array( $this, 'pre_term_slug' ), 5, 2 );
	}

	/**
	 * Creates the term slug in case the term already exists in another language
	 * to allow it to share the same slugs as terms in other languages.
	 *
	 * @since 3.2
	 *
	 * @param string $slug     The inputted slug of the term being saved, may be empty.
	 * @param string $taxonomy The term taxonomy.
	 * @return string
	 */
	public function pre_term_slug( $slug, $taxonomy ) {
		if ( ! $this->model->is_translated_taxonomy( $taxonomy ) ) {
			return $slug;
		}

		$attributes = $this->request->get_attributes();
		if ( empty( $attributes ) ) {
			return $slug;
		}

		$callback = $attributes['callback'];
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

		$lang = $this->request->get_language();
		$id   = $this->request->get_id();
		if ( empty( $lang ) && ! empty( $id ) ) { // Update.
			$post_lang = $this->model->term->get_language( $id );
			if ( ! empty( $post_lang ) ) {
				$lang = $post_lang;
			}
		}

		if ( ! empty( $lang ) ) {
			$parent   = $this->request->get_param( 'parent' );
			$parent   = is_numeric( $parent ) ? (int) $parent : 0;
			$name     = $this->request->get_param( 'name' ) ?? '';
			$new_slug = $this->request->get_param( 'slug' ) ?? '';

			if ( empty( $new_slug ) && empty( $id ) && ! empty( $name ) && is_string( $name ) ) {
				// The term is created without specifying the slug.
				$slug = $name;
			} elseif ( ! empty( $new_slug ) && is_string( $new_slug ) && false === strpos( $new_slug, '___' ) ) {
				// The term is created or updated and the slug is specified.
				$slug = $new_slug;
			}

			if ( ! empty( $slug ) && is_string( $slug ) && ! $this->model->term_exists_by_slug( $slug, $lang, $taxonomy, $parent ) ) {
				$slug = sanitize_title( $slug . '___' . $lang->slug );
			}
		}

		return $slug;
	}

	/**
	 * Returns the current object type, e.g. 'term'.
	 *
	 * @since 3.8
	 *
	 * @return string
	 */
	protected function get_type(): string {
		return 'term';
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
			return $item['term_id'] ?? 0;
		}

		return $item->term_id ?? 0;
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
}
