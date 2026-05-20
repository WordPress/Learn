<?php
/**
 * @package Polylang-Pro
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class that regroups actions focused on post deletions.
 *
 * @since 3.2
 * @since 3.4.5 Renamed `PLL_FSE_Template_Deletion` into `PLL_FSE_Post_Deletion`.
 */
class PLL_FSE_Post_Deletion extends PLL_FSE_Abstract_Module implements PLL_Module_Interface {

	/**
	 * Returns the module's name.
	 *
	 * @since 3.2
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'fse_post_deletion';
	}

	/**
	 * Sub-module init.
	 *
	 * @since 3.2
	 *
	 * @return self
	 */
	public function init() {
		add_action( 'before_delete_post', array( $this, 'delete_translation_posts' ), 8, 2 ); // Before `PLL_CRUD_Posts->delete_post()` (prio 10), so the data about the translations still exist.
		return $this;
	}

	/**
	 * When a template in the default language is deleted, also delete its translations.
	 *
	 * @since 3.2
	 *
	 * @param  int     $post_id Post ID.
	 * @param  WP_Post $post    Post object.
	 * @return void
	 */
	public function delete_translation_posts( $post_id, $post ) {
		if ( ! $post instanceof WP_Post || ! in_array( $post->post_type, PLL_FSE_Tools::get_translatable_post_types(), true ) ) {
			// Not a translated template post type.
			return;
		}

		$def_lang  = $this->model->get_default_language();
		$post_lang = $this->model->post->get_language( $post->ID );

		if ( empty( $def_lang ) || empty( $post_lang ) || $def_lang->slug !== $post_lang->slug ) {
			// This one is not in the default language.
			return;
		}

		$translations = $this->model->post->get_translations( $post->ID );
		$translations = array_diff( $translations, array( $post->ID ) ); // Let's not create an infinite loop.

		if ( empty( $translations ) ) {
			// Nothing to delete.
			return;
		}

		foreach ( $translations as $translation_id ) {
			// Send it to Sovngarde.
			wp_delete_post( $translation_id, true );
		}
	}
}
