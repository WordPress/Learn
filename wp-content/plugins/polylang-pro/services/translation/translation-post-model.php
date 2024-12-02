<?php
/**
 * @package Polylang-Pro
 */

/**
 * Manages posts translations.
 *
 * @since 3.3
 */
class PLL_Translation_Post_Model implements PLL_Translation_Object_Model_Interface {
	/**
	 * @var PLL_Translation_Content
	 */
	protected $translate_content;

	/**
	 * @var PLL_Translation_Post_Metas
	 */
	protected $translate_post_metas;

	/**
	 * @var PLL_Sync_Content
	 */
	protected $sync_content;

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
	 * Service to manage user capabilities, espcecially 'unfiltered_html'.
	 *
	 * @var PLL_Manage_User_Capabilities
	 */
	protected $user_capabilities_manager;

	/**
	 * Constructor.
	 *
	 * @since 3.3
	 *
	 * @param PLL_Settings|PLL_Admin $polylang Polylang object.
	 */
	public function __construct( &$polylang ) {
		$this->translate_content         = new PLL_Translation_Content();
		$this->model                     = &$polylang->model;
		$this->sync_content              = &$polylang->sync_content;
		$this->sync                      = &$polylang->sync;
		$this->sync_post_model           = &$polylang->sync_post_model;
		$this->translate_post_metas      = new PLL_Translation_Post_Metas( $polylang->sync->post_metas );
		$this->user_capabilities_manager = new PLL_Manage_User_Capabilities();
	}

	/**
	 * Translates a post in a given language.
	 *
	 * @since 3.3
	 *
	 * @param  array        $entry           Properties array of an entry.
	 * @param  PLL_Language $target_language A language to translate into.
	 * @return int The translated post ID, 0 on failure.
	 */
	public function translate( array $entry, PLL_Language $target_language ): int {
		$source_post = get_post( $entry['id'] );
		if ( ! $source_post instanceof WP_Post || ! $source_post->ID ) {
			// The source post doesn't exist.
			return 0;
		}

		$tr_post_id = $this->model->post->get( $entry['id'], $target_language );
		$tr_post    = $tr_post_id ? get_post( $tr_post_id ) : null;

		$this->translate_content->set_translations( $entry['data'] );
		$this->translate_post_metas->set_translations( $entry['data'] );
		$this->user_capabilities_manager->forbid_unfiltered_html( $source_post );

		$translation_exists = $tr_post instanceof WP_Post;

		if ( $translation_exists ) {
			$tr_post_id = $this->update_post_translation( $source_post, $tr_post, $target_language );
		} else {
			$tr_post_id = $this->create_post_translation( $entry['fields'], $source_post, $target_language );
			$tr_post    = get_post( $tr_post_id );
		}

		if ( 0 === $tr_post_id || ! $tr_post instanceof WP_Post ) {
			// Something wrong happened during post insertion. No need to go further.
			return $tr_post_id;
		}

		// Fix for `term_exists()`.
		add_filter( 'term_exists_default_query_args', array( $this, 'term_exists_default_query_args' ), 10, 3 );

		$this->sync->taxonomies->copy( $source_post->ID, $tr_post_id, $target_language->slug );
		$this->translate_post_metas->translate( $source_post->ID, $tr_post_id, $target_language, ! $translation_exists );
		$this->user_capabilities_manager->allow_unfiltered_html();

		/** This action is documented in include/crud-posts.php. */
		do_action( 'pll_save_post', $tr_post_id, $tr_post, $this->model->post->get_translations( $tr_post_id ) ); // Triggers the the post metas synchronization.

		return $tr_post_id;
	}

	/**
	 * Creates a new post translation.
	 *
	 * @since 3.3
	 *
	 * @param array        $data_import {
	 *    Import options.
	 *    string $post_status The post status of the imported posts.
	 * }.
	 * @param WP_Post      $source_post     The source post object.
	 * @param PLL_Language $target_language The language to translate into.
	 * @return int The translated post ID, 0 on failure.
	 */
	protected function create_post_translation( $data_import, $source_post, $target_language ) {
		// Creates an auto-draft in DB.
		$tr_post = get_default_post_to_edit( $source_post->post_type, true );
		if ( ! $tr_post instanceof WP_Post ) {
			// Failure during post creation.
			return 0;
		}

		$this->model->post->set_language( $tr_post->ID, $target_language ); // Do it now to share slug.

		$tr_post = $this->copy_source_post( $source_post, $tr_post );

		$tr_post = $this->translate_content( $source_post, $tr_post, $target_language );

		if ( ! $tr_post instanceof WP_Post ) {
			return 0;
		}

		// Set post status in post data.
		$data_import          = wp_parse_args( $data_import, array( 'post_status' => 'draft' ) );
		$tr_post->post_status = $data_import['post_status'];

		$tr_post_args = $tr_post->to_array();

		$tr_id = wp_update_post( wp_slash( $tr_post_args ) );
		if ( ! $tr_id ) {
			// Failure during post update.
			return 0;
		}

		$this->save_translations_group( $source_post->ID, $tr_post->ID, $target_language->slug );

		return $tr_id;
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
	 *
	 * @param WP_Post      $source_post     The source post object.
	 * @param WP_Post      $tr_post         The translated post object.
	 * @param PLL_Language $target_language The language to translate into.
	 * @return int The translated post ID, 0 on failure.
	 */
	protected function update_post_translation( $source_post, $tr_post, $target_language ) {
		$this->maybe_unsync_posts( $source_post->ID, $tr_post->ID, $target_language );
		$tr_post = $this->translate_content( $source_post, $tr_post, $target_language );

		if ( ! $tr_post instanceof WP_Post ) {
			return 0;
		}

		return wp_update_post( $tr_post );
	}

	/**
	 * Translates all content type of a post (i.e. title, excerpt and content).
	 *
	 * @since 3.3
	 *
	 * @param WP_Post      $source_post     The source post object.
	 * @param WP_Post      $tr_post         The translated post object.
	 * @param PLL_Language $target_language The language to translate into.
	 * @return WP_Post|int The translated post object populated with new data. 0 otherwise.
	 */
	protected function translate_content( $source_post, $tr_post, $target_language ) {
		$source_language = $this->model->post->get_language( $source_post->ID );
		if ( ! $source_language instanceof PLL_Language ) {
			// The source post has no language?!
			return 0;
		}
		$tr_post->post_title   = $this->translate_content->translate_title( $source_post->post_title );
		$tr_post->post_excerpt = $this->translate_content->translate_excerpt( $source_post->post_excerpt );
		$tr_post->post_content = $this->sync_content->translate_content(
			$this->translate_content->translate_content( $source_post->post_content ),
			$tr_post,
			$source_language,
			$target_language
		);

		return $tr_post;
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
	 * Translates post parent if there is one.
	 *
	 * @since 3.3
	 *
	 * @param int[]        $ids             Array of source post ids.
	 * @param PLL_Language $target_language The target language.
	 * @return void
	 */
	public function translate_parents( array $ids, PLL_Language $target_language ) {
		$ids = array_filter( $ids );

		if ( empty( $ids ) ) {
			// Invalid list of post IDs.
			return;
		}

		$ids = array_unique( $ids, SORT_NUMERIC );

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
