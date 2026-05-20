<?php
/**
 * @package Polylang Pro
 */

namespace WP_Syntex\Polylang_Pro\Editors\Screens;

use PLL_Base;
use WP_Screen;
use PLL_Language;
use PLL_CRUD_Posts;
use PLL_Admin_Links;

/**
 * Class to manage Post editor scripts.
 */
class Post extends Abstract_Screen {
	/**
	 * @var PLL_CRUD_Posts|null
	 */
	protected $posts;

	/**
	 * @var PLL_Admin_Links|null
	 */
	private $links;

	/**
	 * Constructor
	 *
	 * @since 3.7
	 *
	 * @param PLL_Base $polylang Polylang object.
	 */
	public function __construct( PLL_Base &$polylang ) {
		parent::__construct( $polylang );

		$this->posts = &$polylang->posts;
		$this->links = &$polylang->links;
	}

	/**
	 * Adds required hooks.
	 *
	 * @since 3.8
	 *
	 * @return static
	 */
	public function init() {
		parent::init();

		add_filter( 'pll_block_editor_plugin_settings', array( $this, 'add_new_post_translation_link' ) );

		return $this;
	}

	/**
	 * Adds the nonce field for the new post translation
	 * in the block editor plugin settings.
	 *
	 * @since 3.8
	 *
	 * @param array $settings The settings array.
	 * @return array The settings array.
	 */
	public function add_new_post_translation_link( $settings ) {
		global $post;

		$screen = get_current_screen();
		if ( empty( $this->links ) || empty( $screen ) || ! $this->screen_matches( $screen ) || empty( $post ) ) {
			return $settings;
		}

		foreach ( $this->model->get_languages_list() as $language ) {
			$settings['new_post_translation_links'][ $language->slug ] = $this->links->get_new_post_translation_link(
				$post,
				$language,
				'keep ampersand'
			);
		}

		return $settings;
	}

	/**
	 * Tells whether the given screen is the Post edtitor or not.
	 *
	 * @since 3.7
	 *
	 * @param  WP_Screen $screen The current screen.
	 * @return bool True if Post editor screen, false otherwise.
	 */
	protected function screen_matches( WP_Screen $screen ): bool {
		return (
			'post' === $screen->base
			&& $this->model->post_types->is_translated( $screen->post_type )
			&& $screen->is_block_editor()
		);
	}

	/**
	 * Returns the language to use in the Post editor.
	 *
	 * @since 3.7
	 *
	 * @return PLL_Language|null
	 */
	protected function get_language(): ?PLL_Language {
		global $post;

		if ( ! empty( $post ) && ! empty( $this->posts ) && $this->model->post_types->is_translated( $post->post_type ) ) {
			$this->posts->set_default_language( $post->ID );
			$post_lang = $this->model->post->get_language( $post->ID );
			return ! empty( $post_lang ) ? $post_lang : null;
		}

		return null;
	}

	/**
	 * Returns the screen name for the Post editor to use across all process.
	 *
	 * @since 3.7
	 *
	 * @return string
	 */
	protected function get_screen_name(): string {
		return 'post';
	}
}
