<?php
/**
 * @package Polylang-Pro
 */

/**
 * Class to filter single type templates for posts.
 *
 * @since 3.7
 */
class PLL_Filter_Template_Single extends PLL_Filter_Template_Page {
	/**
	 * Prepends templates for the given post to the given array.
	 *
	 * @since 3.7
	 *
	 * @param array   $templates Array of templates.
	 * @param WP_Post $post      Post object to use.
	 * @return array Array of templates with prepended ones.
	 */
	protected function prepend_templates( array $templates, WP_Post $post ): array {
		array_unshift( $templates, "single-{$post->post_type}-{$post->post_name}.php" );

		return $templates;
	}
}
