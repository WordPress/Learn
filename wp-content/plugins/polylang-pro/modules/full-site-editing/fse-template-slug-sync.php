<?php
/**
 * @package Polylang-Pro
 */

defined( 'ABSPATH' ) || exit;

/**
 * A class that modifies the template slugs according to their language, and sync them among their translations.
 * - The slug of a template in the default language in not suffixed.
 * - The slug of a template in a non-default language is suffixed with `___{language-code}` (see the class
 *   `PLL_FSE_Template_Slug`).
 * - The slug of the template in the default language is used as "base slug" (the part before `___{language-code}`) for
 *   the templates in non-default languages within a translation group. This part is synchronized among the group.
 *
 * TLDR; template slugs are suffixed and synchronized:
 * - Default language: `header`.
 * - Non-default languages: `header___es`, `header___it`, etc.
 *
 * If the slug of a template in the default language changes, the slug of the template in other languages are modified
 * accordingly.
 * If the slug of a template in a non-default language changes, it is modified (or reverted) to fit the slug of the
 * template in the default language.
 *
 * @since 3.2
 */
class PLL_FSE_Template_Slug_Sync extends PLL_FSE_Abstract_Module implements PLL_Module_Interface {

	/**
	 * Returns the module's name.
	 *
	 * @since 3.2
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'fse_template_slug_sync';
	}

	/**
	 * Sub-module init.
	 *
	 * @since 3.2
	 *
	 * @return self
	 */
	public function init() {
		/**
		 * New template:
		 * - (Un)Suffix the template slug after a language has been assigned to a template.
		 * - Synchronize the template slugs among translations after translations have been set or updated.
		 */
		add_action( 'set_object_terms', array( $this, 'modify_template_slug_on_lang_assigning' ), 10, 4 );
		add_action( 'saved_post_translations', array( $this, 'sync_template_slugs_on_translations_save' ) ); // Since WP 5.5.

		/**
		 * Template update:
		 * - (Un)Suffix the template slug before sending the changes into the DB.
		 * - Synchronize the template slugs among translations after a template has been updated.
		 * Note:
		 * We're hooking AFTER `wp_filter_wp_template_unique_post_slug()` to prevent having slugs like `xxxxxxx___fr-2`.
		 */
		add_filter( 'pre_wp_unique_post_slug', array( $this, 'unique_template_slug' ), 10000, 5 ); // After `wp_filter_wp_template_unique_post_slug()` (prio 10).
		add_action( 'post_updated', array( $this, 'sync_template_slugs_on_post_update' ), 10, 2 );
		return $this;
	}

	/**
	 * Modifies the template's slug when the template's language changes.
	 *
	 * @since 3.2
	 *
	 * @see wp_set_object_terms()
	 *
	 * @param  int            $object_id Object ID.
	 * @param  (int|string)[] $terms     An array of object term IDs or slugs, provided as argument to `wp_set_object_terms()`.
	 * @param  int[]          $tt_ids    An array of term taxonomy IDs.
	 * @param  string         $taxonomy  Taxonomy slug.
	 * @return void
	 */
	public function modify_template_slug_on_lang_assigning( $object_id, $terms, $tt_ids, $taxonomy ) {
		if ( empty( $object_id ) || ! is_int( $object_id ) || ! is_array( $terms ) || ! is_array( $tt_ids ) || 'language' !== $taxonomy ) {
			return;
		}

		if ( empty( $terms ) ) {
			/**
			 * We're removing the language from this template, probably because the language itself is being deleted.
			 * In that case we don't remove the language slug, to prevent having two templates with the same slug.
			 * This also allows to keep track of the template's original language, and maybe re-assign it in a future
			 * process if the language is re-created.
			 */
			return;
		}

		$template_post = get_post( $object_id );

		if ( empty( $template_post ) || ! PLL_FSE_Tools::is_template_post_type( $template_post->post_type ) ) {
			// Not a translated template post type.
			return;
		}

		$def_lang = $this->model->get_default_language();

		if ( empty( $def_lang ) ) {
			// No default language.
			return;
		}

		// Since we are at the end of `wp_set_object_terms()`, the new language has been assigned to the template.
		$template_lang = $this->model->post->get_language( $template_post->ID );

		if ( empty( $template_lang ) ) {
			// Uh?
			return;
		}

		if ( $def_lang->slug === $template_lang->slug ) {
			// Default language: remove any language suffix.
			$new_post_name = $this->remove_language_from_post_name( $template_post->post_name );
		} else {
			// Non default language: use the slug of the template in the default language and add a language suffix.
			$def_post_name = $this->get_translation_slug( $template_post->ID, $def_lang->slug, $template_post->post_name );
			$new_post_name = $this->add_language_to_post_name( $def_post_name, $template_lang->slug );
		}

		if ( $new_post_name === $template_post->post_name ) {
			// Nothing to update.
			return;
		}

		// Update the template slug.
		$this->update_template_slug( $template_post, $new_post_name );
	}

	/**
	 * Synchronizes the template slugs among translations after translations have been set or updated.
	 *
	 * @since 3.2
	 *
	 * @param int $term_id Term ID.
	 * @return void
	 */
	public function sync_template_slugs_on_translations_save( $term_id ) {
		$translations = $this->model->post->get_translations_from_term_id( $term_id );

		// Sync translation slugs.
		$this->sync_translation_slugs( $translations );
	}

	/**
	 * Modifies the template's slug after `wp_filter_wp_template_unique_post_slug()` for existing templates.
	 *
	 * @since 3.2
	 *
	 * @param string $override_slug The filtered value of the slug (starts as `null` from `apply_filters()`).
	 * @param string $desired_slug  The desired slug (post_name).
	 * @param int    $post_ID       Post ID.
	 * @param string $post_status   Post status.
	 * @param string $post_type     Post type.
	 * @return string The original or desired slug.
	 */
	public function unique_template_slug( $override_slug, $desired_slug, $post_ID, $post_status, $post_type ) {
		if ( empty( $post_ID ) || ! PLL_FSE_Tools::is_template_post_type( $post_type ) ) {
			// If the post doesn't exist yet, it doesn't have any language assigned to it yet.
			return $override_slug;
		}

		// At this point, the post is an existing template.
		if ( ! empty( $override_slug ) ) {
			$new_post_name = $override_slug;
		} else {
			// Shouldn't happen since we're after `wp_filter_wp_template_unique_post_slug()`.
			// Note: returning a non-null value prevents `PLL_Share_Post_Slug->wp_unique_post_slug()` to run.
			$new_post_name = $desired_slug;
		}

		$template_lang = $this->model->post->get_language( $post_ID );

		if ( empty( $template_lang ) ) {
			// Can't do anything if the template doesn't have any language yet.
			return $new_post_name;
		}

		$def_lang = $this->model->get_default_language();

		if ( empty( $def_lang ) ) {
			// No default language.
			return $new_post_name;
		}

		if ( $def_lang->slug === $template_lang->slug ) {
			// Default language: remove any language suffix.
			return $this->remove_language_from_post_name( $new_post_name );
		}

		// Non default language: use the slug of the template in the default language and add a language suffix.
		$new_post_name = $this->get_translation_slug( $post_ID, $def_lang->slug, $new_post_name );

		return $this->add_language_to_post_name( $new_post_name, $template_lang->slug );
	}

	/**
	 * Synchronizes template slugs among translations after a template update.
	 *
	 * @since 3.2
	 *
	 * @param int     $post_ID       Post ID.
	 * @param WP_Post $template_post Post object after being updated.
	 * @return void
	 */
	public function sync_template_slugs_on_post_update( $post_ID, $template_post ) {
		if ( ! $template_post instanceof WP_Post || ! PLL_FSE_Tools::is_template_post_type( $template_post->post_type ) ) {
			// Not a template.
			return;
		}

		// Sync translations.
		$this->sync_translation_slugs( $this->model->post->get_translations( $template_post->ID ) );
	}

	/**
	 * Removes any language suffix from the given post name.
	 *
	 * @since 3.2
	 *
	 * @param string $post_name The post name.
	 * @return string
	 */
	private function remove_language_from_post_name( $post_name ) {
		return ( new PLL_FSE_Template_Slug( $post_name, $this->get_languages_slugs() ) )->remove_language();
	}

	/**
	 * Adds a language suffix to the given post name.
	 *
	 * @since 3.2
	 *
	 * @param string $post_name The post name.
	 * @param string $lang_slug The lang code.
	 * @return string
	 */
	private function add_language_to_post_name( $post_name, $lang_slug ) {
		return ( new PLL_FSE_Template_Slug( $post_name, $this->get_languages_slugs() ) )->update_language( $lang_slug );
	}

	/**
	 * Returns the slug of a translation.
	 *
	 * @since 3.2
	 *
	 * @param int    $template_id   A template's ID.
	 * @param string $lang_slug     A language slug.
	 * @param string $fallback_slug Fallback slug to return if no template slug is found for the given language.
	 * @return string
	 */
	private function get_translation_slug( $template_id, $lang_slug, $fallback_slug ) {
		$translations = $this->model->post->get_translations( $template_id );

		if ( empty( $translations[ $lang_slug ] ) ) {
			return $fallback_slug;
		}

		$post = get_post( $translations[ $lang_slug ] );

		if ( empty( $post ) ) {
			return $fallback_slug;
		}

		return $post->post_name;
	}

	/**
	 * Update a template's slug in the database.
	 * Also clears the post's cache.
	 *
	 * @since 3.2
	 *
	 * @global wpdb $wpdb
	 *
	 * @param WP_Post $template_post A post ID.
	 * @param string  $slug        The new slug.
	 * @return void
	 */
	private function update_template_slug( WP_Post $template_post, $slug ) {
		$GLOBALS['wpdb']->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$GLOBALS['wpdb']->posts,
			array(
				'post_name' => $slug,
			),
			array(
				'ID' => $template_post->ID,
			),
			null,
			array(
				'ID' => '%d',
			)
		);

		clean_post_cache( $template_post );
	}

	/**
	 * Synchronize translation slugs, given a base slug.
	 *
	 * @since 3.2
	 *
	 * @param int[] $translations An associative array of translations with language code as key
	 *                            and translation id as value.
	 * @return void
	 */
	private function sync_translation_slugs( array $translations ) {
		if ( empty( $translations ) ) {
			return;
		}

		$def_lang = $this->model->get_default_language();

		if ( empty( $def_lang ) ) {
			// No default language.
			return;
		}

		if ( empty( $translations[ $def_lang->slug ] ) ) {
			// We need the slug of the template in default language.
			return;
		}

		if ( count( $translations ) <= 1 ) {
			// There is nothing to sync this with.
			return;
		}

		$translations = $this->get_translation_posts( $translations );

		if ( empty( $translations ) ) {
			// No templates.
			return;
		}

		$def_post = ! empty( $translations[ $def_lang->slug ] ) ? $translations[ $def_lang->slug ] : false;

		if ( empty( $def_post ) || ! PLL_FSE_Tools::is_template_post_type( $def_post->post_type ) ) {
			// We need the slug of the template in default language.
			return;
		}

		$post_name_instance = new PLL_FSE_Template_Slug( $def_post->post_name, $this->get_languages_slugs() );

		foreach ( $translations as $lang_slug => $translation_post ) {
			if ( $lang_slug === $def_lang->slug ) {
				$translation_name = $post_name_instance->remove_language();
			} else {
				$translation_name = $post_name_instance->update_language( $lang_slug );
			}

			if ( $translation_name === $translation_post->post_name ) {
				// Nothing to update.
				continue;
			}

			// Update the template slug.
			$this->update_template_slug( $translation_post, $translation_name );
		}
	}

	/**
	 * Returns translation posts.
	 *
	 * @since 3.2
	 *
	 * @param int[] $translations An associative array of translations with language code as key
	 *                            and translation id as value.
	 * @return WP_Post[] An associative array of translations with language code as key
	 *                   and WP_Post object as value.
	 */
	private function get_translation_posts( array $translations ) {
		/** @var WP_Post[] */
		$posts = ( new WP_Query() )->query(
			array(
				'post__in'               => $translations,
				'posts_per_page'         => count( $translations ),
				'post_type'              => PLL_FSE_Tools::get_template_post_types(),
				'ignore_sticky_posts'    => true,
				'update_post_meta_cache' => false,
				'lang'                   => '',
			)
		);

		// Organize post objects by language.
		$translation_posts = array();
		$translations      = array_flip( $translations );

		foreach ( $posts as $post ) {
			if ( isset( $translations[ $post->ID ] ) ) {
				$translation_posts[ $translations[ $post->ID ] ] = $post;
			}
		}

		return $translation_posts;
	}
}
