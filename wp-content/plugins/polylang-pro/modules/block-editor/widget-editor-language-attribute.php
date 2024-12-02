<?php
/**
 * @package Polylang-Pro
 */

/**
 * Widget editor language attribute.
 *
 * @since 3.1
 */
class PLL_Widget_Editor_Language_Attribute {

	/**
	 * Constructor.
	 *
	 * Adds filters.
	 *
	 * @since 3.1
	 */
	public function __construct() {
		add_filter( 'register_block_type_args', array( $this, 'add_block_attribute' ) ); // Since WP 5.5.
	}

	/**
	 *
	 * Adds the 'pll_lang' attribute to an existing block.
	 * Do not add the 'pll_lang' attribute if the block is a legacy widget.
	 *
	 * @since 3.1
	 *
	 * @param array $block_properties Array of parameters for registering a block type.
	 *                                Each of them becomes a property of the block type object.
	 * @return array Array of modified parameters for registering a block type.
	 */
	public function add_block_attribute( $block_properties ) {
		$is_block_dynamic = $this->is_block_dynamic( $block_properties );
		$is_legacy_widget = 'core/legacy-widget' === $block_properties['name'];
		if ( $is_block_dynamic && ! $is_legacy_widget ) {
			$block_properties['attributes']['pll_lang'] = array(
				'type'    => 'string',
				'default' => '',
			);
		}
		return $block_properties;
	}

	/**
	 * Whether the block is a dynamic one or not.
	 *
	 * Copied from {@see https://github.com/WordPress/wordpress-develop/blob/2382765afa36e10bf3c74420024ad4e85763a47c/src/wp-includes/class-wp-block-type.php#L261 WP_Block_Type::is_dynamic()}.
	 *
	 * Explanation: 'register_block_type_args' filter only passes the WP_Block_Type::name property, so the WP_Block_Type::is_dynamic() method is inaccessible in a callback function, {@see https://github.com/WordPress/WordPress/blob/5.7/wp-includes/class-wp-block-type.php#L330 }.
	 * We could not use the WP_Block_Type_Registry to get the block object neither, because the WP_Block_Type instance is added to the registry only after the use of the 'register_block_type_args' filter ( done in WP_Block_Type::set_props itself done in the WP_Block_Type constructor ):
	 *   - {@see https://github.com/WordPress/WordPress/blob/5.7/wp-includes/class-wp-block-type.php#L223}
	 *   - {@see https://github.com/WordPress/WordPress/blob/5.7/wp-includes/class-wp-block-type.php#L312}
	 *   - {@see https://github.com/WordPress/WordPress/blob/058f9903676a7efaee534a682df0a2a8b87574d8/wp-includes/class-wp-block-type-registry.php#L80-L84}
	 *
	 * @since 3.1
	 *
	 * @param array $block_properties Associative array mimicking the structure of a {@see https://github.com/WordPress/wordpress-develop/blob/2382765afa36e10bf3c74420024ad4e85763a47c/src/wp-includes/class-wp-block-parser.php#L15 WP_Block_Parser_Block}.
	 *
	 * @return bool
	 */
	public function is_block_dynamic( $block_properties ) {
		$render_callback = $block_properties['render_callback'];

		return is_callable( $render_callback );
	}
}
