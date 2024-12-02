<?php
/**
 * @package Polylang-Pro
 */

/**
 * Main class for Polylang Pro wizard.
 *
 * @since 2.7
 */
class PLL_Wizard_Pro {

	/**
	 * @var PLL_Model
	 */
	public $model;

	/**
	 * @var PLL_Sync_Post_Model
	 */
	protected $sync_model;

	/**
	 * Constructor
	 *
	 * @since 2.7
	 *
	 * @param object $polylang Reference to Polylang options array.
	 */
	public function __construct( &$polylang ) {
		$this->model      = &$polylang->model;
		$this->sync_model = &$polylang->sync_post_model;

		// See pll_wizard_create_home_page_translations filter in PLL_Wizard class.
		add_filter( 'pll_wizard_create_home_page_translations', array( $this, 'replace_create_home_page_translations' ) );
	}

	/**
	 * Replace function to apply to process the home page transations creation.
	 *
	 * @since 2.7
	 *
	 * @return callable
	 */
	public function replace_create_home_page_translations() {
		return array( $this, 'create_home_page_translations' );
	}

	/**
	 * Create home page translations for each language defined with duplicating content.
	 *
	 * @since 2.7
	 *
	 * @param string $default_language       slug of the default language; null if no default language is defined.
	 * @param int    $home_page              post_id of the home page if it's defined, false otherwise.
	 * @param string $home_page_title        home page title if it's defined, 'Homepage' otherwise.
	 * @param string $home_page_language     slug of the home page if it's defined, false otherwise.
	 * @param array  $untranslated_languages array of languages which needs to have a home page translated.
	 * @return void
	 */
	public function create_home_page_translations( $default_language, $home_page, $home_page_title, $home_page_language, $untranslated_languages ) {
		global $wpdb;

		foreach ( $untranslated_languages as $language ) {
			$translated_post_id = $this->sync_model->copy_post( $home_page, $language, false );
			$language_properties = $this->model->get_language( $language );
			$wpdb->update(
				$wpdb->posts,
				array( 'post_title' => $home_page_title . ' - ' . $language_properties->name ),
				array( 'ID' => $translated_post_id )
			); // Don't use wp_update_post to ensure not redo save post process.
		}
	}
}
