<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\REST\Translatable;

use WP_Error;
use PLL_REST_API;
use WP_Syntex\Polylang\Capabilities\Capabilities;

/**
 * Abstract class to expose posts (or terms) language and translations in the REST API
 *
 * @since 2.2
 */
abstract class Abstract_Object {
	/**
	 * @var \PLL_Admin_Links|null
	 */
	public $links;

	/**
	 * @var \PLL_Model
	 */
	public $model;

	/**
	 * REST request stored for internal usage.
	 *
	 * @var \WP_Syntex\Polylang\REST\Request
	 */
	protected $request;

	/**
	 * Constructor
	 *
	 * @since 2.2
	 * @since 2.2.1 $content_types is an array of arrays
	 * @since 2.6   The first parameter is an instance of PLL_REST_API instead of PLL_Model
	 *
	 * @param PLL_REST_API $rest_api      Instance of PLL_REST_API.
	 * @param array        $content_types Array of arrays with post types or taxonomies as keys and options as values.
	 *                                    The possible options are:
	 *                                    filters:      whether to filter queries, defaults to true.
	 *                                    lang:         whether to return the language in the response, defaults to true.
	 *                                    translations: whether to return the translations in the response, defaults to true.
	 */
	public function __construct( PLL_REST_API $rest_api, array $content_types ) {
		$this->model   = $rest_api->model;
		$this->request = $rest_api->request;
		$this->links   = &$rest_api->links;

		foreach ( $content_types as $type ) {
			$this->register_rest_field( $type );
		}
	}

	/**
	 * Returns the current object type, e.g. 'post' or 'term'.
	 *
	 * @since 3.8
	 *
	 * @return string
	 */
	abstract protected function get_type(): string;

	/**
	 * Returns the database identifier for the item.
	 *
	 * @since 3.8
	 *
	 * @param array|object $item Item array or object, usually a post or term.
	 * @return int The database identifier, 0 if not found.
	 */
	abstract protected function get_db_id( $item ): int;

	/**
	 * Returns the object language.
	 *
	 * @since 2.2
	 *
	 * @param array $object Post or Term array.
	 * @return string|false Language slug. False if no language is assigned to the object.
	 */
	public function get_language( $object ) {
		$language = $this->model->{$this->get_type()}->get_language( $this->get_rest_id( $object ) );
		return empty( $language ) ? false : $language->slug;
	}

	/**
	 * Sets the object language.
	 *
	 * @since 2.2
	 *
	 * @param string $lang   Language code.
	 * @param object $object Instance of WP_Post or WP_Term.
	 * @return bool|\WP_Error True or false when setting the language, WP_Error on failure.
	 */
	public function set_language( $lang, $object ) {
		$language = $this->get_language_with_permission( $lang );
		if ( is_wp_error( $language ) ) {
			return $language;
		}

		return $this->model->{$this->get_type()}->set_language( $this->get_db_id( $object ), $language );
	}

	/**
	 * Get the rest field type for a content type.
	 *
	 * @since 2.3.11
	 *
	 * @param string $type Post type or taxonomy name.
	 * @return string REST API field type.
	 */
	protected function get_rest_field_type( $type ) {
		return $type;
	}

	/**
	 * Returns the REST identifier for the item.
	 *
	 * @since 3.8
	 *
	 * @param array|object $item Item array or object, usually a post or term.
	 * @return int The REST identifier, 0 if not found.
	 */
	protected function get_rest_id( $item ): int {
		if ( is_array( $item ) ) {
			return $item['id'] ?? 0;
		}

		return $item->id ?? 0;
	}

	/**
	 * Register the language REST field.
	 *
	 * @since 3.8
	 *
	 * @param string $type The type of the object.
	 * @return void
	 */
	protected function register_rest_field( string $type ): void {
		register_rest_field(
			$this->get_rest_field_type( $type ),
			'lang',
			array(
				'get_callback'    => array( $this, 'get_language' ),
				'update_callback' => array( $this, 'set_language' ),
				'schema'          => $this->get_language_schema(),
			)
		);
	}

	/**
	 * Returns the language object if the user has permission to set the language of the object.
	 *
	 * @since 3.8
	 *
	 * @param string $language_slug Language slug from the request.
	 * @return \PLL_Language|\WP_Error Language object if the language is valid, WP_Error otherwise.
	 */
	protected function get_language_with_permission( $language_slug ) {
		$language = $this->model->get_language( $language_slug );
		if ( ! $language ) {
			return new WP_Error(
				'rest_invalid_language_code',
				__( 'Invalid language code.', 'polylang-pro' ),
				array( 'status' => 400 )
			);
		}

		if ( ! Capabilities::get_user()->can_translate( $language ) ) {
			return new WP_Error(
				'rest_cannot_set_language',
				/* translators: %s is a native language name. */
				sprintf( __( 'You are not allowed to set the language of this item to %s.', 'polylang-pro' ), $language->name ),
				array( 'status' => 403 )
			);
		}

		return $language;
	}

	/**
	 * Returns the schema for the language REST field.
	 *
	 * @since 3.8
	 *
	 * @return array JSON schema.
	 */
	private function get_language_schema(): array {
		return array(
			'description' => __( 'Language of the item', 'polylang-pro' ),
			'type'        => 'string',
			'enum'        => $this->model->languages->get_list( array( 'fields' => 'slug' ) ),
		);
	}
}
