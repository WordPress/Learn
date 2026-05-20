<?php
/**
 * @package Polylang-Pro
 */

/**
 * Class to filter templates for terms.
 *
 * @since 3.7
 */
class PLL_Filter_Template_Core_Taxonomy extends PLL_Abstract_Filter_Template {
	/**
	 * Adds templates of the default language from a term.
	 *
	 * @since 3.7
	 *
	 * @param array  $templates Array of templates guessed from the hierarchy.
	 * @param object $object    Term to use to guess templates.
	 * @return array Array of templates with added ones.
	 */
	protected function add_def_lang_templates_from_object( array $templates, object $object ): array {
		if ( ! $object instanceof WP_Term ) {
			return $templates;
		}

		$def_lang_id     = $this->model->term->get( $object->term_id, $this->default_language );
		$def_lang_object = get_term( $def_lang_id );
		if ( ! $def_lang_object instanceof WP_Term ) {
			return $templates;
		}

		return $this->prepend_templates( $templates, $def_lang_object );
	}

	/**
	 * Returns the language of the given term.
	 *
	 * @since 3.7
	 *
	 * @param object $object The term object.
	 * @return PLL_Language|false Term language, `false` if none found.
	 */
	protected function get_object_language( object $object ) {
		if ( ! $object instanceof WP_Term ) {
			return false;
		}

		return $this->model->term->get_language( $object->term_id );
	}

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
		$type = 'post_tag' === $term->taxonomy ? 'tag' : $term->taxonomy;
		array_unshift( $templates, "{$type}-{$term->term_id}.php" );
		array_unshift( $templates, "{$type}-{$term->slug}.php" );

		return $templates;
	}
}
