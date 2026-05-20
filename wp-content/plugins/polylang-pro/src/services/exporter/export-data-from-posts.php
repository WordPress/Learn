<?php
/**
 * @package Polylang-Pro
 */

/**
 * Class handling the sending of data contained in posts for export.
 *
 * @since 3.6
 */
class PLL_Export_Data_From_Posts {

	/**
	 * A reference to the current `PLL_Model`.
	 *
	 * @var PLL_Model
	 */
	protected $model;

	/**
	 * Handles posts export.
	 *
	 * @var PLL_Export_Posts
	 */
	protected $export_posts;

	/**
	 * Handles terms export.
	 *
	 * @var PLL_Export_Terms
	 */
	protected $export_terms;

	/**
	 * The service to collect the linked posts.
	 *
	 * @var PLL_Collect_Linked_Posts
	 */
	protected $collect_posts;

	/**
	 * The service to collect the linked terms.
	 *
	 * @var PLL_Collect_Linked_Terms
	 */
	protected $collect_terms;

	/**
	 * PLL_Export_Items constructor.
	 *
	 * @since 3.6
	 *
	 * @param PLL_Model $model Used to query languages and post translations.
	 */
	public function __construct( PLL_Model $model ) {
		$this->model = $model;

		$this->export_posts  = new PLL_Export_Posts( $this->model->post );
		$this->export_terms  = new PLL_Export_Terms( $this->model->term );
		$this->collect_posts = new PLL_Collect_Linked_Posts( $this->model->options );
		$this->collect_terms = new PLL_Collect_Linked_Terms();
	}

	/**
	 * Exports the items with their related ones.
	 *
	 * @since 3.6
	 *
	 * @param PLL_Export_Container $container       Data to export.
	 * @param WP_Post[]            $posts           An array containing `WP_Post` objects.
	 * @param PLL_Language         $target_language The target language of the posts.
	 * @param array                $args           {
	 *     Optional. A list of optional arguments.
	 *
	 *     @type bool $include_translated_items Tells if items that are already translated in the target languages must
	 *                                          also be exported. This applies only to linked items (like assigned
	 *                                          terms, items from reusable blocks, etc). Default is false.
	 * }
	 * @return void
	 */
	public function send_to_export( PLL_Export_Container $container, array $posts, PLL_Language $target_language, array $args = array() ) {
		$posts_to_export = $this->get_posts_to_export( $posts, $target_language, $args );
		$this->export_posts->add_items( $container, $posts_to_export, $target_language );

		$terms_to_export = $this->get_terms_to_export( $posts_to_export, $target_language, $args );
		$this->export_terms->add_items( $container, $terms_to_export, $target_language );
	}

	/**
	 * Gets all posts to be exported.
	 *
	 * @since 3.6
	 *
	 * @param WP_Post[]    $posts An array of posts.
	 * @param PLL_Language $lang  The target language of the posts.
	 * @param array        $args  A list of optional arguments.
	 * @return WP_Post[]
	 */
	protected function get_posts_to_export( array $posts, PLL_Language $lang, array $args = array() ): array {
		$include_translated_items = ! empty( $args['include_translated_items'] );
		$post_types               = $this->model->get_translated_post_types();
		$collected_posts          = array_merge(
			$posts,
			$this->collect_posts->get_linked_posts( $posts, $post_types )
		);

		if ( ! $include_translated_items ) {
			// Remove items that are already translated in this language.
			foreach ( $collected_posts as $i => $linked_post ) {
				if ( $this->model->post->get_translation( $linked_post->ID, $lang ) ) {
					// A translation already exists.
					unset( $collected_posts[ $i ] );
				}
			}
		}

		/**
		 * Filters the posts to export.
		 *
		 * Allows filtering the complete list of posts before export,
		 * after all posts have been collected (main posts and linked posts).
		 *
		 * @since 3.8
		 *
		 * @param WP_Post[] $collected_posts Posts to export.
		 * @param WP_Post[] $posts           The original posts being exported.
		 */
		$collected_posts = apply_filters( 'pll_export_posts', $collected_posts, $posts );

		return $this->export_posts->remove_duplicate_items( $collected_posts );
	}

	/**
	 * Gets all terms to be exported.
	 *
	 * @since 3.6
	 *
	 * @param WP_Post[]    $posts An array of posts.
	 * @param PLL_Language $lang  The target language of the posts.
	 * @param array        $args  A list of optional arguments.
	 * @return WP_Term[]
	 */
	protected function get_terms_to_export( array $posts, PLL_Language $lang, array $args = array() ): array {
		$include_translated_items = ! empty( $args['include_translated_items'] );
		$taxonomies               = $this->model->get_translated_taxonomies();

		// Collect terms in posts.
		$collected_terms = $this->collect_terms->get_linked_terms( $posts, $taxonomies );

		if ( ! $include_translated_items ) {
			// Remove items that are already translated in this language.
			foreach ( $collected_terms as $i => $term ) {
				if ( $this->model->term->get_translation( $term->term_id, $lang ) ) {
					// A translation already exists.
					unset( $collected_terms[ $i ] );
				}
			}
		}

		/**
		 * Filters the terms to export.
		 *
		 * Allows filtering the complete list of terms before export,
		 * after all terms have been collected (assigned, from content, and parents).
		 *
		 * @since 3.8
		 *
		 * @param WP_Term[] $collected_terms Terms to export.
		 * @param WP_Post[] $posts           The posts being exported.
		 */
		$collected_terms = apply_filters( 'pll_export_terms', $collected_terms, $posts );

		return $this->export_terms->remove_duplicate_items( $collected_terms );
	}
}
