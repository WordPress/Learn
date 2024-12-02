<?php
/**
 * @package Polylang-Pro
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class that changes the template slugs when a language slug changes.
 *
 * @since 3.2
 */
class PLL_FSE_Language_Slug_Change extends PLL_FSE_Abstract_Bulk_Edit_Template_Slugs_Module implements PLL_Module_Interface {

	/**
	 * Returns the module's name.
	 *
	 * @since 3.2
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'fse_language_slug_change';
	}

	/**
	 * Sub-module init.
	 *
	 * @since 3.2
	 *
	 * @return self
	 */
	public function init() {
		add_action( 'pll_update_language', array( $this, 'change_template_slugs' ), 10, 2 );
		return $this;
	}

	/**
	 * Modifies template slugs when a language slug changes.
	 *
	 * @since 3.2
	 *
	 * @param array        $args {
	 *     Arguments used to modify the language. @see PLL_Admin_Model::update_language().
	 *
	 *     @type string $name           Language name (used only for display).
	 *     @type string $slug           Language code (ideally 2-letters ISO 639-1 language code).
	 *     @type string $locale         WordPress locale.
	 *     @type int    $rtl            1 if rtl language, 0 otherwise.
	 *     @type int    $term_group     Language order when displayed.
	 *     @type string $no_default_cat Optional, if set, no default category has been created for this language.
	 *     @type string $flag           Optional, country code, @see flags.php.
	 * }
	 * @param PLL_Language $lang Previous value of the language beeing edited.
	 * @return void
	 */
	public function change_template_slugs( $args, $lang ) {
		if ( $lang->slug === $args['slug'] ) {
			// The slug hasn't changed.
			return;
		}

		/**
		 * At this point:
		 * - the language cache has been cleared: `PLL_Model->clean_languages_cache()` is hooked to
		 *   'edited_term_taxonomy'.
		 * - `$this->options['default_lang']` has been updated in `PLL_Admin_Model->update_language()`.
		 */
		$def_lang = $this->model->get_default_language();

		if ( empty( $def_lang ) || $def_lang->slug === $args['slug'] ) {
			// Templates in the default language don't have the language suffix: nothing to update then.
			return;
		}

		// At this point, we're modifying the slug of a non-default language.
		$this->update_language_suffix_in_post_names( $lang, $args['slug'] );
	}
}
