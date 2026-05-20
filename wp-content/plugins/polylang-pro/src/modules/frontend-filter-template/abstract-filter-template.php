<?php
/**
 * @package Polylang-Pro
 */

/**
 * Abstract class to filter templates for translatable objects.
 *
 * @since 3.7
 */
abstract class PLL_Abstract_Filter_Template {
	/**
	 * Instance of Polylang model.
	 *
	 * @var PLL_Model
	 */
	protected $model;

	/**
	 * Default language object.
	 *
	 * @var PLL_Language
	 */
	protected $default_language;

	/**
	 * Constructor.
	 *
	 * @since 3.7
	 *
	 * @param PLL_Model $model Instance of Polylang model.
	 */
	public function __construct( PLL_Model $model ) {
		$this->model = $model;
		/** @phpstan-var PLL_Language $default_language */
		$default_language       = $this->model->get_default_language();
		$this->default_language = $default_language;
	}

	/**
	 * Filters templates according to the current queried object.
	 *
	 * @since 3.7
	 *
	 * @param array $templates Array of templates guessed from the hierarchy.
	 * @return array Filtered array of templates.
	 */
	public function filter( $templates ) {
		if ( ! is_array( $templates ) ) {
			// Something bad happened.
			return $templates;
		}

		$object = get_queried_object();
		if ( ! is_object( $object ) ) {
			return $templates;
		}

		$language = $this->get_object_language( $object );
		if ( ! $language instanceof PLL_Language ) {
			return $templates;
		}

		if ( $language->slug === $this->default_language->slug ) {
			return $templates;
		}

		return $this->add_def_lang_templates_from_object( $templates, $object );
	}

	/**
	 * Adds templates of the default language from an object.
	 *
	 * @since 3.7
	 *
	 * @param array  $templates Array of templates guessed from the hierarchy.
	 * @param object $object    Object to use to guess templates.
	 * @return array Array of templates with added ones.
	 */
	abstract protected function add_def_lang_templates_from_object( array $templates, object $object ): array;

	/**
	 * Returns the language of the given object.
	 *
	 * @since 3.7
	 *
	 * @param object $object The object (e.g. `WP_Post` or `WP_Term`).
	 * @return PLL_Language|false Object language, `false` if none found.
	 */
	abstract protected function get_object_language( object $object );
}
