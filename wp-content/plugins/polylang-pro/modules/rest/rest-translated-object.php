<?php
/**
 * @package Polylang-Pro
 */

/**
 * Abstract class to expose posts (or terms) language and translations in the REST API
 *
 * @since 2.2
 */
abstract class PLL_REST_Translated_Object extends PLL_REST_Filtered_Object {
	/**
	 * @var PLL_Admin_Links
	 */
	public $links;

	/**
	 * How is named the object id, typically 'ID' for posts and 'term_id' for terms.
	 * Mainly used in the object to save it in the database.
	 * Must be defined by the child class.
	 *
	 * @since 3.2 Property renamed from id.
	 *
	 * @var string
	 */
	protected $setter_id_name;

	/**
	 * How is named the object id in the returned array from the REST API, typically 'id' for posts and terms and 'wp_id' for template and template parts.
	 * Must be defined by the child class if different of the default 'id' value.
	 *
	 * @since 3.2
	 *
	 * @var string
	 */
	protected $getter_id_name = 'id';

	/**
	 * Constructor
	 *
	 * @since 2.2
	 * @since 2.2.1 $content_types is an array of arrays
	 * @since 2.6   The first parameter is an instance of PLL_REST_API instead of PLL_Model
	 *
	 * @param object $rest_api      Instance of PLL_REST_API.
	 * @param array  $content_types Array of arrays with post types or taxonomies as keys and options as values.
	 *                              The possible options are:
	 *                              filters:      whether to filter queries, defaults to true.
	 *                              lang:         whether to return the language in the response, defaults to true.
	 *                              translations: whether to return the translations in the response, defaults to true.
	 */
	public function __construct( &$rest_api, $content_types ) {
		parent::__construct( $rest_api, $content_types );

		$this->links = &$rest_api->links;

		foreach ( $content_types as $type => $args ) {
			$args = wp_parse_args( $args, array_fill_keys( array( 'lang', 'translations' ), true ) );

			if ( $args['lang'] ) {
				register_rest_field(
					$this->get_rest_field_type( $type ),
					'lang',
					array(
						'get_callback'    => array( $this, 'get_language' ),
						'update_callback' => array( $this, 'set_language' ),
						'schema'          => array(
							'lang' => __( 'Language', 'polylang-pro' ),
							'type' => 'string',
						),
					)
				);
			}

			if ( $args['translations'] ) {
				register_rest_field(
					$this->get_rest_field_type( $type ),
					'translations',
					array(
						'get_callback'    => array( $this, 'get_translations' ),
						'update_callback' => array( $this, 'save_translations' ),
						'schema'          => array(
							'translations' => __( 'Translations', 'polylang-pro' ),
							'type'         => 'object',
						),
					)
				);
			}
		}
	}

	/**
	 * Returns the object language.
	 *
	 * @since 2.2
	 *
	 * @param array $object Post or Term array.
	 * @return string|false Language slug. False if no language is assigned to the object.
	 */
	public function get_language( $object ) {
		$language = $this->model->{$this->type}->get_language( $object[ $this->getter_id_name ] );
		return empty( $language ) ? false : $language->slug;
	}

	/**
	 * Sets the object language.
	 *
	 * @since 2.2
	 *
	 * @param string $lang   Language code.
	 * @param object $object Instance of WP_Post or WP_Term.
	 * @return bool
	 */
	public function set_language( $lang, $object ) {
		if ( isset( $object->{$this->setter_id_name} ) ) { // Test to avoid a warning with WooCommerce
			$this->model->{$this->type}->set_language( $object->{$this->setter_id_name}, $lang );
		}
		return true;
	}

	/**
	 * Returns the object translations.
	 *
	 * @since 2.2
	 *
	 * @param array $object Post or Term array.
	 * @return array
	 */
	public function get_translations( $object ) {
		return $this->model->{$this->type}->get_translations( $object[ $this->getter_id_name ] );
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
		if ( isset( $object->{$this->setter_id_name} ) ) { // Test to avoid a warning with WooCommerce.
			$this->model->{$this->type}->save_translations( $object->{$this->setter_id_name}, $translations );
		}
		return true;
	}
}
