<?php
/**
 * @package Polylang-Pro
 */

/**
 * Smart copy of post content
 *
 * @since 2.6
 */
class PLL_Sync_Content {
	/**
	 * Stores the plugin options.
	 *
	 * @var \WP_Syntex\Polylang\Options\Options
	 */
	protected $options;

	/**
	 * @var PLL_Model
	 */
	protected $model;

	/**
	 * Instance of a child class of PLL_Links_Model.
	 *
	 * @var PLL_Links_Model
	 */
	protected $links_model;

	/**
	 * Shortcodes translator.
	 *
	 * @var PLL_Sync_Shortcodes
	 */
	protected $shortcodes;

	/**
	 * HTML translator.
	 *
	 * @var PLL_Sync_HTML
	 */
	protected $html;

	/**
	 * Constructor
	 *
	 * @since 1.9
	 *
	 * @param PLL_Frontend|PLL_Admin|PLL_Settings|PLL_REST_Request $polylang Polylang object.
	 */
	public function __construct( &$polylang ) {
		$this->options    = &$polylang->model->options;
		$this->model      = &$polylang->model;
		$sync_ids         = new PLL_Sync_Ids( $this->model );
		$this->shortcodes = new PLL_Sync_Shortcodes( $sync_ids );
		$this->html       = new PLL_Sync_HTML( $sync_ids );
	}

	/**
	 * Copy the content from one post to the other
	 *
	 * @since 1.9
	 *
	 * @param WP_Post             $from_post       The post to copy from.
	 * @param WP_Post             $target_post     The post to copy to.
	 * @param PLL_Language|string $target_language The language of the post to copy to.
	 * @return WP_Post
	 */
	public function copy_content( $from_post, $target_post, $target_language ) {
		$from_language   = $this->model->post->get_language( $from_post->ID );
		$target_language = $this->model->get_language( $target_language );

		if ( ! $from_language || ! $target_language ) {
			return $target_post;
		}

		$target_post->post_title = $from_post->post_title;
		$target_post->post_name  = wp_unique_post_slug(
			$from_post->post_name,
			$target_post->ID,
			$target_post->post_status,
			$target_post->post_type,
			$target_post->post_parent
		);
		$target_post->post_excerpt = $this->translate(
			$from_post->post_excerpt,
			$target_language,
			$target_post,
			$from_post
		);
		$target_post->post_content = $this->translate(
			$from_post->post_content,
			$target_language,
			$target_post,
			$from_post
		);

		return $target_post;
	}

	/**
	 * Translates shortcodes, HTML and blocks in text.
	 *
	 * @since 1.9
	 * @since 3.3 Requires $target_post, $from_language and $target_language parameters.
	 * @since 3.7 Deprecate `$from_language` argument and move `$target_language` to third position.
	 *
	 * @param string       $content         Text to translate.
	 * @param ?WP_Post     $target_post     The post object to translate to, pass `null` if the content is not post related.
	 * @param PLL_Language $target_language The language to translate to.
	 * @param PLL_Language $deprecated      Deprecated target language object for backward compatibility.
	 * @return string Translated text
	 */
	public function translate_content( $content, ?WP_Post $target_post, PLL_Language $target_language, ?PLL_Language $deprecated = null ) {
		if ( $deprecated instanceof PLL_Language ) {
			$target_language = $deprecated;

			_deprecated_argument(
				__METHOD__,
				'3.7',
				'You must use only 3 arguments, `$target_language` as third.'
			);
		}

		return $this->translate( $content, $target_language, $target_post );
	}

	/**
	 * Duplicates the feature image if the translation does not exist yet.
	 *
	 * @since 2.3
	 *
	 * @param int    $id   Thumbnail ID.
	 * @param string $key  Meta key.
	 * @param string $lang Language code.
	 * @return int
	 */
	public function duplicate_thumbnail( $id, $key, $lang ) {
		if ( '_thumbnail_id' !== $key ) {
			return $id;
		}

		$tr_id = $this->model->post->get( $id, $lang );
		if ( empty( $tr_id ) ) {
			$tr_id = $this->model->post->create_media_translation( $id, $lang );
		}

		return empty( $tr_id ) ? $id : $tr_id;
	}

	/**
	 * Duplicates a term if the translation does not exist yet.
	 *
	 * @since 2.3
	 *
	 * @param int    $tr_term_id Translated term id.
	 * @param int    $term_id    Source term id.
	 * @param string $lang       Language slug.
	 * @return int The translated term id. O on failure.
	 */
	public function duplicate_term( $tr_term_id, $term_id, $lang ) {
		if ( empty( $tr_term_id ) ) {
			$term = get_term( $term_id );

			if ( $term instanceof WP_Term ) {
				$language = $this->model->term->get_language( $term->term_id );

				if ( $language && $language->slug !== $lang ) { // Create a new term translation only if the source term has a language.
					$tr_parent = empty( $term->parent ) ? 0 : (int) $this->model->term->get_translation( $term->parent, $lang );

					// Duplicate the parent if the parent translation doesn't exist yet.
					if ( empty( $tr_parent ) && ! empty( $term->parent ) ) {
						$tr_parent = $this->duplicate_term( 0, $term->parent, $lang );
					}

					$args = array(
						'description' => wp_slash( $term->description ),
						'parent'      => $tr_parent,
					);

					if ( $this->options['force_lang'] ) {
						// Share slugs
						$args['slug'] = $term->slug . '___' . $lang;
					} else {
						// Language set from the content: assign a different slug
						// otherwise we would change the current term language instead of creating a new term
						$args['slug'] = sanitize_title( $term->name ) . '-' . $lang;
					}

					$t = wp_insert_term( wp_slash( $term->name ), $term->taxonomy, $args );

					$tr_term_id = 0;

					if ( is_array( $t ) ) {
						$tr_term_id = $t['term_id'];
						$this->model->term->set_language( $tr_term_id, $lang );
						$translations = $this->model->term->get_translations( $term->term_id );
						$translations[ $lang ] = $tr_term_id;
						$this->model->term->save_translations( $term->term_id, $translations );

						/**
						 * Fires after a term translation is automatically created when duplicating a post.
						 *
						 * @since 2.3.8
						 *
						 * @param int    $from Term ID of the source term.
						 * @param int    $to   Term ID of the new term translation.
						 * @param string $lang Language code of the new translation.
						 */
						do_action( 'pll_duplicate_term', $term->term_id, $tr_term_id, $lang );
					}
				}
			}
		}
		return $tr_term_id;
	}

	/**
	 * Translates a piece of content, using source post context if available.
	 *
	 * @since 3.7
	 *
	 * @param string       $content         Content to translate.
	 * @param PLL_Language $target_language The language to translate to.
	 * @param WP_Post|null $target_post     The target post object, pass `null` if the content is not post related. Default to `null`.
	 * @param WP_Post|null $source_post     The source post object, pass `null` if the content doesn't need this context. Default to `null`.
	 * @return string Translated content.
	 */
	private function translate( $content, PLL_Language $target_language, ?WP_Post $target_post = null, ?WP_Post $source_post = null ) {
		if ( has_blocks( $content ) ) {
			$content = ( new PLL_Sync_Blocks( $this->shortcodes, $this->html, $source_post ) )->translate( $content, $target_language, $target_post );
		} else {
			$content = $this->html->translate(
				$this->shortcodes->translate( $content, $target_language, $target_post ),
				$target_language,
				$target_post
			);
		}

		return $content;
	}
}
