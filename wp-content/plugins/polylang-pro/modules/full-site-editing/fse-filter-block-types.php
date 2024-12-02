<?php
/**
 * @package Polylang-Pro
 */

defined( 'ABSPATH' ) || exit;

/**
 * A class that filters blocks in a Site Editor context.
 *
 * @since 3.2.2
 */
class PLL_FSE_Filter_Block_Types extends PLL_FSE_Abstract_Module implements PLL_Module_Interface {

	/**
	 * Returns the module's name.
	 *
	 * @since 3.2.2
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'fse_manage_template_part_blocks';
	}

	/**
	 * Sub-module init.
	 *
	 * @since 3.2.2
	 *
	 * @return self
	 */
	public function init() {
		// Backward compatibility with WP < 6.5.
		if ( version_compare( $GLOBALS['wp_version'], '6.5-alpha', '<' ) ) {
			add_filter( 'block_type_metadata_settings', array( $this, 'block_type_metadata_settings' ), 10, 2 );
		} else {
			add_filter( 'get_block_type_variations', array( $this, 'filter_block_type_variations' ), 10, 2 );
		}

		return $this;
	}


	/**
	 * Filters out template part instances from block `core/template-part` variations.
	 * This avoids to display all translations of templates parts in the block selection list,
	 * otherwhise the confusing UI could allow a user to insert a template part in a wrong language.
	 *
	 * @since 3.2.2
	 * @since 3.6 Renamed from `remove_template_part_instance_variations()`.
	 *
	 * @param array $settings Array of determined settings for registering a block type.
	 * @param array $metadata Metadata provided for registering a block type..
	 * @return array Filtered array of settings with removed template part instances variations.
	 */
	public function block_type_metadata_settings( $settings, $metadata ) {
		if ( 'core/template-part' !== $metadata['name'] ) {
			return $settings;
		}

		if ( empty( $settings['variations'] ) || ! is_array( $settings['variations'] ) ) {
			return $settings;
		}

		$settings['variations'] = $this->remove_template_part_variations( $settings['variations'] );

		return $settings;
	}

	/**
	 * Filters out template part instances from block `core/template-part` variations.
	 * This avoids to display all translations of templates parts in the block selection list,
	 * otherwhise the confusing UI could allow a user to insert a template part in a wrong language.
	 *
	 * @since 3.6
	 *
	 * @param array         $variations Array of block variations.
	 * @param WP_Block_Type $block_type Block type object to filter.
	 * @return array Filtered variations.
	 */
	public function filter_block_type_variations( $variations, $block_type ) {
		if ( 'core/template-part' !== $block_type->name ) {
			return $variations;
		}

		return $this->remove_template_part_variations( $variations );
	}

	/**
	 * Filters out template part instances from block `core/template-part` variations
	 * and keep are variations.
	 *
	 * @since 3.6
	 *
	 * @param array $variations Array of block `core/template-part` variations.
	 * @return array Filtered variations.
	 */
	private function remove_template_part_variations( $variations ) {
		foreach ( $variations as $i => $variation ) {
			/*
			 * Check attributes specific to template part instances variations to keep area variations registered.
			 * See: {https://github.com/WordPress/wordpress-develop/blob/6.1.1/src/wp-includes/blocks/template-part.php#L186-L238}
			 * and {https://github.com/WordPress/wordpress-develop/blob/6.1.1/src/wp-includes/blocks/template-part.php#L161-L184}.
			 */
			if ( ! isset( $variation['attributes']['slug'], $variation['attributes']['theme'] ) ) {
				continue;
			}

			unset( $variations[ $i ] );
		}

		return array_values( $variations );
	}
}
