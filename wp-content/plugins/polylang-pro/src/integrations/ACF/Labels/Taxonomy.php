<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Integrations\ACF\Labels;

use stdClass;
use ACF_Internal_Post_Type;

/**
 * This class is part of the ACF compatibility.
 * Registers and translates the labels of custom taxonomies created within ACF's UI.
 *
 * @since 3.7
 */
class Taxonomy extends Abstract_Object_Type {
	/**
	 * Returns the type.
	 *
	 * @since 3.7
	 *
	 * @return string
	 *
	 * @phpstan-return non-falsy-string
	 */
	protected function get_type(): string {
		return 'taxonomy';
	}

	/**
	 * Returns the instance of the related "ACF type".
	 *
	 * @since 3.7
	 *
	 * @return ACF_Internal_Post_Type
	 */
	protected function get_acf_type_instance(): ACF_Internal_Post_Type {
		return acf_get_instance( 'ACF_Taxonomy' );
	}

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
	protected function get_type_objects(): array {
		return $GLOBALS['wp_taxonomies'];
	}

	/**
	 * Returns the label of the type.
	 *
	 * @since 3.7
	 *
	 * @return string
	 *
	 * @phpstan-return non-empty-string
	 */
	protected function get_type_label(): string {
		return 'Taxonomy';
	}
}
