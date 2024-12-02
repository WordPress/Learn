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
	public $model;

	/**
	 *
	 * Reference to the PLL_Sync_Content instance.
	 *
	 * @var PLL_Sync_Content
	 */
	public $sync_content;

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

		add_filter( 'pll_translate_blocks', array( $this, 'translate_blocks' ), 10, 3 );
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
	 *
	 * @param array[] $blocks        An array of block arrays.
	 * @param string  $language      Slug language of the target post.
	 * @param string  $from_language Slug language of the source post.
	 * @return array Array of translated blocks.
	 */
	public function translate_blocks( $blocks, $language, $from_language ) {
		foreach ( $blocks as $k => $block ) {
			switch ( $block['blockName'] ) {
				case 'core/navigation':
					if ( array_key_exists( 'ref', $blocks[ $k ]['attrs'] ) ) {
						$blocks[ $k ]['attrs']['ref'] = $this->translate_navigation_block( $block['attrs']['ref'], $language, $from_language );
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
						$blocks[ $k ]['innerBlocks'] = $this->translate_blocks( $block['innerBlocks'], $language, $from_language );
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
	 *
	 * @param int    $id       Navigation link id.
	 * @param string $kind     Link type (post-type or taxonomy).
	 * @param string $language Slug language of the target post.
	 * @return array An array with the untranslated id if the navigation link post type isn't translated, or an array
	 *               with the translated id, label and url.
	 */
	public function translate_navigation_link( $id, $kind, $language ) {
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
	 *
	 * @param int    $id            Navigation block id.
	 * @param string $language      Slug language of the target post.
	 * @param string $from_language Slug language of the source post.
	 * @return false|int|WP_Error   Id of the translated navigation block.
	 */
	public function translate_navigation_block( $id, $language, $from_language ) {
		$tr_id = $this->model->post->get( $id, $language );

		// If we don't have a translation, then we create it.
		if ( ! $tr_id ) {
			$tr_id = $this->create_navigation_block_translation( $id, $language, $from_language );
		}

		// Check the content of the navigation block post to see if there is any block to translate.
		$tr_post = get_post( $tr_id );

		if ( ! $tr_post instanceof WP_Post ) {
			// Something went wrong!
			return $id;
		}

		$from_language   = $this->model->get_language( $from_language );
		$target_language = $this->model->get_language( $language );

		if ( ! $from_language instanceof PLL_Language || ! $target_language instanceof PLL_Language ) {
			// Something went wrong!
			return $tr_id;
		}

		$tr_content = $this->sync_content->translate_content( $tr_post->post_content, $tr_post, $from_language, $target_language );
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
	 *
	 * @param int    $id            The source navigation block ID.
	 * @param string $lang          New translation language slug.
	 * @param string $from_language Slug language of the source post.
	 * @return int|WP_Error ID of the translated navigation block.
	 */
	public function create_navigation_block_translation( $id, $lang, $from_language ) {
		$tr_post = get_post( $id );

		if ( empty( $tr_post ) ) {
			return $id;
		}

		$tr_post->ID = 0;
		$tr_id       = wp_insert_post( wp_slash( $tr_post->to_array() ) );

		$this->model->post->set_language( $id, $from_language );
		$this->model->post->set_language( $tr_id, $lang );

		$translations = $this->model->post->get_translations( $id );
		$translations[ $lang ] = $tr_id;
		$this->model->post->save_translations( $id, $translations );

		return $tr_id;
	}
}
