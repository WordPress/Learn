<?php
/**
 * @package Polylang-Pro
 */

/**
 * Class to filter templates for posts.
 *
 * @since 3.7
 */
class PLL_Filter_Template_Page extends PLL_Abstract_Filter_Template {
	/**
	 * Adds templates of the default language from a post.
	 *
	 * @since 3.7
	 *
	 * @param array  $templates Array of templates guessed from the hierarchy.
	 * @param object $object    Post to use to guess templates.
	 * @return array Array of templates with added ones.
	 */
	protected function add_def_lang_templates_from_object( array $templates, object $object ): array {
		if ( ! $object instanceof WP_Post ) {
			return $templates;
		}

		$def_lang_id     = $this->model->post->get( $object->ID, $this->default_language );
		$def_lang_object = get_post( $def_lang_id );
		if ( ! $def_lang_object instanceof WP_Post ) {
			return $templates;
		}
		return $this->prepend_templates( $templates, $def_lang_object );
	}

	/**
	 * Returns the language of the given post.
	 *
	 * @since 3.7
	 *
	 * @param object $object The post object.
	 * @return PLL_Language|false Post language, `false` if none found.
	 */
	protected function get_object_language( object $object ) {
		if ( ! $object instanceof WP_Post ) {
			return false;
		}

		return $this->model->post->get_language( $object->ID );
	}

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
		array_unshift( $templates, "page-{$post->ID}.php" );
		array_unshift( $templates, "page-{$post->post_name}.php" );

		return $templates;
	}
}
