<?php
/**
 * @package Polylang-Pro
 */

/**
 * Modifies links on frontend
 *
 * @since 1.9
 */
class PLL_Frontend_Translate_Slugs extends PLL_Translate_Slugs {
	/**
	 * @var PLL_Model
	 */
	public $model;

	/**
	 * Instance of a child class of PLL_Links_Model.
	 *
	 * @var PLL_Links_Model
	 */
	public $links_model;

	/**
	 * Constructor.
	 *
	 * @since 1.9
	 *
	 * @param PLL_Translate_Slugs_Model $slugs_model An instance of PLL_Translate_Slugs_Model.
	 * @param PLL_Language              $curlang     Current language.
	 */
	public function __construct( &$slugs_model, &$curlang ) {
		parent::__construct( $slugs_model, $curlang );

		$this->model       = &$slugs_model->model;
		$this->links_model = &$slugs_model->links_model;

		// Translates slugs in archive link.
		if ( $this->links_model->using_permalinks ) {
			foreach ( array( 'author_link', 'search_link', 'get_pagenum_link', 'attachment_link' ) as $filter ) {
				add_filter( $filter, array( $this, 'translate_slug' ), 20 );
			}
		}

		add_filter( 'pll_get_archive_url', array( $this, 'pll_get_archive_url' ), 10, 2 );
		add_filter( 'pll_check_canonical_url', array( $this, 'pll_check_canonical_url' ), 10, 2 );
		add_action( 'template_redirect', array( $this, 'fix_wp_rewrite' ), 1 ); // After the language is set (when set from content).

		add_filter( 'pll_remove_paged_from_link', array( $this, 'remove_paged_from_link' ), 10, 2 );
		add_filter( 'pll_add_paged_to_link', array( $this, 'add_paged_to_link' ), 10, 3 );
	}

	/**
	 * Translate the slugs in archive urls
	 *
	 * @since 1.9
	 *
	 * @param string       $url      The archive url in which want to translat a slug.
	 * @param PLL_Language $language The language.
	 * @return string Modified url.
	 */
	public function pll_get_archive_url( $url, $language ) {
		global $wp_rewrite;

		if ( is_post_type_archive() ) {
			/** @var WP_Post_Type */
			$post_type = get_queried_object();
			$url = $this->slugs_model->switch_translated_slug( $url, $language, 'archive_' . $post_type->name );
		}

		if ( is_tax( 'post_format' ) ) {
			/** @var WP_Term */
			$term = get_queried_object();

			$url = $this->slugs_model->switch_translated_slug( $url, $language, 'post_format' );
			$url = $this->slugs_model->switch_translated_slug( $url, $language, $term->slug );
		}

		if ( is_author() ) {
			$url = $this->slugs_model->switch_translated_slug( $url, $language, 'author' );
		}

		if ( is_search() ) {
			$url = $this->slugs_model->switch_translated_slug( $url, $language, 'search' );
		}

		if ( ! empty( $wp_rewrite->front ) && trim( $wp_rewrite->front, '/' ) ) {
			$url = $this->slugs_model->switch_translated_slug( $url, $language, 'front' );
		}

		$url = $this->links_model->remove_paged_from_link( $url );

		return $url;
	}

	/**
	 * Modifies the canonical url with the translated slugs.
	 *
	 * @since 1.9
	 *
	 * @param string       $redirect_url The canonical url to redirect to as evaluated in Polylang.
	 * @param PLL_Language $language     The language.
	 * @return string Modified canonical url.
	 */
	public function pll_check_canonical_url( $redirect_url, $language ) {
		global $wp_rewrite;

		$slugs = array();

		if ( is_post_type_archive() ) {
			/** @var WP_Post_Type */
			$obj     = get_queried_object();
			$slugs[] = 'archive_' . $obj->name;
		}

		elseif ( is_single() || is_page() ) {
			$post = get_post();

			if ( isset( $post->ID ) && $this->model->is_translated_post_type( $post->post_type ) ) {
				$slugs[] = $post->post_type;
			}
		}

		elseif ( is_category() || is_tag() || is_tax() ) {
			/** @var WP_Term|null */
			$obj = get_queried_object();

			if ( ! empty( $obj ) ) {
				if ( $this->model->is_translated_taxonomy( $obj->taxonomy ) ) {
					$slugs[] = $obj->taxonomy;
				} elseif ( 'post_format' === $obj->taxonomy ) {
					$slugs[] = 'post_format';
					$slugs[] = $obj->slug;
				}
			}
		}

		elseif ( is_author() ) {
			$slugs[] = 'author';
		}

		elseif ( is_search() ) {
			$slugs[] = 'search';
		}

		if ( is_paged() ) {
			$slugs[] = 'paged';
		}

		if ( is_attachment() ) {
			$slugs[] = 'attachment';
		}

		if ( ! empty( $wp_rewrite->front ) && trim( $wp_rewrite->front, '/' ) ) {
			$slugs[] = 'front';
		}

		foreach ( $slugs as $slug ) {
			$redirect_url = $this->slugs_model->switch_translated_slug( $redirect_url, $language, $slug );
		}

		return $redirect_url;
	}

	/**
	 * Hack rewrite bases of $wp_rewrite
	 * Especially important for the pagination base to avoid WP canonical url breaking
	 *
	 * @since 1.9
	 *
	 * @return void
	 */
	public function fix_wp_rewrite() {
		global $wp_rewrite;

		if ( isset( $this->slugs_model->translated_slugs['author'] ) ) {
			$wp_rewrite->author_base = $this->slugs_model->translated_slugs['author']['translations'][ $this->curlang->slug ];
		}

		if ( isset( $this->slugs_model->translated_slugs['search'] ) ) {
			$wp_rewrite->search_base = $this->slugs_model->translated_slugs['search']['translations'][ $this->curlang->slug ];
		}

		if ( isset( $this->slugs_model->translated_slugs['paged'] ) ) {
			$wp_rewrite->pagination_base = $this->slugs_model->get_translated_slug( 'paged', $this->curlang->slug );
		}
	}

	/**
	 * If the paged slug is translated, PLL_Links_Model::remove_paged_from_link does not work
	 * so here is a replacement
	 *
	 * @since 1.9
	 *
	 * @param string $_link Url to modify.
	 * @param string $link  Original url to modify.
	 * @return string Modified link.
	 */
	public function remove_paged_from_link( $_link, $link ) {
		if ( isset( $this->slugs_model->translated_slugs['paged'] ) ) {
			$slugs   = $this->slugs_model->translated_slugs['paged']['translations'];
			$slugs[] = $this->slugs_model->translated_slugs['paged']['slug'];
			$slugs   = $this->slugs_model->encode_deep( $slugs );

			return preg_replace(
				'#/(' . implode( '|', array_unique( $slugs ) ) . ')/[0-9]+/#',
				'/',
				$link
			);
		}
		return $_link;
	}

	/**
	 * Returns the link to the paged page when translating the 'page' slug
	 *
	 * @since 2.0.6
	 *
	 * @param string $_url Url to modify.
	 * @param string $url  Original url to modify.
	 * @param int    $page The page number.
	 * @return string Modified url.
	 */
	public function add_paged_to_link( $_url, $url, $page ) {
		if ( isset( $this->slugs_model->translated_slugs['paged'] ) ) {
			$slug = $this->slugs_model->get_translated_slug( 'paged', $this->curlang->slug );
			return user_trailingslashit( trailingslashit( $url ) . $slug . '/' . $page, 'paged' );
		}
		return $_url;
	}
}
