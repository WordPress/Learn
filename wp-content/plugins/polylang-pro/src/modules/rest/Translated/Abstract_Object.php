<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\REST\Translated;

use WP_Syntex\Polylang_Pro\REST\Translatable\Abstract_Object as Abstract_Translatable_Object;

/**
 * Abstract class to expose posts (or terms) language and translations in the REST API
 *
 * @since 2.2
 */
abstract class Abstract_Object extends Abstract_Translatable_Object {
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
	 * Returns the object translations.
	 *
	 * Cast to `stdClass` to ensure `json_encode` always returns a JSON object `{}`
	 * rather than a JSON array `[]` when there are no translations.
	 *
	 * @since 2.2
	 *
	 * @param array $object Post or Term array.
	 * @return object
	 */
	public function get_translations( $object ) {
		return (object) $this->model->{$this->get_type()}->get_translations( $this->get_rest_id( $object ) );
	}

	/**
	 * Save translations.
	 *
	 * @since 2.2
	 *
	 * @param array  $translations Array of translations with language codes as keys and object ids as values.
	 * @param object $object       Instance of WP_Post or WP_Term.
	 * @return bool
	 */
	public function save_translations( $translations, $object ) {
		$object_language = $this->model->{$this->get_type()}->get_language( $this->get_db_id( $object ) );

		if ( ! $object_language ) {
			return false;
		}

		$expected = array_merge(
			array( $object_language->slug => $this->get_db_id( $object ) ),
			$translations
		);


		$new_translations = $this->model->{$this->get_type()}->save_translations( $this->get_db_id( $object ), $translations );

		/* Use loose comparison to avoid ordering issues */
		return $new_translations == $expected; // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
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
		parent::register_rest_field( $type );

		register_rest_field(
			$this->get_rest_field_type( $type ),
			'translations',
			array(
				'get_callback'    => array( $this, 'get_translations' ),
				'update_callback' => array( $this, 'save_translations' ),
				'schema'          => $this->get_translations_schema(),
			)
		);
	}

	/**
	 * Returns the schema for the translations REST field.
	 *
	 * @since 3.8
	 *
	 * @return array JSON schema.
	 */
	private function get_translations_schema(): array {
		return array(
			'description' => __( 'Translations of the item.', 'polylang-pro' ),
			'type'        => 'object',
			'properties'  => array_fill_keys(
				$this->model->get_languages_list( array( 'fields' => 'slug' ) ),
				array(
					'type'    => 'integer',
					'minimum' => 1,
				)
			),
		);
	}
}
