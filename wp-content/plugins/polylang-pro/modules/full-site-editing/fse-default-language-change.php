<?php
/**
 * @package Polylang-Pro
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class that changes the template slugs when the default language changes.
 *
 * @since 3.2
 */
class PLL_FSE_Default_Language_Change extends PLL_FSE_Abstract_Bulk_Edit_Template_Slugs_Module implements PLL_Module_Interface {

	/**
	 * Returns the module's name.
	 *
	 * @since 3.2
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'fse_default_language_change';
	}

	/**
	 * Plugin init.
	 *
	 * @since 3.2
	 *
	 * @return self
	 */
	public function init() {
		add_action( 'pll_update_default_lang', array( $this, 'change_template_slugs' ) );
		return $this;
	}

	/**
	 * Suffixes or unsuffixes the template slugs when changing the default language.
	 * More precisely:
	 * - Adds a language suffix to the slugs belonging to templates in the old default language.
	 * - Removes the language suffix from the slugs belonging to templates in the new default language.
	 *
	 * @since 3.2
	 *
	 * @param  string $new_def_lang_slug Slug of the new default language.
	 *                                   At this point, the default language has not been changed in PLL's settings yet.
	 * @return void
	 */
	public function change_template_slugs( $new_def_lang_slug ) {
		$cur_def_lang = $this->model->get_default_language();

		if ( empty( $cur_def_lang ) || $cur_def_lang->slug === $new_def_lang_slug ) {
			// Uh? The new lang and the current one are the same?
			return;
		}

		$new_def_lang = $this->model->get_language( $new_def_lang_slug );

		if ( empty( $new_def_lang ) ) {
			// Uh?
			return;
		}

		// Add a language suffix to the slugs belonging to templates in the current default language.
		$this->update_language_suffix_in_post_names( $cur_def_lang );

		// Remove the language suffix from the slugs belonging to templates in the new default language.
		$this->remove_language_suffix_from_post_names( $new_def_lang );
	}
}
