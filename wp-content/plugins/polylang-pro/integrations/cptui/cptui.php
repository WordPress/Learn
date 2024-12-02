<?php
/**
 * @package Polylang-Pro
 */

/**
 * Manages compatibility with Custom Post Type UI.
 * Version tested: 1.5.4.
 *
 * @since 2.1
 */
class PLL_CPTUI {

	/**
	 * Initializes filters and actions.
	 *
	 * @since 2.1
	 *
	 * @return void
	 */
	public function init() {
		$keys = array(
			'*' => array(
				'label'          => 1,
				'singular_label' => 1,
				'description'    => 1,
				'labels'         => array(
					'*' => 1,
				),
			),
		);

		new PLL_Translate_Option( 'cptui_post_types', $keys, array( 'context' => 'CPT UI' ) );
		new PLL_Translate_Option( 'cptui_taxonomies', $keys, array( 'context' => 'CPT UI' ) );

		if ( PLL() instanceof PLL_Frontend && ! PLL()->options['force_lang'] ) {
			// Special case when the language is set from the content as CPT and taxonomies are registered before the language is defined.
			add_action( 'pll_language_defined', array( $this, 'pll_language_defined' ) );
		}


		// Add CPT UI post types and taxonomies to Polylang settings.
		add_filter( 'pll_get_post_types', array( $this, 'pll_get_types' ), 10, 2 );
		add_filter( 'pll_get_taxonomies', array( $this, 'pll_get_types' ), 10, 2 );
	}

	/**
	 * Translates custom post types and taxonomies labels when the language is set from the content.
	 *
	 * @since 2.1
	 *
	 * @param array $types       Array of registered post types or taxonomies.
	 * @param array $cptui_types Array of CPT UI post types or taxonomies.
	 */
	public function translate_registered_types( $types, $cptui_types ) {
		foreach ( $types as $name => $type ) {
			if ( in_array( $name, $cptui_types ) ) {
				$type->label       = pll__( $type->label );
				$type->description = pll__( $type->description );

				foreach ( array_keys( get_object_vars( $type->labels ) ) as $key ) {
					$type->labels->$key = pll__( $type->labels->$key );
				}
			}
		}
	}

	/**
	 * Translates custom post types and taxonomies labels when the language is set from the content.
	 *
	 * @since 2.1
	 */
	public function pll_language_defined() {
		$this->translate_registered_types( $GLOBALS['wp_post_types'], array_keys( get_option( 'cptui_post_types', array() ) ) );
		$this->translate_registered_types( $GLOBALS['wp_taxonomies'], array_keys( get_option( 'cptui_taxonomies', array() ) ) );
	}

	/**
	 * Add CPT UI post types and taxonomies to Polylang settings.
	 *
	 * @since 2.1
	 *
	 * @param string[] $types       List of post type or taxonomy names.
	 * @param bool     $is_settings True when displaying the list in Polylang settings.
	 * @return string[]
	 */
	public function pll_get_types( $types, $is_settings ) {
		if ( $is_settings ) {
			$type        = substr( current_filter(), 8 );
			$cptui_types = get_option( "cptui_{$type}" );

			if ( is_array( $cptui_types ) ) {
				$types = array_merge( $types, array_keys( $cptui_types ) );
			}
		}
		return $types;
	}
}
