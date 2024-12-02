<?php
/**
 * Class PLL_Translation_Content
 *
 * @package Polylang-Pro
 */

use WP_Syntex\Polylang_Pro\Modules\Import_Export\Services\Context;

/**
 * Translates content.
 *
 * @since 3.3
 */
class PLL_Translation_Content {

	/**
	 * The translations used to transform the content.
	 *
	 * @since 3.3
	 *
	 * @var Translations
	 */
	private $translations;

	/**
	 * Setter
	 *
	 * @since 3.3
	 *
	 * @param Translations $translations Used to translate the content.
	 * @return void
	 */
	public function set_translations( $translations ) {
		$this->translations = $translations;
	}

	/**
	 * Translates the original's post title.
	 *
	 * @since 3.3
	 *
	 * @param string $from_post The post_content field of the original WP_Post.
	 * @return string
	 */
	public function translate_title( $from_post ) {
		return $this->translations->translate(
			$from_post,
			Context::to_string(
				array(
					Context::FIELD => PLL_Import_Export::POST_TITLE,
				)
			)
		);
	}

	/**
	 * Uses a {@see PLL_Translation_Walker_Interface} subclass to iterate over each translatable part of the passed content, and applies a transformation callback to it. Then returns the transformed content.
	 *
	 * @since 3.3
	 *
	 * @param string $content The post_content field of the original WP_Post.
	 * @return string
	 */
	public function translate_content( $content ) {
		$walker = PLL_Translation_Walker_Factory::create_from( $content );

		return $walker->walk( array( $this->translations, 'translate_entry' ) );
	}

	/**
	 * Translates the original post's excerpt.
	 *
	 * @since 3.3
	 *
	 * @param string $post_excerpt The post_excerpt field of the original WP_Post.
	 * @return string
	 */
	public function translate_excerpt( $post_excerpt ) {
		return $this->translations->translate(
			$post_excerpt,
			Context::to_string(
				array(
					Context::FIELD => PLL_Import_Export::POST_EXCERPT,
				)
			)
		);
	}
}
