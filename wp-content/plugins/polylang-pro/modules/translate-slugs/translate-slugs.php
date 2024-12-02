<?php
/**
 * @package Polylang-Pro
 */

/**
 * Modifies links on both frontend and admin side
 *
 * @since 1.9
 */
class PLL_Translate_Slugs {
	/**
	 * @var PLL_Translate_Slugs_Model
	 */
	public $slugs_model;

	/**
	 * Current language.
	 *
	 * @var PLL_Language|null
	 */
	public $curlang;

	/**
	 * Constructor
	 *
	 * @since 1.9
	 *
	 * @param PLL_Translate_Slugs_Model $slugs_model An instance of PLL_Translate_Slugs_Model.
	 * @param PLL_Language              $curlang     The Current language.
	 */
	public function __construct( &$slugs_model, &$curlang ) {
		$this->slugs_model = &$slugs_model;
		$this->curlang     = &$curlang;

		add_filter( 'pll_post_type_link', array( $this, 'pll_post_type_link' ), 10, 3 );
		add_filter( 'pll_term_link', array( $this, 'pll_term_link' ), 10, 3 );
		add_filter( 'post_type_archive_link', array( $this, 'translate_slug' ), 20, 2 );
	}

	/**
	 * Modifies custom post type links.
	 *
	 * @since 1.9
	 *
	 * @param string       $url  The post link.
	 * @param PLL_Language $lang The post language.
	 * @param WP_Post      $post The post object.
	 * @return string
	 */
	public function pll_post_type_link( $url, $lang, $post ) {
		global $wp_rewrite;

		if ( ! empty( $wp_rewrite->front ) && trim( $wp_rewrite->front, '/' ) ) {
			$url = $this->slugs_model->translate_slug( $url, $lang, 'front' );
		}

		return $this->slugs_model->translate_slug( $url, $lang, $post->post_type );
	}

	/**
	 * Modifies term links.
	 *
	 * @since 1.9
	 *
	 * @param string       $url  The term link.
	 * @param PLL_Language $lang The term language.
	 * @param WP_Term      $term The term object.
	 * @return string
	 */
	public function pll_term_link( $url, $lang, $term ) {
		global $wp_rewrite;

		if ( 'post_format' === $term->taxonomy ) {
			$url = $this->slugs_model->translate_slug( $url, $lang, $term->slug ); // Occurs only on frontend.
		}

		if ( ! empty( $wp_rewrite->front ) && trim( $wp_rewrite->front, '/' ) ) {
			$url = $this->slugs_model->translate_slug( $url, $lang, 'front' );
		}

		return $this->slugs_model->translate_slug( $url, $lang, $term->taxonomy );
	}

	/**
	 * Translate the slugs
	 *
	 * The filter was originally only on frontend but is needed on admin too for
	 * compatibility with the archive link of the ACF link field since ACF 5.4.0
	 *
	 * @since 1.9
	 *
	 * @param string $link      The link in which we want to translate a slug.
	 * @param string $post_type Optional, Post type.
	 * @return string Modified link
	 */
	public function translate_slug( $link, $post_type = '' ) {
		global $wp_rewrite;

		if ( empty( $this->curlang ) ) {
			return $link;
		}

		$types = array(
			'post_type_archive_link' => 'archive_' . $post_type,
			'get_pagenum_link'       => 'paged',
			'author_link'            => 'author',
			'attachment_link'        => 'attachment',
			'search_link'            => 'search',
		);

		$link = $this->slugs_model->translate_slug( $link, $this->curlang, $types[ current_filter() ] );

		if ( ! empty( $wp_rewrite->front ) && trim( $wp_rewrite->front, '/' ) ) {
			$link = $this->slugs_model->translate_slug( $link, $this->curlang, 'front' );
		}

		return $link;
	}
}
