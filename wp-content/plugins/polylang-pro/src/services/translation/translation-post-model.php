<?php
/**
 * @package Polylang-Pro
 */

/**
 * Manages posts translations.
 *
 * @since 3.3
 *
 * @phpstan-type EntryData array{
 *    id: int,
 *    data: Translations,
 *    fields: array{
 *        post_status?: string,
 *    }
 * }
 */
class PLL_Translation_Post_Model implements PLL_Translation_Data_Model_Interface {
	use PLL_Translation_Object_Model_Trait;

	/**
	 * Used to query languages and translations.
	 *
	 * @var PLL_Model
	 */
	protected $model;

	/**
	 * @var PLL_Sync
	 */
	protected $sync;

	/**
	 * @var PLL_Sync_Post_Model
	 */
	protected $sync_post_model;

	/**
	 * Used to sync the post content.
	 *
	 * @var PLL_Sync_Content
	 */
	protected $sync_content;

	/**
	 * Constructor.
	 *
	 * @since 3.3
	 *
	 * @param PLL_Settings|PLL_Admin $polylang Polylang object.
	 */
	public function __construct( &$polylang ) {
		$this->model           = &$polylang->model;
		$this->sync            = &$polylang->sync;
		$this->sync_post_model = &$polylang->sync_post_model;
		$this->sync_content    = &$polylang->sync_content;
	}

	/**
	 * Translates a post in a given language.
	 *
	 * @since 3.3
	 *
	 * @param  array        $entry           {
	 *    Import data.
	 *    int          $id     Source object ID.
	 *    Translations $data   Object containing translated data.
	 *    array        $fields {
	 *        Fields to create the new translation.
	 *        string $post_status Post status to use during translation creation.
	 *    }
	 * }.
	 * @param  PLL_Language $target_language A language to translate into.
	 * @return int|WP_Error The translated post ID, `WP_Error` on failure.
	 *
	 * @phpstan-param EntryData $entry
	 */
	public function translate( array $entry, PLL_Language $target_language ) {
		if ( ! $entry['data'] instanceof Translations ) {
			/* translators: %d is a post ID. */
			return new WP_Error( 'pll_translate_post_no_translations', sprintf( __( 'The post with ID %d could not be translated.', 'polylang-pro' ), (int) $entry['id'] ) );
		}

		$source_post = get_post( $entry['id'] );
		if ( ! $source_post instanceof WP_Post || ! $source_post->ID ) {
			/* translators: %d is a post ID. */
			return new WP_Error( 'pll_translate_post_no_source_post', sprintf( __( 'The post with ID %d could not be translated as it doesn\'t exist.', 'polylang-pro' ), (int) $entry['id'] ) );
		}

		$tr_post_id = $this->model->post->get( $entry['id'], $target_language );
		$tr_post    = $tr_post_id ? get_post( $tr_post_id ) : null;

		$translation_exists = $tr_post instanceof WP_Post;

		if ( $translation_exists ) {
			$tr_post = $this->update_post_translation(
				$entry,
				$source_post,
				$target_language,
				$tr_post
			);
		} else {
			$tr_post = $this->create_post_translation(
				$entry,
				$source_post,
				$target_language
			);
		}

		if ( ! $tr_post instanceof WP_Post ) {
			/* translators: %d is a post ID. */
			return new WP_Error( 'pll_translate_post_failed', sprintf( __( 'The post with ID %d could not be translated.', 'polylang-pro' ), (int) $entry['id'] ) );
		}

		// Fix for `term_exists()`.
		add_filter( 'term_exists_default_query_args', array( $this, 'term_exists_default_query_args' ), 10, 3 );

		$this->sync->taxonomies->copy( $source_post->ID, $tr_post->ID, $target_language->slug );
		( new PLL_Translation_Post_Metas( $this->sync->post_metas, $entry['data'] ) )
			->translate( $source_post->ID, $tr_post->ID, $target_language, ! $translation_exists );

		/**
		 * Fires once a post has been translated.
		 *
		 * @since 3.7
		 *
		 * @param WP_Post      $source_post     The source post.
		 * @param WP_Post      $tr_post         The target post.
		 * @param PLL_Language $target_language The language to translate into.
		 * @param Translations $translations    The set of translations for the entry.
		 */
		do_action( 'pll_after_post_translation', $source_post, $tr_post, $target_language, $entry['data'] );

		/** This action is documented in polylang/src/crud-posts.php. */
		do_action( 'pll_save_post', $tr_post->ID, $tr_post, $this->model->post->get_translations( $tr_post->ID ) ); // Triggers the the post metas synchronization.

		return $tr_post->ID;
	}

	/**
	 * Creates a new post translation.
	 *
	 * @since 3.3
	 *
	 * @param array        $data_import     Import data, @see {self::translate()}.
	 * @param WP_Post      $source_post     The source post object.
	 * @param PLL_Language $target_language The language to translate into.
	 * @return WP_Post|null The translated post object, `null` on failure.
	 *
	 * @phpstan-param EntryData $data_import
	 */
	protected function create_post_translation( array $data_import, WP_Post $source_post, PLL_Language $target_language ): ?WP_Post {
		$tr_post = get_default_post_to_edit( $source_post->post_type, true );
		$this->model->post->set_language( $tr_post->ID, $target_language ); // Do it now to share slug.

		$tr_post = $this->copy_source_post( $source_post, $tr_post );

		$tr_post = $this->translate_content( $data_import, $source_post, $target_language, $tr_post );

		if ( ! $tr_post instanceof WP_Post ) {
			return null;
		}

		// Set post status.
		$tr_post->post_status = $data_import['fields']['post_status'] ?? 'draft';

		/**
		 * Filters the translated post before it is created.
		 *
		 * Allows to modify any property of the translated post, such as status, etc.
		 *
		 * @since 3.7.8
		 *
		 * @param WP_Post      $tr_post         The translated post object.
		 * @param WP_Post      $source_post     The source post object.
		 * @param PLL_Language $target_language The target language.
		 */
		$tr_post = apply_filters( 'pll_create_post_translation', $tr_post, $source_post, $target_language );

		$tr_post_args = $tr_post->to_array();

		$tr_id = wp_update_post( wp_slash( $tr_post_args ) );
		if ( ! $tr_id ) {
			// Failure during post update.
			return null;
		}

		$this->save_translations_group( $source_post->ID, $tr_post->ID, $target_language->slug );

		return get_post( $tr_id );
	}

	/**
	 * Saves the translations group.
	 *
	 * @since 3.3
	 *
	 * @param int    $from_id The post source id.
	 * @param int    $tr_id   The translated post id.
	 * @param string $lang    The language slug of the translated post.
	 * @return void
	 */
	protected function save_translations_group( $from_id, $tr_id, $lang ) {
		$translations          = $this->model->post->get_translations( $from_id );
		$translations[ $lang ] = $tr_id;
		$this->model->post->save_translations( $from_id, $translations );
	}

	/**
	 * Updates an existing post translation.
	 *
	 * @since 3.3
	 * @since 3.7 $data_import parameter added.
	 *
	 * @param array        $data_import     Import data, @see {self::translate()}.
	 * @param WP_Post      $source_post     The source post object.
	 * @param PLL_Language $target_language The language to translate into.
	 * @param WP_Post      $tr_post         The translated post object.
	 * @return WP_Post|null The translated post object, `null` on failure.
	 *
	 * @phpstan-param EntryData $data_import
	 */
	protected function update_post_translation( array $data_import, WP_Post $source_post, PLL_Language $target_language, WP_Post $tr_post ): ?WP_Post {
		$this->maybe_unsync_posts( $source_post->ID, $tr_post->ID, $target_language );
		$tr_post = $this->translate_content( $data_import, $source_post, $target_language, $tr_post );

		if ( ! $tr_post instanceof WP_Post ) {
			return null;
		}

		return get_post(
			wp_update_post( $tr_post )
		);
	}

	/**
	 * Translates all content type of a post (i.e. title, excerpt and content).
	 *
	 * @since 3.3
	 * @since 3.7 $translations parameter added.
	 *
	 * @param array        $data_import     Import data, @see {self::translate()}.
	 * @param WP_Post      $source_post     The source post object.
	 * @param PLL_Language $target_language The language to translate into.
	 * @param WP_Post      $tr_post         The translated post object.
	 * @return WP_Post|null The translated post object populated with new data. Null otherwise.
	 */
	protected function translate_content( array $data_import, WP_Post $source_post, PLL_Language $target_language, WP_Post $tr_post ): ?WP_Post {
		$translate_content     = new PLL_Translation_Content( $data_import['data'] );
		$tr_post->post_title   = $translate_content->translate_title( $source_post->post_title );
		$tr_post->post_excerpt = $translate_content->translate_excerpt( $source_post->post_excerpt );
		$tr_post->post_content = $this->sync_content->translate_content(
			$translate_content->translate_content( $source_post->post_content ),
			$tr_post,
			$target_language
		);

		/**
		 * Filters a translated post before it is saved.
		 *
		 * @since 3.7
		 *
		 * @param WP_Post      $tr_post         The target post.
		 * @param WP_Post      $source_post     The source post.
		 * @param PLL_Language $target_language The language to translate into.
		 * @param Translations $translations    The set of translations for the entry..
		 */
		$tr_post = apply_filters( 'pll_filter_translated_post', $tr_post, $source_post, $target_language, $data_import['data'] );

		return $tr_post instanceof WP_Post ? $tr_post : null;
	}

	/**
	 * Copy the source post data in the translated post.
	 *
	 * @since 3.3
	 * @since 3.4 Renamed from `clone_source_post` and added second parameter `$tr_post`.
	 *
	 * @param WP_Post $source_post The Source Post.
	 * @param WP_Post $tr_post     The translated Post.
	 * @return WP_Post The translated post.
	 */
	protected function copy_source_post( $source_post, $tr_post ) {
		// The columns to copy.
		$columns = array(
			'post_author',
			'post_content',
			'post_title',
			'post_excerpt',
			'comment_status',
			'ping_status',
			'post_parent',
			'menu_order',
			'post_mime_type',
			'post_password',
		);

		foreach ( $columns as $column ) {
			$tr_post->{$column} = $source_post->{$column};
		}

		return $tr_post;
	}

	/**
	 * Assigns the parents to posts creating during the import.
	 *
	 * @since 3.3
	 * @since 3.7 Renamed from `translate_parents` and made private.
	 *
	 * @param int[]        $ids             Array of source post ids.
	 * @param PLL_Language $target_language The target language.
	 * @return void
	 */
	private function assign_parents( array $ids, PLL_Language $target_language ) {
		// Keep only the posts that have a parent.
		$posts = get_posts(
			array(
				'include'                => $ids,
				'post_type'              => 'any',
				'post_status'            => 'any',
				'post_parent__not_in'    => array( 0 ),
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
				'fields'                 => 'id=>parent',
			)
		);

		if ( empty( $posts ) ) {
			// No posts with parents.
			return;
		}

		$tr_ids = array();
		foreach ( $posts as $child => $post ) {
			$tr_ids[ $child ] = $this->model->post->get( $child, $target_language->slug );
		}
		$tr_ids = array_filter( $tr_ids );

		if ( empty( $tr_ids ) ) {
			// No translations.
			return;
		}

		foreach ( $posts as $child => $post ) {
			if ( empty( $tr_ids[ $child ] ) ) {
				// Not translated.
				continue;
			}

			$tr_parent_post = $this->model->post->get( $post, $target_language->slug );

			if ( empty( $tr_parent_post ) ) {
				// The parent post is not translated.
				continue;
			}

			wp_update_post(
				array(
					'ID'          => $tr_ids[ $child ],
					'post_parent' => $tr_parent_post,
				)
			);
		}
	}

	/**
	 * Filters default query arguments when checking if a term exists.
	 * In `term_exists()`, WP 6.0 uses `get_terms()`, which is filtered by language by Polylang.
	 * This filter prevents `term_exists()` to be filtered by language.
	 * Copied from PLL_Filters::term_exists_default_query_args
	 *
	 * @since 3.3
	 *
	 * @param array      $defaults An array of arguments passed to get_terms().
	 * @param int|string $term     The term to check. Accepts term ID, slug, or name.
	 * @param string     $taxonomy The taxonomy name to use. An empty string indicates the search is against all taxonomies.
	 * @return array
	 */
	public function term_exists_default_query_args( $defaults, $term, $taxonomy ) {
		if ( ! empty( $taxonomy ) && ! $this->model->is_translated_taxonomy( $taxonomy ) ) {
			return $defaults;
		}

		if ( ! is_array( $defaults ) ) {
			$defaults = array();
		}

		if ( ! isset( $defaults['lang'] ) ) {
			$defaults['lang'] = '';
		}

		return $defaults;
	}

	/**
	 * Unsynchronizes translated post from the source.
	 *
	 * @since 3.3
	 *
	 * @param int          $source_post_id  Source post ID.
	 * @param int          $target_post_id  Translated post ID.
	 * @param PLL_Language $target_language Translated post language object.
	 * @return void
	 */
	protected function maybe_unsync_posts( $source_post_id, $target_post_id, $target_language ) {
		if ( ! $this->sync_post_model->are_synchronized( $source_post_id, $target_post_id ) ) {
			return;
		}

		$sync_posts = $this->sync_post_model->get( $source_post_id );

		if ( ! isset( $sync_posts[ $target_language->slug ] ) || $sync_posts[ $target_language->slug ] !== $target_post_id ) {
			return;
		}

		unset( $sync_posts[ $target_language->slug ] );

		$this->sync_post_model->save_group( $source_post_id, array_keys( $sync_posts ) );
	}
}
