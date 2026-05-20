<?php
/**
 * @package Polylang-Pro
 */

/**
 * Translates HTML to keep it synchronized across pieces of content.
 *
 * @since 3.7
 */
class PLL_Sync_HTML {
	/**
	 * Translates object IDs.
	 *
	 * @var PLL_Sync_Ids
	 */
	public $ids;

	/**
	 * Stores the plugin options.
	 *
	 * @var \WP_Syntex\Polylang\Options\Options
	 */
	private $options;

	/**
	 * Language of the target post.
	 *
	 * @var PLL_Language
	 */
	private $target_language;

	/**
	 * Main target post object.
	 *
	 * @var WP_Post|null
	 */
	private $target_post;

	/**
	 * Constructor.
	 *
	 * @since 3.7
	 *
	 * @param PLL_Sync_Ids $ids Ids translator.
	 */
	public function __construct( PLL_Sync_Ids $ids ) {
		$this->ids     = $ids;
		$this->options = $ids->model->options;
	}

	/**
	 * Translates images and caption in HTML.
	 *
	 * @since 3.7
	 *
	 * @param string       $content         HTML string.
	 * @param PLL_Language $target_language Target language.
	 * @param WP_Post|null $target_post     Main target post object.
	 * @return string Translated HTML.
	 */
	public function translate( string $content, PLL_Language $target_language, ?WP_Post $target_post ): string {
		if ( ! $this->options['media_support'] ) {
			return $content;
		}

		$this->target_language = $target_language;
		$this->target_post     = $target_post;

		$textarr = wp_html_split( $content ); // Since 4.2.3

		$img_ids = array();
		foreach ( $textarr as $i => $text ) {
			// Translate img class and alternative text
			if (
				0 === strpos( $text, '<img' )
				|| strpos( $text, 'role="img"' ) !== false
				|| strpos( $text, 'wp-block-cover__image-background' ) !== false
			) {
				$img_ids[] = $this->translate_img( $textarr[ $i ] );
			}
		}

		if ( empty( $img_ids ) ) {
			return $content;
		}

		$new_content = implode( $textarr );
		$key = 0;
		$new_content = preg_replace_callback(
			'@(?<before><figcaption.*?>)(.+?)(?<after></figcaption>)@',
			function ( $matches ) use ( $img_ids, &$key ) {
				$tr_post = get_post( $img_ids[ $key ] );
				$key++;
				if ( ! empty( $tr_post->post_excerpt ) ) {
					return $matches['before'] . $tr_post->post_excerpt . $matches['after'];
				} else {
					return $matches[0];
				}
			},
			$new_content
		);

		if ( is_string( $new_content ) ) {
			return $new_content;
		}

		return $content;
	}

	/**
	 * Translates <img> 'class' and 'alt' attributes.
	 *
	 * @since 1.9
	 * @since 2.5 The html is passed by reference and the return value is the image ID.
	 *
	 * @param string $text Reference to <img> html with attributes.
	 * @return null|int Translated image id if exist.
	 */
	private function translate_img( &$text ) {
		$attributes = wp_kses_attr_parse( $text ); // since WP 4.2.3

		if ( ! is_array( $attributes ) ) {
			return null;
		}

		// Replace class
		foreach ( $attributes as $k => $attr ) {
			if ( 0 === strpos( $attr, 'class' ) && preg_match( '#wp\-image\-([0-9]+)#', $attr, $matches ) && ! empty( $matches[1] ) ) {
				$tr_id            = $this->ids->translate( (int) $matches[1], 'attachment', $this->target_language, $this->target_post );
				$attributes[ $k ] = str_replace( 'wp-image-' . $matches[1], 'wp-image-' . $tr_id, $attr );

			}

			if ( preg_match( '#^data\-id="([0-9]+)#', $attr, $matches ) && ! empty( $matches[1] ) ) {
				$tr_id            = $this->ids->translate( (int) $matches[1], 'attachment', $this->target_language, $this->target_post );
				$attributes[ $k ] = str_replace( 'data-id="' . $matches[1], 'data-id="' . $tr_id, $attr );
			}

			if ( 0 === strpos( $attr, 'data-link' ) && preg_match( '#attachment_id=([0-9]+)#', $attr, $matches ) && ! empty( $matches[1] ) ) {
				$tr_id            = $this->ids->translate( (int) $matches[1], 'attachment', $this->target_language, $this->target_post );
				$attributes[ $k ] = str_replace( 'attachment_id=' . $matches[1], 'attachment_id=' . $tr_id, $attr );
			}
		}

		if ( ! empty( $tr_id ) ) {
			// Got a tr_id, attempt to replace the alt text
			$alt = get_post_meta( (int) $tr_id, '_wp_attachment_image_alt', true );
			if ( is_string( $alt ) && ! empty( $alt ) ) {
				foreach ( $attributes as $k => $attr ) {
					if ( 0 === strpos( $attr, 'alt' ) ) {
						$attributes[ $k ] = 'alt="' . esc_attr( $alt ) . '" ';
					}
					if ( 0 === strpos( $attr, 'aria-label' ) ) {
						$attributes[ $k ] = 'aria-label="' . esc_attr( $alt ) . '" ';
					}
				}
			}
		}

		$text = implode( $attributes );

		return empty( $tr_id ) ? null : $tr_id;
	}
}
