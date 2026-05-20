<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Integrations\ACF\Strategy;

use Translation_Entry;
use Translations;
use WP_Syntex\Polylang_Pro\Modules\Import_Export\Services\Context;
use WP_Syntex\Polylang_Pro\Integrations\ACF\Entity\Abstract_Object;
/**
 * This class is part of the ACF compatibility.
 * Import strategy.
 * Saves the custom fields value from translated data objects (e.g. DeepL or XLIFF).

 * @since 3.7
 */
class Import extends Copy {

	/**
	 * Translations set where to look for the post custom fields translations.
	 *
	 * @var Translations
	 */
	protected $translations;

	/**
	 * Constructor.
	 *
	 * @since 3.7
	 *
	 * @param Translations $translations A set of translations to search the custom fields translations in.
	 * @return void
	 */
	public function __construct( Translations $translations ) {
		$this->translations = $translations;
	}

	/**
	 * Applies the translation strategy.
	 *
	 * Depending on the type of fields, this will copy a layout and
	 * auto-translate object ids and translated custom fields.
	 *
	 * @since 3.7
	 *
	 * @param Abstract_Object $object ACF object.
	 * @param mixed           $value  Custom field value of the source object.
	 * @param array           $field  Custom field definition.
	 * @param array           $args   {
	 *     Array of arguments.
	 *
	 *     @type mixed $original_value Optional. The translated or default value of the field, if any.
	 * }
	 * @return mixed Custom field value of the target object.
	 */
	public function execute( Abstract_Object $object, $value, array $field, array $args = array() ) {
		$args = wp_parse_args( $args, array( 'original_value' => null ) );

		if ( ! $this->can_execute( $field ) ) {
			return $args['original_value'];
		}

		if ( empty( $value ) ) {
			return $value;
		}

		if ( ! is_string( $value ) ) {
			return parent::execute( $object, $value, $field, $args );
		}

		$args['original_value'] = is_string( $args['original_value'] ) ? $args['original_value'] : '';

		$entry = new Translation_Entry(
			array(
				'singular' => $value,
				'context'  => Context::to_string(
					array(
						Context::FIELD => 'acf',
						Context::ID    => $this->get_field_key( $field ),
					)
				),
			)
		);

		/*
		 * Use `translate_entry()` to know whether the entry is in the translation set or not.
		 * Because `translate()` doesn't return false but the source string if the entry doesn't exist.
		 */
		if ( ! $this->translations->translate_entry( $entry ) ) {
			// The entry is not in the translation set.

			if ( 'translate_once' === $field['translations'] && ! empty( $args['original_value'] ) ) {
				/*
				 * If there is no entry in the translation set and the field is set to `translate_once`,
				 * it means that it has already been translated.
				 */
				return $this->is_field_default_value( $value, $field, $args )
					? $this->get_translated_default_value( $field, $args['target_language'] )
					: $args['original_value'];
			}

			if ( $this->is_field_default_value( $value, $field, $args ) ) {
				return $this->get_translated_default_value( $field, $args['target_language'] );
			}
			return parent::execute( $object, $value, $field, $args );
		}

		$value = $this->translations->translate( $value, $entry->context );

		return parent::execute(
			$object,
			$value,
			$field,
			$args
		);
	}
}
