<?php
/**
 * @package  Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Integrations\ACF\Entity;

use Translations;
use PLL_Language;
use PLL_Export_Data;
use WP_Syntex\Polylang_Pro\Integrations\ACF\Dispatcher;
use WP_Syntex\Polylang_Pro\Integrations\ACF\Strategy\Copy;
use WP_Syntex\Polylang_Pro\Integrations\ACF\Strategy\Export;
use WP_Syntex\Polylang_Pro\Integrations\ACF\Strategy\Import;
use WP_Syntex\Polylang_Pro\Integrations\ACF\Fields\Repeater;
use WP_Syntex\Polylang_Pro\Integrations\ACF\Strategy\Synchronize;
use WP_Syntex\Polylang_Pro\Integrations\ACF\Strategy\Abstract_Strategy;

/**
 * This class is part of the ACF compatibility.
 * Abstract class to handle objects such posts and terms.
 *
 * @since 3.7
 */
abstract class Abstract_Object implements Translatable_Entity_Interface {
	/**
	 * Stores fields to avoid reverse synchronization.
	 *
	 * @var string[]
	 */
	private static $updated = array();

	/**
	 * Object ID, could be a source or target.
	 *
	 * @var int
	 */
	private $id;

	/**
	 * Constructor
	 *
	 * @since 3.7
	 *
	 * @param int $id The object ID, default to 0.
	 */
	public function __construct( int $id = 0 ) {
		$this->id = $id;
	}

	/**
	 * Filters the field about to be rendered.
	 *
	 * @since 3.7
	 *
	 * @param array $field Custom field definition.
	 * @return array Custom field of the target object with a value.
	 */
	public function translate_rendered_field( $field ) {
		if ( empty( $_GET['new_lang'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return $field;
		}

		$lang = PLL()->model->get_language( sanitize_key( $_GET['new_lang'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( empty( $lang ) ) {
			return $field;
		}

		$from_id = $this->get_from_id_in_request();
		if ( empty( $from_id ) || $from_id === $this->get_id() ) {
			return $field;
		}

		$from_value     = Repeater::get_value( static::acf_id( $from_id ), $field );
		$original_value = $field['value'] ?? ( $field['default_value'] ?? null );
		$field['value'] = ( new Copy() )->execute(
			$this,
			$from_value,
			$field,
			array(
				'target_language' => $lang,
				'source_language' => PLL()->model->{$this->get_type()}->get_language( $from_id ),
				'original_value'  => $original_value,
			)
		);

		if ( ! empty( $field['value'] ) && Repeater::is_paginated( $field ) ) {
			add_filter( 'acf/prepare_field', array( Repeater::class, 'change_row_keys' ) );
		}

		return $field;
	}

	/**
	 * Updates the custom field value of the current object.
	 *
	 * @since 3.7
	 *
	 * @param mixed $value Custom field value of the source object.
	 * @param array $field Custom field definition.
	 * @return mixed Custom field value of the target object.
	 */
	public function update( $value, $field ) {
		// Avoid reverse sync.
		if ( in_array( $this->get_storage_key( $this->get_id(), $field['key'] ), self::$updated, true ) ) {
			return $value;
		}

		$strategy = new Synchronize( new Copy() );

		if ( ! $strategy->can_execute( $field ) ) {
			return $value;
		}

		$translations = PLL()->model->{$this->get_type()}->get_translations( $this->get_id() );

		$this->mark_subfields_as_updated( $field, array_values( $translations ) );

		foreach ( $translations as $lang => $tr_id ) {
			if ( $this->get_id() === $tr_id ) {
				continue;
			}

			/** @var PLL_Language */
			$lang = PLL()->model->get_language( $lang );

			self::$updated[] = $this->get_storage_key( $tr_id, $field['key'] );

			$acf_id   = static::acf_id( $tr_id );
			$tr_value = acf_get_value( $acf_id, $field );
			$tr_value = $strategy->execute(
				$this,
				$value,
				$field,
				array(
					'target_language' => $lang,
					'original_value'  => $tr_value,
					'target_id'       => $tr_id,
				)
			);

			if ( ! empty( $field['sub_fields'] ) && is_array( $tr_value ) && empty( $tr_value ) ) {
				/*
				 * The fields has subfields but they have been removed
				 * by `Abstract_Strategy::apply_on_subfield()`
				 * as they cannot be synchronized, so do not update.
				 */
				continue;
			}

			$tr_value = Repeater::reset_row_keys( $tr_value, $field );

			acf_update_value( $tr_value, $acf_id, $field );
		}

		return $value;
	}

	/**
	 * Recursively pre-marks all subfields of a container field as updated for all translation IDs.
	 *
	 * When ACF saves a container field, its `update_value()` hook calls `acf_update_value()`
	 * for each subfield individually, re-triggering our hook.
	 * This causes redundant sync operations and corrupts the ACF value store for translations that have not yet been
	 * processed by our main loop, shifting their `original_value` indexes.
	 *
	 * Pre-marking all subfield keys prevents these cascade calls from executing.
	 *
	 * @since 3.8.2
	 *
	 * @param array $field      The field definition.
	 * @param int[] $object_ids All object IDs in the translation group.
	 * @return void
	 */
	private function mark_subfields_as_updated( array $field, array $object_ids ): void {
		$sub_fields = $field['sub_fields'] ?? array();

		// Merge layouts' subfields into the main subfields array.
		foreach ( $field['layouts'] ?? array() as $layout ) {
			$sub_fields = array_merge( $sub_fields, $layout['sub_fields'] ?? array() );
		}

		// Process all gathered subfields and recurse.
		foreach ( $sub_fields as $sub_field ) {
			foreach ( $object_ids as $any_id ) {
				self::$updated[] = $this->get_storage_key( $any_id, $sub_field['key'] );
			}
			$this->mark_subfields_as_updated( $sub_field, $object_ids );
		}
	}

	/**
	 * Executes a strategy on fields from the current object to a target object.
	 *
	 * @since 3.7
	 *
	 * @param Abstract_Strategy $strategy Strategy to execute.
	 * @param int               $to       ID of the target object.
	 * @param array             $args     {
	 *      Array of arguments.
	 *
	 *      @type mixed  $original_value Optional. The translated value of the field, if any.
	 *      @type bool   $update         Optional. Tells if we can update the target ID fields, default `true`.
	 * }
	 * @return void
	 */
	public function apply_to_all_fields( Abstract_Strategy $strategy, int $to = 0, array $args = array() ) {
		// Removes filters on `Dispatcher::update()` to avoid unnecessary operations on `acf_update_value`.
		remove_filter( 'acf/update_value', array( Dispatcher::class, 'update' ), 5 );

		$fields = get_field_objects( static::acf_id( $this->get_id() ), false );

		if ( empty( $fields ) ) {
			$fields = array();
		}

		$args['update'] = ! isset( $args['update'] ) || (bool) $args['update'];

		foreach ( $fields as $field ) {
			if ( empty( $field['value'] ) && ! is_string( $field['value'] ) ) {
				continue;
			}

			$args['original_value'] = acf_get_value( static::acf_id( $to ), $field );
			if ( empty( $args['original_value'] ) ) {
				$args['original_value'] = $field['default_value'] ?? null;
			}

			$tr_value = $strategy->execute(
				$this,
				$field['value'],
				$field,
				$args
			);

			if ( $to <= 0 || empty( $args['update'] ) ) {
				continue;
			}

			/*
			 * Prevent row duplication with paginated repeaters:
			 * Since we're copying the whole list of rows, we don't need `acf_field_repeater::update_value()` to
			 * decide which row is new, modified, etc. Just save it as we provide it.
			 */
			$field['pagination'] = 0;

			acf_update_value(
				$tr_value,
				static::acf_id( $to ),
				$field
			);
		}

		// Reset filter for `Dispatcher::update` so our integration works for later operations.
		add_filter( 'acf/update_value', array( Dispatcher::class, 'update' ), 5, 3 );
	}

	/**
	 * Exports custom fields.
	 *
	 * @param PLL_Export_Data $export The export object.
	 * @param object|null     $to     The translated object if it exists, `null` otherwise.
	 * @return void
	 * @since 3.7
	 */
	public function export( PLL_Export_Data $export, ?object $to ) {
		$this->apply_to_all_fields(
			new Export( $export ),
			empty( $to ) ? 0 : $this->get_object_id( $to ),
			array(
				'target_language' => $export->get_target_language(),
				'source_language' => $export->get_source_language(),
				'update'          => false,
			)
		);
	}

	/**
	 * Translates the custom fields from the current object.
	 *
	 * @since 3.7
	 *
	 * @param object       $to           The target object.
	 * @param PLL_Language $target_lang  Target language object.
	 * @param Translations $translations A set of translations to search the custom fields translations in.
	 * @return object The translated object.
	 */
	public function translate( object $to, PLL_Language $target_lang, Translations $translations ): object {
		$this->apply_to_all_fields(
			new Import( $translations ),
			$this->get_object_id( $to ),
			array(
				'target_language' => $target_lang,
				'source_language' => PLL()->model->{$this->get_type()}->get_language( $this->get_id() ),
			)
		);

		return $to;
	}

	/**
	 * Removes ACF metas from metas to be synchronized by Polylang.
	 * To use only the ACF integration synchronization mechanism.
	 *
	 * @since 3.7
	 *
	 * @param string[]   $metas List of custom fields names.
	 * @param bool       $sync  True if it is synchronization, false if it is a copy.
	 * @param int|string $from  ID of the object from which we copy information.
	 * @param int|string $to    ID of the object to which we copy information.
	 * @return string[]
	 */
	public static function remove_acf_metas_from_pll_sync( $metas, $sync, $from, $to ) {
		if ( ! is_array( $metas ) ) {
			return $metas;
		}

		$from = static::acf_id( (int) $from );
		$to   = static::acf_id( (int) $to );

		$acf_metas = array_merge( (array) acf_get_meta( $from ), (array) acf_get_meta( $to ) );
		$acf_metas = array_keys( $acf_metas );

		return array_diff( $metas, $acf_metas );
	}

	/**
	 * Ajax response for changing the language of an object in its interface.
	 *
	 * @since 3.8
	 *
	 * @param PLL_Language $language The language slug fetched from the AJAX request.
	 * @param string       $fields   Fields fetched from the AJAX request.
	 * @return array The AJAX response with the new language slug and fields.
	 */
	public function on_lang_choice( PLL_Language $language, string $fields ): array {
		$response = array(
			'lang' => $language->slug,
		);

		$fields = explode( ',', $fields );
		foreach ( $fields as $field ) {
			$field_array = acf_get_field( $field );

			if ( false === $field_array ) {
				continue;
			}

			$from_value           = acf_get_value( static::acf_id( $this->get_id() ), $field_array );
			$field_array['value'] = ( new Copy() )->execute(
				$this,
				$from_value,
				$field_array,
				array(
					'target_language' => $language,
					'original_value'  => $from_value,
				)
			);
			acf_update_value( $field_array['value'], static::acf_id( $this->get_id() ), $field_array );

			$field_wrap = $this->render_field( $field_array );

			$response['fields'][] = array(
				'field_key'  => str_replace( '_', '-', $field ),
				'field_data' => $field_wrap,
			);
		}

		return $response;
	}

	/**
	 * Adds the language of the current object to the arguments that will be used for the query
	 * in the `relationship` and `post_object` ACF fields.
	 *
	 * @since 3.7
	 *
	 * @param array $args Arguments to retrieve posts.
	 * @return array The arguments to retrieve posts with the current object language.
	 */
	public function add_language_to_query( $args ) {
		if ( isset( $args['lang'] ) ) {
			return $args;
		}

		$language = PLL()->model->{$this->get_type()}->get_language( $this->get_id() );
		if ( empty( $language ) ) {
			return $args;
		}

		$args['lang'] = $language->slug;

		return $args;
	}

	/**
	 * Enqueues Javascript to refresh fields on language change in translatable entities metabox.
	 *
	 * @since 3.8
	 *
	 * @return void
	 */
	protected static function enqueue_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_script( 'pll_acf_post', plugins_url( '/js/build/integrations/acf' . $suffix . '.js', POLYLANG_ROOT_FILE ), array( 'wp-api-fetch', 'acf-input', 'wp-sanitize' ), POLYLANG_VERSION, true );
	}

	/**
	 * Returns current object ID.
	 *
	 * @since 3.7
	 *
	 * @return int
	 */
	public function get_id(): int {
		return $this->id;
	}

	/**
	 * Gets the ACF field key to store.
	 *
	 * @since 3.7
	 *
	 * @param int    $id  Object ID.
	 * @param string $key The custom field key.
	 * @return string
	 */
	protected function get_storage_key( $id, $key ) {
		return static::acf_id( $id ) . '|' . $key;
	}

	/**
	 * Returns the object ID.
	 *
	 * @since 3.7
	 *
	 * @param object $object The object.
	 * @return int
	 */
	abstract protected function get_object_id( $object ): int;

	/**
	 * Transforms an object ID to the corresponding ACF post ID.
	 *
	 * @since 3.7
	 *
	 * @param int $id Object ID.
	 * @return int|string ACF post ID.
	 */
	abstract protected static function acf_id( $id );

	/**
	 * Returns source object ID passed in the main request if exists.
	 *
	 * @since 3.7
	 *
	 * @return int
	 */
	abstract protected function get_from_id_in_request(): int;

	/**
	 * Returns current object type.
	 *
	 * The returned value must match:
	 * - the name of the property storing the corresponding model (`PLL()->model->{type}`).
	 * - the `object_type` from `PLL_Export_Data::add_translation_entry()`.
	 *
	 * @since 3.7
	 *
	 * @return string
	 * @phpstan-return non-falsy-string
	 */
	abstract public function get_type(): string;

	/**
	 * Renders the field with its wrapping element type and instruction render position (label|field).
	 *
	 * @since 3.7
	 *
	 * @param array $field The field to render.
	 * @return string HTML rendered string of the field.
	 */
	abstract protected function render_field( array $field ): string;
}
