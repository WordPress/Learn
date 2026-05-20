<?php
/**
 * @package Polylang-Pro
 */

/**
 * Class to filter custom taxonomy type templates for terms.
 *
 * @since 3.7
 */
class PLL_Filter_Template_Custom_Taxonomy extends PLL_Filter_Template_Core_Taxonomy {
	/**
	 * Prepends templates for the given term to the given array.
	 *
	 * @since 3.7
	 *
	 * @param array   $templates Array of templates.
	 * @param WP_Term $term      Term object to use.
	 * @return array Array of templates with prepended ones.
	 */
	protected function prepend_templates( array $templates, WP_Term $term ): array {
		array_unshift( $templates, "taxonomy-{$term->taxonomy}-{$term->slug}.php" );

		return $templates;
	}
}
