<?php
/**
 * @package Polylang-Pro
 */

/**
 * Class PLL_Sync_Navigation
 *
 * @since 3.2
 */
class PLL_Sync_Navigation {

	/**
	 * @var PLL_Model
	 */
	private $model;

	/**
	 *
	 * Reference to the PLL_Sync_Content instance.
	 *
	 * @var PLL_Sync_Content
	 */
	private $sync_content;

	/**
	 * Constructor.
	 * Setup filters.
	 *
	 * @since 3.2
	 *
	 * @param PLL_Frontend|PLL_Admin|PLL_Settings|PLL_REST_Request $polylang Polylang object.
	 */
	public function __construct( &$polylang ) {
		$this->model = &$polylang->model;
		$this->sync_content = &$polylang->sync_content;

		add_filter( 'pll_translate_blocks', array( $this, 'translate_blocks' ), 10, 2 );
		add_filter( 'pll_get_post_types', array( $this, 'add_post_type' ), 10, 2 );
	}

	/**
	 * Adds the wp_navigation post type to the list of translatable post types.
	 *
	 * @since 3.2
	 *
	 * @param string[] $post_types  List of post types.
	 * @param bool     $is_settings True when displaying the list of custom post types in Polylang settings.
	 * @return string[]
	 */
	public function add_post_type( $post_types, $is_settings = false ) {
		if ( $is_settings || ! is_array( $post_types ) ) {
			return $post_types;
		}

		$post_types['wp_navigation'] = 'wp_navigation';

		return $post_types;
	}

	/**
	 * Recursively translate navigation blocks.
	 *
	 * @since 3.2
	 * @since 3.7 `$from_language` parameter removed.
	 *
	 * @param array[] $blocks        An array of block arrays.
	 * @param string  $language      Slug language of the target post.
	 * @return array Array of translated blocks.
	 */
	public function translate_blocks( $blocks, $language ) {
		foreach ( $blocks as $k => $block ) {
			switch ( $block['blockName'] ) {
				case 'core/navigation':
					if ( array_key_exists( 'ref', $blocks[ $k ]['attrs'] ) ) {
						$blocks[ $k ]['attrs']['ref'] = $this->translate_navigation_block( $block['attrs']['ref'], $language );
					}

					if ( array_key_exists( 'overlay', $blocks[ $k ]['attrs'] ) ) {
						$blocks[ $k ]['attrs']['overlay'] = $this->translate_overlay_slug( $block['attrs']['overlay'], $language );
					}

					break;
				case 'core/navigation-link':
					if ( array_key_exists( 'id', $blocks[ $k ]['attrs'] ) && array_key_exists( 'kind', $blocks[ $k ]['attrs'] ) ) {
						$blocks[ $k ]['attrs'] = array_merge( $blocks[ $k ]['attrs'], $this->translate_navigation_link( $block['attrs']['id'], $block['attrs']['kind'], $language ) );
					}
					break;
				case 'core/navigation-submenu':
					// If there is attrs id and kind, the submenu top level item menu is a navigation link .
					if ( array_key_exists( 'id', $blocks[ $k ]['attrs'] ) && array_key_exists( 'kind', $blocks[ $k ]['attrs'] ) ) {
						$blocks[ $k ]['attrs'] = array_merge( $blocks[ $k ]['attrs'], $this->translate_navigation_link( $block['attrs']['id'], $block['attrs']['kind'], $language ) );
					}

					if ( ! empty( $block['innerBlocks'] ) ) {
						$blocks[ $k ]['innerBlocks'] = $this->translate_blocks( $block['innerBlocks'], $language );
					}
					break;
			}
		}
		return $blocks;
	}

	/**
	 * Get the navigation link id.
	 *
	 * @since 3.2
	 * @since 3.7 Visibility changed to private.
	 *
	 * @param int    $id       Navigation link id.
	 * @param string $kind     Link type (post-type or taxonomy).
	 * @param string $language Slug language of the target post.
	 * @return array An array with the untranslated id if the navigation link post type isn't translated, or an array
	 *               with the translated id, label and url.
	 */
	private function translate_navigation_link( $id, $kind, $language ) {
		if ( 'post-type' === $kind ) {
			$tr_post_id = $this->model->post->get( $id, $language );
			if ( $tr_post_id ) {
				$tr_post = get_post( $tr_post_id );

				return array(
					'id'    => $tr_post_id,
					'label' => $tr_post->post_title,
					'url'   => get_permalink( $tr_post_id ),
				);
			}
		} elseif ( 'taxonomy' === $kind ) {
			$tr_term_id = $this->model->term->get( $id, $language );
			if ( $tr_term_id ) {
				$tr_term = get_term( $tr_term_id );

				return $tr_term instanceof WP_Term ? array(
					'id'    => $tr_term_id,
					'label' => $tr_term->name,
					'url'   => get_category_link( $tr_term_id ),
				) : array();
			}
		}
		return array( 'id' => $id );
	}

	/**
	 * Get the navigation block translation id.
	 * Create the translation if it does not exist.
	 *
	 * @since 3.2
	 * @since 3.7 Visibility changed to private and `$from_language` parameter removed.
	 *
	 * @param int    $id            Navigation block id.
	 * @param string $language      Slug language of the target post.
	 * @return int   Id of the translated navigation block. 0 on failure.
	 */
	private function translate_navigation_block( $id, $language ) {
		$tr_id = $this->model->post->get( $id, $language );

		// If we don't have a translation, then we create it.
		if ( ! $tr_id ) {
			$tr_id = $this->create_navigation_block_translation( $id, $language );
		}

		// Check the content of the navigation block post to see if there is any block to translate.
		$tr_post = get_post( $tr_id );

		if ( ! $tr_post instanceof WP_Post ) {
			// Something went wrong!
			return $id;
		}

		$target_language = $this->model->get_language( $language );

		if ( ! $target_language instanceof PLL_Language ) {
			// Something went wrong!
			return $tr_id;
		}

		$tr_content = $this->sync_content->translate_content( $tr_post->post_content, $tr_post, $target_language );
		if ( $tr_content !== $tr_post->post_content ) {
			$tr_post->post_content = $tr_content;
			wp_update_post( $tr_post );
		}

		return $tr_id;
	}

	/**
	 * Creates a navigation block translation.
	 *
	 * @since 3.2
	 * @since 3.7 Visibility changed to private and `$from_language` parameter removed.
	 *
	 * @param int    $id            The source navigation block ID.
	 * @param string $lang          New translation language slug.
	 * @return int ID of the translated navigation block. 0 on failure.
	 */
	private function create_navigation_block_translation( $id, $lang ) {
		$tr_post = get_post( $id );

		if ( empty( $tr_post ) ) {
			return $id;
		}

		$tr_post->ID = 0;
		$tr_id       = wp_insert_post( wp_slash( $tr_post->to_array() ) );

		$this->model->post->set_language( $tr_id, $lang );
		$translations = $this->model->post->get_translations( $id );
		$translations[ $lang ] = $tr_id;
		$this->model->post->save_translations( $id, $translations );

		return $tr_id;
	}

	/**
	 * Translates an overlay slug for a navigation block's overlay attribute.
	 *
	 * @since 3.8
	 *
	 * @param string $overlay_slug The overlay slug.
	 * @param string $language     Slug language of the target post.
	 * @return string The translated overlay slug or original if translation doesn't exist.
	 */
	private function translate_overlay_slug( $overlay_slug, $language ) {
		if ( empty( $overlay_slug ) ) {
			return $overlay_slug;
		}

		$language = $this->model->get_language( $language );
		if ( ! $language ) {
			return $overlay_slug;
		}

		$template = PLL_FSE_Tools::query_template_post(
			$overlay_slug,
			get_stylesheet(),
			'wp_template_part',
			$language
		);

		return $template ? $template->post_name : $overlay_slug;
	}
}
