<?php
/**
 * @package Polylang-Pro
 */

defined( 'ABSPATH' ) || exit;

/**
 * A class that re-assigns to templates a language that has just been re-created.
 * When a language is deleted, the language suffix is not removed from the template slugs (see
 * `PLL_FSE_Template_Slug_Sync->modify_template_slug_on_lang_assigning()`). When this language is re-created later
 * (deleted by mistake?), it is re-assigned to the templates (as long as it uses the same slug than before its
 * deletion).
 *
 * @since 3.2
 */
class PLL_FSE_Recreate_Language extends PLL_FSE_Abstract_Module implements PLL_Module_Interface {

	/**
	 * Instance of `PLL_Admin_Model`.
	 *
	 * @var PLL_Admin_Model
	 */
	protected $model;

	/**
	 * Returns the module's name.
	 *
	 * @since 3.2
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'fse_recreate_language';
	}

	/**
	 * Sub-module init.
	 *
	 * @since 3.2
	 *
	 * @return self
	 */
	public function init() {
		add_action( 'pll_add_language', array( $this, 'reassign_language' ) );
		return $this;
	}

	/**
	 * Re-assigns a language to templates upon its re-creation.
	 *
	 * @since  3.2
	 * @global wpdb $wpdb
	 *
	 * @param array $args {
	 *     Arguments used to create the language.
	 *
	 *     @type string $name           Language name (used only for display).
	 *     @type string $slug           Language code (ideally 2-letters ISO 639-1 language code).
	 *     @type string $locale         WordPress locale.
	 *     @type int    $rtl            1 if rtl language, 0 otherwise.
	 *     @type int    $term_group     Language order when displayed.
	 *     @type string $no_default_cat Optional, if set, no default category will be created for this language.
	 *     @type string $flag           Optional, country code, @see flags.php.
	 * }
	 * @return void
	 *
	 * @phpstan-param array{name:string,slug:string,locale:string,rtl:int<0,1>,term_group:positive-int,no_default_cat?:string,flag?:string} $args
	 */
	public function reassign_language( $args ) {
		global $wpdb;

		if ( empty( $this->options['default_lang'] ) || $args['slug'] === $this->options['default_lang'] ) {
			// Default language.
			return;
		}

		$def_lang = $this->model->get_default_language();
		$new_lang = $this->model->get_language( $args['slug'] );

		if ( empty( $def_lang ) || empty( $new_lang ) ) {
			// Uh?
			return;
		}

		// Post IDs of posts in new language, post names of posts in default language.
		$post_names_by_post_ids_by_theme_id = $this->get_posts_with_lang_suffix( $new_lang );

		if ( empty( $post_names_by_post_ids_by_theme_id ) ) {
			// No templates found.
			return;
		}

		/**
		 * We must work "by theme" because template slugs can be identical from 1 theme to another.
		 * When updating translation groups, `PLL_Admin_Model->set_translation_in_mass()` cannot be used because it adds
		 * new translations instead of updating the existing ones.
		 */
		$set_language_post_ids  = array(); // Used to assign the language to newly bound templates.
		$clean_cache_post_ids   = array(); // Used to clean the posts cache.
		$clean_cache_term_ids   = array(); // Used to clean the terms cache.
		$all_relationships      = array(); // Used to assign the translation groups to newly bound templates.
		$create_groups_post_ids = array(); // Used to create new translation groups when they don't exist yet.

		foreach ( $post_names_by_post_ids_by_theme_id as $theme_id => $post_names_by_post_ids ) {
			// Get posts by their post_name and theme ID.
			$results = $this->get_translation_groups( $post_names_by_post_ids, $theme_id );

			if ( empty( $results ) ) {
				// Should not happen.
				continue;
			}

			// Update translation groups and/or create new ones.
			$post_ids_by_post_names = array_flip( $post_names_by_post_ids ); // Prevents multiple uses of `array_search()`.
			$tt_ids                 = array(); // Used to update the existing translation groups.
			$query                  = array( // Used to build the query that will update the existing translation groups.
				"UPDATE {$wpdb->term_taxonomy} SET description = (",
				'CASE term_taxonomy_id',
			);

			foreach ( $results as $result ) {
				$new_lang_post_id        = $post_ids_by_post_names[ $result['post_name'] ];
				$set_language_post_ids[] = $new_lang_post_id;

				if ( empty( $result['tt_id'] ) ) {
					// The translation group doesn't exist yet.
					$clean_cache_post_ids[]   = $new_lang_post_id;
					$clean_cache_post_ids[]   = $result['post_id'];
					$create_groups_post_ids[] = array(
						$def_lang->slug => $result['post_id'],
						$new_lang->slug => $new_lang_post_id,
					);
					continue;
				}

				// The translation group exist: update it.
				$result['translations'][ $new_lang->slug ] = $new_lang_post_id;

				$clean_cache_post_ids = array_merge( $clean_cache_post_ids, array_values( $result['translations'] ) );

				$tt_ids[]            = $result['tt_id'];
				$all_relationships[] = $wpdb->prepare( '(%d,%d)', $new_lang_post_id, $result['tt_id'] );
				$query[]             = $wpdb->prepare( 'WHEN %d THEN %s', $result['tt_id'], maybe_serialize( $result['translations'] ) );
			}

			if ( ! empty( $tt_ids ) ) {
				$query[] = 'END';
				$query[] = ')';
				$query[] = 'WHERE term_taxonomy_id IN (' . PLL_Db_Tools::prepare_values_list( $tt_ids ) . ')';

				$wpdb->query( implode( "\n", $query ) ); // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared

				$clean_cache_term_ids = array_merge( $clean_cache_term_ids, $tt_ids );
			}
		}

		if ( ! empty( $set_language_post_ids ) ) {
			// Assign the language.
			$this->model->post->set_language_in_mass( $set_language_post_ids, $new_lang );
		}

		if ( ! empty( $all_relationships ) ) {
			// Assign the translation groups.
			$wpdb->query( "INSERT INTO {$wpdb->term_relationships} (object_id, term_taxonomy_id) VALUES " . implode( ',', $all_relationships ) );
		}

		if ( ! empty( $create_groups_post_ids ) ) {
			// Create new translation groups.
			$this->model->post->set_translation_in_mass( $create_groups_post_ids );
		}

		if ( ! empty( $clean_cache_post_ids ) ) {
			// Clean posts cache.
			clean_object_term_cache( $clean_cache_post_ids, PLL_FSE_Tools::get_template_post_types() );
		}

		if ( ! empty( $clean_cache_term_ids ) ) {
			// Clean terms cache.
			clean_term_cache( $clean_cache_term_ids, '', false );
		}
	}

	/**
	 * Returns a list of templates that have a post_name suffixed with the given lang slug.
	 *
	 * @since 3.2
	 *
	 * @param PLL_Language $language A language object.
	 * @return string[][] A set of post IDs and post names, grouped by theme ID (term_taxonomy_id of the
	 *                    'wp_theme' taxonomy). Each result uses the post ID as array key and the post name
	 *                    (without lang suffix) as array value.
	 *                    Example of returned value:
	 *                    array(
	 *                        {theme_id} => array(
	 *                            {post_id} => '{post_name}',
	 *                            {post_id} => '{post_name}',
	 *                        ),
	 *                        {theme_id} => array(
	 *                            {post_id} => '{post_name}',
	 *                        ),
	 *                    )
	 */
	private function get_posts_with_lang_suffix( PLL_Language $language ) {
		global $wpdb;

		$post_types = PLL_Db_Tools::prepare_values_list( PLL_FSE_Tools::get_template_post_types() );

		// 'dummy' => 'dummy___fr' => 'dummy\_\_\_fr' => '%\_\_\_fr' - No it's not commented code damnit.
		$post_slug = ( new PLL_FSE_Template_Slug( 'dummy' ) )->update_language( $language->slug );
		$post_slug = str_replace( 'dummy', '%', $wpdb->esc_like( $post_slug ) );

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT p.ID as post_id, p.post_name, tt.term_taxonomy_id as theme_id
					FROM {$wpdb->posts} AS p
				INNER JOIN {$wpdb->term_relationships} AS tr
					ON tr.object_id = p.ID
				INNER JOIN {$wpdb->term_taxonomy} AS tt
					ON tt.term_taxonomy_id = tr.term_taxonomy_id
					AND tt.taxonomy = 'wp_theme'
				WHERE p.post_type IN ($post_types)
					AND post_name LIKE %s",
				$post_slug
			),
			ARRAY_A
		);
		// phpcs:enable

		if ( empty( $results ) ) {
			return array();
		}

		$language_slugs      = $this->get_languages_slugs();
		$post_names_by_post_ids_by_theme_id = array();

		foreach ( $results as $result ) {
			$theme_id  = $this->model->post->sanitize_int_id( $result['theme_id'] );
			$post_id   = $this->model->post->sanitize_int_id( $result['post_id'] );
			$post_name = ( new PLL_FSE_Template_Slug( $result['post_name'], $language_slugs ) )->remove_language();  // We need the post_name without lang suffix to find the template in default language.

			$post_names_by_post_ids_by_theme_id[ $theme_id ][ $post_id ] = $post_name;
		}

		return $post_names_by_post_ids_by_theme_id;
	}

	/**
	 * Returns a list of translation groups belonging to templates that use the given post_names within the given theme
	 * ID. This will also return posts that don't have a translation group.
	 *
	 * @since 3.2
	 *
	 * @param  string[] $post_names List of post names.
	 * @param  int      $theme_id   `term_taxonomy_id` of the 'wp_theme' taxonomy.
	 * @return array[]              {
	 *     Array of arrays containing the translation groups and their `term_taxonomy_id`.
	 *
	 *     @type int           $post_id      Post ID (post in default language).
	 *     @type string        $post_name    Post slug.
	 *     @type int           $tt_id        `term_taxonomy_id` of the 'post_translations' taxonomy.`0` means the term
	 *                                       doesn't exist.
	 *     @type string[]      $translations List of translations. IDs are not sanitized to keep it simple (the array
	 *                                       can contain other things).
	 * }
	 *
	 * @phpstan-return array<array{post_id:int<0,max>,post_name:string,tt_id:int<0,max>,translations:array<string>}>
	 */
	private function get_translation_groups( array $post_names, $theme_id ) {
		global $wpdb;

		$post_types = PLL_Db_Tools::prepare_values_list( PLL_FSE_Tools::get_template_post_types() );
		$post_names = PLL_Db_Tools::prepare_values_list( $post_names );

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT p.ID as post_id, p.post_name, tt.term_taxonomy_id as tt_id, tt.description as translations
					FROM {$wpdb->posts} AS p

				LEFT JOIN (
					SELECT tr1.object_id, tt1.term_taxonomy_id, tt1.description
						FROM {$wpdb->term_relationships} AS tr1
					INNER JOIN {$wpdb->term_taxonomy} AS tt1
						ON tt1.term_taxonomy_id = tr1.term_taxonomy_id
						AND tt1.taxonomy = 'post_translations'
				) AS tt
					ON tt.object_id = p.ID

				INNER JOIN {$wpdb->term_relationships} AS tr
					ON tr.object_id = p.ID
					AND tr.term_taxonomy_id = %d

				WHERE p.post_type IN ($post_types)
					AND post_name IN ($post_names)",
				$theme_id
			),
			ARRAY_A
		);
		// phpcs:enable

		return array_map(
			function ( $result ) {
				$result['post_id']      = $this->model->post->sanitize_int_id( $result['post_id'] );
				$result['tt_id']        = $this->model->term->sanitize_int_id( $result['tt_id'] );
				$result['translations'] = $this->format_translations( $result['translations'] );
				return $result;
			},
			(array) $results
		);
	}

	/**
	 * Formats a raw translation group from the database into an array.
	 *
	 * @since 3.2
	 *
	 * @param mixed $translations Raw translation group from the database.
	 * @return string[] Translation IDs are not sanitized to keep it simple
	 *                  (the array can contain other things).
	 */
	private function format_translations( $translations ) {
		if ( empty( $translations ) ) {
			return array();
		}

		$translations = maybe_unserialize( $translations );

		return is_array( $translations ) ? $translations : array();
	}
}
