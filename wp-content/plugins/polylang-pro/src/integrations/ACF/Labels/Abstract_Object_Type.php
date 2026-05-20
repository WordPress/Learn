<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Integrations\ACF\Labels;

use stdClass;
use ACF_Internal_Post_Type;

/**
 * This class is part of the ACF compatibility.
 * Registers and translates the labels of custom post types or taxonomies created within ACF's UI.
 * Translation is supposed to happen only on frontend (for the archive page title for example).
 *
 * @since 3.7
 */
abstract class Abstract_Object_Type {
	/**
	 * Setups actions and filters.
	 *
	 * @since 3.7
	 *
	 * @return void
	 */
	public function on_acf_init(): void {
		if ( ! defined( 'ACF_VERSION' ) || version_compare( ACF_VERSION, '6.1.0', '<' ) ) {
			// Backward compatibility with ACF < 6.1.
			return;
		}

		if ( ! acf_get_setting( 'enable_post_types' ) ) {
			// The feature is deactivated.
			return;
		}

		/*
		 * After `ACF::init()` (prio 5).
		 * Otherwise the classes `ACF_Post_Type` and `ACF_Taxonomy` won't exist (their file is included there).
		 */
		$this->register_strings();

		if ( did_action( 'pll_language_defined' ) ) {
			$this->translate_registered_strings();
		} else {
			// Special case when the language is set from the content as CPT and taxonomies are registered before the language is defined.
			add_action( 'pll_language_defined', array( $this, 'translate_registered_strings' ) );
		}
	}

	/**
	 * Registers strings for custom post types or taxonomies labels.
	 *
	 * @since 3.7
	 *
	 * @return void
	 */
	public function register_strings(): void {
		foreach ( $this->get_acf_type_instance()->get_posts( array( 'active' => true ) ) as $acf_object ) {
			$label_start = sprintf( 'ACF %s, %s,', $this->get_type_label(), $acf_object[ $this->get_type() ] );

			pll_register_string( "{$label_start} title", $acf_object['title'], 'ACF' );
			pll_register_string( "{$label_start} description", $acf_object['description'], 'ACF', true );

			foreach ( $acf_object['labels'] as $key => $label ) {
				pll_register_string( "{$label_start} {$key}", $label, 'ACF' );
			}
		}
	}

	/**
	 * Translates custom post type and taxonomy labels when the language is ready.
	 *
	 * @since 3.7
	 *
	 * @return void
	 */
	public function translate_registered_strings(): void {
		$acf_objects = $this->get_acf_type_instance()->get_posts( array( 'active' => true ) );
		$acf_objects = array_column( $acf_objects, $this->get_type(), $this->get_type() );
		$acf_objects = array_intersect_key( $this->get_type_objects(), $acf_objects );

		foreach ( $acf_objects as $type ) {
			$type->label       = pll__( $type->label );
			$type->description = pll__( $type->description );

			foreach ( array_keys( get_object_vars( $type->labels ) ) as $key ) {
				$type->labels->$key = pll__( $type->labels->$key );
			}
		}
	}

	/**
	 * Returns the type.
	 *
	 * @since 3.7
	 *
	 * @return string
	 *
	 * @phpstan-return non-falsy-string
	 */
	abstract protected function get_type(): string;

	/**
	 * Returns the instance of the related "ACF type".
	 *
	 * @since 3.7
	 *
	 * @return ACF_Internal_Post_Type
	 */
	abstract protected function get_acf_type_instance(): ACF_Internal_Post_Type;

	/**
	 * Returns the list of type objects containing labels.
	 *
	 * @since 3.7
	 *
	 * @return object[]
	 *
	 * @phpstan-return array<
	 *     non-falsy-string,
	 *     object{label: string, description: string, labels: object}&stdClass
	 * >
	 */
	abstract protected function get_type_objects(): array;

	/**
	 * Returns the label of the type.
	 *
	 * @since 3.7
	 *
	 * @return string
	 *
	 * @phpstan-return non-empty-string
	 */
	abstract protected function get_type_label(): string;
}
