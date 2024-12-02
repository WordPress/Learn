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
	 * @var array
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
	 * @var PLL_CRUD_Posts
	 */
	protected $posts;

	/**
	 * The post object to fill with translated data.
	 *
	 * @var WP_Post
	 */
	protected $target_post;

	/**
	 * Language of the target post.
	 *
	 * @var PLL_Language
	 */
	protected $target_language;

	/**
	 * Language of the source post.
	 *
	 * @var PLL_Language
	 */
	protected $from_language;

	/**
	 * Constructor
	 *
	 * @since 1.9
	 *
	 * @param PLL_Frontend|PLL_Admin|PLL_Settings|PLL_REST_Request $polylang Polylang object.
	 */
	public function __construct( &$polylang ) {
		$this->options = &$polylang->options;
		$this->model   = &$polylang->model;
		$this->posts   = &$polylang->posts;
	}

	/**
	 * Copy the content from one post to the other
	 *
	 * @since 1.9
	 *
	 * @param WP_Post             $from_post       The post to copy from.
	 * @param WP_Post             $target_post     The post to copy to.
	 * @param PLL_Language|string $target_language The language of the post to copy to.
	 * @return WP_Post|void
	 */
	public function copy_content( $from_post, $target_post, $target_language ) {
		$from_language   = $this->model->post->get_language( $from_post->ID );
		$target_language = $this->model->get_language( $target_language );

		if ( ! $from_language || ! $target_language ) {
			return;
		}

		$target_post->post_title = $from_post->post_title;
		$target_post->post_name  = wp_unique_post_slug(
			$from_post->post_name,
			$target_post->ID,
			$target_post->post_status,
			$target_post->post_type,
			$target_post->post_parent
		);
		$target_post->post_excerpt = $this->translate_content(
			$from_post->post_excerpt,
			$target_post,
			$from_language,
			$target_language
		);
		$target_post->post_content = $this->translate_content(
			$from_post->post_content,
			$target_post,
			$from_language,
			$target_language
		);

		return $target_post;
	}

	/**
	 * Translate shortcodes and <img> attributes in a given text
	 *
	 * @since 1.9
	 * @since 3.3 Requires $target_post, $from_language and $target_language parameters.
	 * @global array $shortcode_tags
	 *
	 * @param string       $content         Text to translate.
	 * @param WP_Post      $target_post     The post object to populate with translated content.
	 * @param PLL_Language $from_language   The source language .
	 * @param PLL_Language $target_language The language to translate to.
	 * @return string Translated text
	 */
	public function translate_content( $content, $target_post, PLL_Language $from_language, PLL_Language $target_language ) {
		global $shortcode_tags;

		$this->target_post     = $target_post;
		$this->from_language   = $from_language;
		$this->target_language = $target_language;

		// Hack shortcodes.
		$backup = $shortcode_tags;
		$shortcode_tags = array(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		// Add our own shorcode actions.
		if ( $this->options['media_support'] ) {
			add_shortcode( 'gallery', array( $this, 'ids_list_shortcode' ) );
			add_shortcode( 'playlist', array( $this, 'ids_list_shortcode' ) );
			add_shortcode( 'caption', array( $this, 'caption_shortcode' ) );
			add_shortcode( 'wp_caption', array( $this, 'caption_shortcode' ) );
		}

		if ( has_blocks( $content ) ) {
			$blocks  = parse_blocks( $content );
			$blocks  = $this->translate_blocks( $blocks );
			$content = serialize_blocks( $blocks );
		} else {
			$content = do_shortcode( $content ); // Translate shortcodes.
			$content = $this->translate_html( $content );
		}

		// Get the shorcodes back.
		$shortcode_tags = $backup; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		return $content;
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
		if ( '_thumbnail_id' === $key && ! $tr_id = $this->model->post->get( $id, $lang ) ) {
			$tr_id = $this->posts->create_media_translation( $id, $lang );
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
	 * Get the media translation id
	 * Create the translation if it does not exist
	 * Attach the media to the parent post
	 *
	 * @since 1.9
	 *
	 * @param int $id Media ID.
	 * @return int Translated media ID.
	 */
	protected function translate_media( $id ) {
		global $wpdb;

		if ( ! $tr_id = $this->model->post->get( $id, $this->target_language ) ) {
			$tr_id = $this->posts->create_media_translation( $id, $this->target_language );
		}

		// If we don't have a translation and did not success to create one, return current media
		if ( empty( $tr_id ) ) {
			return $id;
		}

		// Attach to the translated post
		if ( ! wp_get_post_parent_id( $tr_id ) && 0 < $this->target_post->ID ) {
			// Query inspired by wp_media_attach_action()
			$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET post_parent = %d WHERE post_type = 'attachment' AND ID = %d", $this->target_post->ID, $tr_id ) );
			clean_attachment_cache( $tr_id );
		}

		return $tr_id;
	}

	/**
	 * Translates the 'gallery' and 'playlist' shortcodes
	 *
	 * @since 1.9
	 *
	 * @param array  $attr Shortcode attributes.
	 * @param null   $null Shortcode content, not used.
	 * @param string $tag  Shortcode tag (either 'gallery' or 'playlist').
	 * @return string Translated shortcode.
	 */
	public function ids_list_shortcode( $attr, $null, $tag ) {
		$out = array();

		foreach ( $attr as $k => $v ) {
			if ( 'ids' === $k ) {
				$ids    = explode( ',', $v );
				$tr_ids = array();
				foreach ( $ids as $id ) {
					$tr_ids[] = $this->translate_media( (int) $id );
				}
				$v = implode( ',', $tr_ids );
			}
			$out[] = $k . '="' . $v . '"';
		}

		return '[' . $tag . ' ' . implode( ' ', $out ) . ']';
	}

	/**
	 * Translates the caption shortcode
	 * Compatible only with the new style introduced in WP 3.4
	 *
	 * @since 1.9
	 *
	 * @param array  $attr    Shortcode attrbute.
	 * @param string $content Shortcode content.
	 * @param string $tag     Shortcode tag (either 'caption' or 'wp-caption').
	 * @return string Translated shortcode.
	 */
	public function caption_shortcode( $attr, $content, $tag ) {
		// Translate the caption id
		$out = array();

		foreach ( $attr as $k => $v ) {
			if ( 'id' === $k ) {
				$idarr = explode( '_', $v );
				$id    = $idarr[1]; // Remember this
				$tr_id = $idarr[1] = $this->translate_media( (int) $id );
				$v     = implode( '_', $idarr );
			}
			$out[] = $k . '="' . $v . '"';
		}

		// Translate the caption content
		if ( ! empty( $id ) && ! empty( $tr_id ) ) {
			$p    = get_post( (int) $id );
			$tr_p = get_post( $tr_id );
			if ( $p && $tr_p ) {
				$content = str_replace( $p->post_excerpt, $tr_p->post_excerpt, $content );
			}
		}

		return '[' . $tag . ' ' . implode( ' ', $out ) . ']' . $content . '[/' . $tag . ']';
	}

	/**
	 * Translate images and caption in inner html
	 *
	 * Since 2.5
	 *
	 * @param string $content HTML string.
	 * @return string
	 */
	protected function translate_html( $content ) {
		if ( $this->options['media_support'] ) {
			$textarr = wp_html_split( $content ); // Since 4.2.3

			$img_ids = array();
			foreach ( $textarr as $i => $text ) {
				// Translate img class and alternative text
				if ( 0 === strpos( $text, '<img' ) ) {
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
	protected function translate_img( &$text ) {
		$attributes = wp_kses_attr_parse( $text ); // since WP 4.2.3

		if ( ! is_array( $attributes ) ) {
			return null;
		}

		// Replace class
		foreach ( $attributes as $k => $attr ) {
			if ( 0 === strpos( $attr, 'class' ) && preg_match( '#wp\-image\-([0-9]+)#', $attr, $matches ) && ! empty( $matches[1] ) ) {
				$tr_id            = $this->translate_media( (int) $matches[1] );
				$attributes[ $k ] = str_replace( 'wp-image-' . $matches[1], 'wp-image-' . $tr_id, $attr );

			}

			if ( preg_match( '#^data\-id="([0-9]+)#', $attr, $matches ) && ! empty( $matches[1] ) ) {
				$tr_id            = $this->translate_media( (int) $matches[1] );
				$attributes[ $k ] = str_replace( 'data-id="' . $matches[1], 'data-id="' . $tr_id, $attr );
			}

			if ( 0 === strpos( $attr, 'data-link' ) && preg_match( '#attachment_id=([0-9]+)#', $attr, $matches ) && ! empty( $matches[1] ) ) {
				$tr_id            = $this->translate_media( (int) $matches[1] );
				$attributes[ $k ] = str_replace( 'attachment_id=' . $matches[1], 'attachment_id=' . $tr_id, $attr );
			}
		}

		if ( ! empty( $tr_id ) ) {
			// Got a tr_id, attempt to replace the alt text
			$alt = get_post_meta( $tr_id, '_wp_attachment_image_alt', true );
			if ( is_string( $alt ) && ! empty( $alt ) ) {
				foreach ( $attributes as $k => $attr ) {
					if ( 0 === strpos( $attr, 'alt' ) ) {
						$attributes[ $k ] = 'alt="' . esc_attr( $alt ) . '" ';
					}
				}
			}
		}

		$text = implode( $attributes );

		return empty( $tr_id ) ? null : $tr_id;
	}

	/**
	 * Recursively translate blocks.
	 *
	 * @param array[] $blocks An array of arrays representing a block.
	 * @return array
	 */
	protected function translate_blocks( $blocks ) {
		foreach ( $blocks as $k => $block ) {
			switch ( $block['blockName'] ) {
				case 'core/block':
					if ( ! $this->model->is_translated_post_type( 'wp_block' ) || ! isset( $block['attrs']['ref'] ) ) {
						break;
					}

					$tr_id = $this->model->post->get( $block['attrs']['ref'], $this->target_language );

					if ( ! empty( $tr_id ) ) {
						$blocks[ $k ]['attrs']['ref'] = $tr_id;
					}
					break;

				case 'core/latest-posts':
					if ( isset( $block['attrs']['categories'] ) ) {
						$tr_ids = array();
						foreach ( $block['attrs']['categories'] as $term ) {
							$tr_ids[] = $this->model->term->get( $term['id'], $this->target_language );
						}

						// Let's remove unfound translation results.
						$tr_ids = array_filter( $tr_ids );

						// If there is no translation, then the category is unset.
						if ( empty( $tr_ids ) ) {
							unset( $blocks[ $k ]['attrs']['categories'] );
							break;
						}

						// Query all the translated terms outside the loop to avoid multiple SQL queries with get_term() call.
						$terms = get_terms( array( 'include' => $tr_ids, 'hide_empty' => false, 'fields' => 'id=>name' ) );

						if ( ! is_array( $terms ) ) {
							unset( $blocks[ $k ]['attrs']['categories'] );
							break;
						}

						$tr_data = array();
						foreach ( $terms as $id => $term_name ) {
							$tr_data[] = array(
								'id'    => $id,
								'value' => $term_name,
							);
						}
						if ( $tr_data ) {
							$blocks[ $k ]['attrs']['categories'] = $tr_data;
						} else {
							unset( $blocks[ $k ]['attrs']['categories'] );
						}
					} else {
						unset( $blocks[ $k ]['attrs']['categories'] );
					}
					break;
			}

			if ( $this->options['media_support'] ) {
				$blocks[ $k ] = $this->translate_media_block( $blocks[ $k ] );
			}

			if ( ! empty( $block['innerBlocks'] ) ) {
				$blocks[ $k ]['innerBlocks'] = $this->translate_blocks( $block['innerBlocks'] );
			}
		}

		/**
		 * Filters parsed blocks after core blocks have been translated.
		 *
		 * @since 2.5.3
		 *
		 * @param array[] $blocks    List of blocks.
		 * @param string  $lang      Language of target.
		 * @param string  $from_lang Language of the source.
		 */
		return apply_filters( 'pll_translate_blocks', $blocks, $this->target_language->slug, $this->from_language->slug );
	}

	/**
	 * Translates media ids in blocks.
	 *
	 * @since 3.3
	 *
	 * @param array $block A representative array of a block.
	 * @return array The translated block.
	 */
	protected function translate_media_block( $block ) {
		switch ( $block['blockName'] ) {
			case 'core/audio':
			case 'core/video':
				if ( array_key_exists( 'id', $block['attrs'] ) ) {
					$block['attrs']['id'] = $this->translate_media( $block['attrs']['id'] );
				}
				break;
			case 'core/cover':
			case 'core/image':
				if ( array_key_exists( 'id', $block['attrs'] ) ) {
					$block['attrs']['id'] = $this->translate_media( $block['attrs']['id'] );
				}
				$block = $this->translate_block_content( $block );
				break;

			case 'core/file':
				$source_id = $block['attrs']['id'];
				$tr_id = $this->translate_media( $source_id );
				$block['attrs']['id'] = $tr_id;
				$textarr = wp_html_split( $block['innerHTML'] );
				$source_post = get_post( $source_id );
				if ( ! $source_post instanceof WP_Post ) {
					break;
				}
				$replace_file_link_text = 0 === strpos( $textarr[3], '<a' ) && $textarr[4] === $source_post->post_title;
				if ( $replace_file_link_text ) {
					$tr_post = get_post( $tr_id );
					if ( $tr_post ) {
						$textarr[4] = $tr_post->post_title;
						$block['innerContent'][0] = implode( $textarr );
						$block['innerHTML'] = implode( $textarr );
					}
				}
				break;

			case 'core/gallery':
				if ( isset( $block['attrs']['ids'] ) && is_array( $block['attrs']['ids'] ) ) {
					// Backward compatibility with WP < 5.9.
					foreach ( $block['attrs']['ids'] as $n => $id ) {
						$block['attrs']['ids'][ $n ] = $this->translate_media( $id );
					}
				}
				$block = $this->translate_block_content( $block );
				break;

			case 'core/media-text':
				$block['attrs']['mediaId'] = $this->translate_media( $block['attrs']['mediaId'] );
				$block['innerContent'][0] = $this->translate_html( $block['innerContent'][0] );
				break;

			case 'core/shortcode':
				$block['innerContent'][0] = do_shortcode( $block['innerContent'][0] );
				$block['innerHTML'] = do_shortcode( $block['innerHTML'] );
				break;

			default:
				if ( ! empty( $block['innerHTML'] ) ) {
					$block = $this->translate_block_content( $block );
				}
				break;
		}

		return $block;
	}

	/**
	 * Updates the block properties with a translation if it is found.
	 *
	 * @since 2.9
	 *
	 * @param array $block An array mimicking the structure of {@see https://github.com/WordPress/WordPress/blob/5.5.1/wp-includes/class-wp-block-parser.php WP_Block_Parser_Block}.
	 * @return array The updated array formatted block.
	 */
	protected function translate_block_content( $block ) {
		$inner_content_nb = count( $block['innerContent'] );
		for ( $i = 0; $i < $inner_content_nb; $i++ ) {
			if ( ! empty( $block['innerContent'][ $i ] ) ) {
				$html = do_shortcode( $block['innerContent'][ $i ] ); // Translate shortcodes.
				$html = $this->translate_html( $html ); // Translate inline images.

				$block['innerContent'][ $i ] = $html;
			}
		}
		$html = do_shortcode( $block['innerHTML'] ); // Translate shortcodes.
		$block['innerHTML'] = $this->translate_html( $html );

		return $block;
	}
}
