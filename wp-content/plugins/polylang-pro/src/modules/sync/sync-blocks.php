<?php
/**
 * @package Polylang-Pro
 */

/**
 * Translates blocks to keep them synchronized across pieces of content.
 *
 * @since 3.7
 */
class PLL_Sync_Blocks {
	/**
	 * @var PLL_Model
	 */
	private $model;

	/**
	 * Stores the plugin options.
	 *
	 * @var \WP_Syntex\Polylang\Options\Options
	 */
	private $options;

	/**
	 * Translates object IDs.
	 *
	 * @var PLL_Sync_Ids
	 */
	private $ids;

	/**
	 * Language of the target post.
	 *
	 * @var PLL_Language
	 */
	private $target_language;

	/**
	 * Shortcodes translator.
	 *
	 * @var PLL_Sync_Shortcodes
	 */
	private $shortcodes;

	/**
	 * HTML translator.
	 *
	 * @var PLL_Sync_HTML
	 */
	private $html;

	/**
	 * Parsing rules for blocks.
	 *
	 * @var PLL_Sync_Block_Parsing_Rules
	 */
	private $parsing_rules;

	/**
	 * Main target post object.
	 *
	 * @var WP_Post|null
	 */
	private $target_post;

	/**
	 * Source post object.
	 *
	 * @var WP_Post|null
	 */
	private $source_post;

	/**
	 * Constructor.
	 *
	 * @since 3.7
	 *
	 * @param PLL_Sync_Shortcodes $shortcodes  Shortcodes translator.
	 * @param PLL_Sync_HTML       $html        HTML translator.
	 * @param WP_Post|null        $source_post Source post object, `null` if context agnostic.
	 */
	public function __construct( PLL_Sync_Shortcodes $shortcodes, PLL_Sync_HTML $html, ?WP_Post $source_post = null ) {
		$this->model           = $shortcodes->ids->model;
		$this->options         = $shortcodes->ids->model->options;
		$this->ids             = $shortcodes->ids;
		$this->shortcodes      = $shortcodes;
		$this->html            = $html;
		$this->parsing_rules   = new PLL_Sync_Block_Parsing_Rules();
		$this->source_post     = $source_post;
	}

	/**
	 * Recursively translates blocks.
	 *
	 * @since 3.7
	 *
	 * @param string       $content         Content, ideally containing blocks.
	 * @param PLL_Language $target_language Target language.
	 * @param ?WP_Post     $target_post     Main target post object, default to null.
	 * @return string Content with translated blocks.
	 */
	public function translate( string $content, PLL_Language $target_language, ?WP_Post $target_post = null ): string {
		$this->target_language = $target_language;
		$this->target_post     = $target_post;

		return serialize_blocks(
			$this->walk_blocks(
				parse_blocks(
					$content
				)
			)
		);
	}

	/**
	 * Walks through a list of blocks and apply translation on each of them if applicable.
	 *
	 * @since 3.7
	 *
	 * @param array $blocks List of blocks to synchronize.
	 * @return array Synchronized list of blocks.
	 */
	private function walk_blocks( array $blocks ): array {
		foreach ( $blocks as $k => $block ) {
			if ( 'core/latest-posts' === $block['blockName'] ) {
				/*
				 * `pll_blocks_attributes_to_synchronize` cannot be used to translate 'core/latest-posts' categories
				 * because it's stored as an array of data such as {'id': int, 'value':string}.
				 */
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
						continue;
					}

					// Query all the translated terms outside the loop to avoid multiple SQL queries with get_term() call.
					$terms = get_terms( array( 'include' => $tr_ids, 'hide_empty' => false, 'fields' => 'id=>name' ) );

					if ( ! is_array( $terms ) ) {
						unset( $blocks[ $k ]['attrs']['categories'] );
						continue;
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
				continue;
			}

			$blocks[ $k ] = $this->parse_html(
				$this->parse_attributes( $blocks[ $k ] )
			);

			if ( $this->options['media_support'] ) {
				$blocks[ $k ] = $this->translate_media_block( $blocks[ $k ] );
			}

			if ( ! empty( $block['innerBlocks'] ) ) {
				$blocks[ $k ]['innerBlocks'] = $this->walk_blocks( $block['innerBlocks'] );
			}
		}

		/**
		 * Filters parsed blocks after core blocks have been translated.
		 *
		 * @since 2.5.3
		 * @since 3.7 `$from_lang` parameter deprecated.
		 *
		 * @param array[] $blocks     List of blocks.
		 * @param string  $lang       Language slug of target.
		 * @param string  $deprecated Deprecated. Empty string.
		 */
		$blocks = (array) apply_filters( 'pll_translate_blocks', $blocks, $this->target_language->slug, '' );

		/**
		 * Filters parsed blocks after core blocks have been translated with source post context.
		 *
		 * @since 3.7
		 *
		 * @param array[]      $blocks          List of blocks.
		 * @param PLL_Language $target_language Target language.
		 * @param WP_Post|null $source_post     Source post object, `null` if context agnostic.
		 */
		return (array) apply_filters( 'pll_translate_blocks_with_context', $blocks, $this->target_language, $this->source_post );
	}

	/**
	 * Translates media inner content.
	 * Attributes are now translated thanks to `pll_blocks_attributes_to_synchronize` filter.
	 *
	 * @since 3.3
	 *
	 * @param array $block A representative array of a block.
	 * @return array The translated block.
	 */
	private function translate_media_block( $block ) {
		switch ( $block['blockName'] ) {
			case 'core/cover':
			case 'core/image':
			case 'core/gallery':
				$block = $this->translate_inner_content( $block );

				if ( empty( $block['attrs']['id'] ) || empty( $block['attrs']['alt'] ) ) {
					break;
				}

				$alt = get_post_meta(
					$this->ids->translate(
						(int) $block['attrs']['id'],
						'attachment',
						$this->target_language,
						$this->target_post
					),
					'_wp_attachment_image_alt',
					true
				);

				if ( ! empty( $alt ) ) {
					$block['attrs']['alt'] = $alt;
				}
				break;

			case 'core/file':
				$source_id            = $block['attrs']['id'];
				$tr_id                = $this->ids->translate( $source_id, 'attachment', $this->target_language, $this->target_post );
				$block['attrs']['id'] = $tr_id;
				$textarr              = wp_html_split( $block['innerHTML'] );
				$source_post          = get_post( $source_id );
				if ( ! $source_post instanceof WP_Post ) {
					break;
				}
				$replace_file_link_text = 0 === strpos( $textarr[3], '<a' ) && $textarr[4] === $source_post->post_title;
				if ( $replace_file_link_text ) {
					$tr_post = get_post( (int) $tr_id );
					if ( $tr_post ) {
						$textarr[4] = $tr_post->post_title;
						$block['innerContent'][0] = implode( $textarr );
						$block['innerHTML'] = implode( $textarr );
					}
				}
				break;

			case 'core/media-text':
				if ( isset( $block['attrs']['mediaLink'] ) ) {
					$block['attrs']['mediaLink'] = preg_replace(
						'#attachment_id=([0-9]+)#',
						'attachment_id=' . $block['attrs']['mediaId'],
						$block['attrs']['mediaLink']
					);
				}
				foreach ( $block['innerContent'] as $key => $content ) {
					if ( ! empty( $content ) ) {
						$block['innerContent'][ $key ] = $this->html->translate( $content, $this->target_language, $this->target_post );
					}
				}
				break;

			case 'core/shortcode':
				$block['innerContent'][0] = $this->shortcodes->translate( $block['innerContent'][0], $this->target_language, $this->target_post );
				$block['innerHTML']       = $this->shortcodes->translate( $block['innerHTML'], $this->target_language, $this->target_post );
				break;

			default:
				if ( ! empty( $block['innerHTML'] ) ) {
					$block = $this->translate_inner_content( $block );
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
	private function translate_inner_content( $block ) {
		$inner_content_nb = count( $block['innerContent'] );
		for ( $i = 0; $i < $inner_content_nb; $i++ ) {
			if ( ! empty( $block['innerContent'][ $i ] ) ) {
				$block['innerContent'][ $i ] = $this->html->translate(
					$this->shortcodes->translate( $block['innerContent'][ $i ], $this->target_language, $this->target_post ),
					$this->target_language,
					$this->target_post
				);
			}
		}
		$block['innerHTML'] = $this->html->translate( // @FIXME Is imploding innerContent sufficient?
			$this->shortcodes->translate( $block['innerHTML'], $this->target_language, $this->target_post ),
			$this->target_language,
			$this->target_post
		);

		return $block;
	}

	/**
	 * Translates object IDs in attributes using predefined rules.
	 *
	 * @since 3.7
	 *
	 * @param array $block An array of block data.
	 * @return array  An array of translated block data.
	 */
	private function parse_attributes( array $block ): array {
		if ( empty( $block['attrs'] ) ) {
			return $block;
		}

		$no_media       = ! $this->options['media_support'];
		$no_patterns    = ! $this->model->post_types->is_translated( 'wp_block' );
		$attrs_by_types = $this->parsing_rules->attributes( $block );
		$attrs_by_types = array_filter(
			$this->parsing_rules->attributes( $block ),
			function ( $type ) use ( $no_media, $no_patterns ) {
				if ( $no_media && 'attachment' === $type ) {
					return false;
				}
				if ( $no_patterns && 'wp_block' === $type ) {
					return false;
				}

				return true;
			},
			ARRAY_FILTER_USE_KEY
		);

		foreach ( $attrs_by_types as $type => $attrs ) {
			$block['attrs'] = $this->translate_attributes_recursively(
				$block['attrs'],
				$type,
				$attrs
			);
		}

		return $block;
	}

	/**
	 * Walks though an array of attributes recursilevely
	 * and translates object IDs.
	 *
	 * @since 3.7
	 *
	 * @param array|scalar    $attrs                   An array of attributes to parse, or an attribute value.
	 * @param string          $type                    Translation type. Either `post`, `term`, `attachment` or `wp_block`.
	 * @param string[]|string $attributes_to_translate Optional. An array of attributes to synchronize, object type otherwise (`post` or `term`).
	 * @return array|scalar  An array of parsed attributes, or a translated attribute value.
	 *
	 * @phpstan-param array<string, array|string>|string $attributes_to_translate
	 * @phpstan-return (
	 *     $attrs is array ? array : scalar
	 * )
	 */
	private function translate_attributes_recursively( $attrs, $type, $attributes_to_translate ) {
		if ( empty( $attributes_to_translate ) ) {
			return $attrs;
		}

		if ( is_array( $attributes_to_translate ) ) {
			// We have sub-keys to match.
			if ( ! is_array( $attrs ) ) {
				// No more attributes.
				return $attrs;
			}

			$matcher = new PLL_Format_Util();

			foreach ( $attributes_to_translate as $attribute_name => $attribute_sub_fields ) {
				// Find the attributes matching `$attribute_name` (may contain wildcards).
				$entries = $matcher->filter_list( $attrs, (string) $attribute_name );

				foreach ( $entries as $key => $values ) {
					// Parse sub-attributes.
					$attrs[ $key ] = $this->translate_attributes_recursively( $attrs[ $key ], $type, $attribute_sub_fields );
				}
			}

			return $attrs;
		}

		$ids       = $attrs;
		$no_list   = is_array( $attrs ) || ( strpos( (string) $attrs, ',' ) === false );
		$separator = '';
		if ( is_string( $ids ) && ! $no_list ) {
			preg_match( '/(?<separator>\s|,)/', $ids, $matches );
			$separator = $matches['separator'] ?? '';
			$ids       = wp_parse_id_list( $ids );
		}

		if ( is_array( $ids ) ) {
			foreach ( $ids as $k => $id ) {
				$ids[ $k ] = $this->ids->translate( $id, $type, $this->target_language, $this->target_post );
			}
		} else {
			$ids = $this->ids->translate( $ids, $type, $this->target_language, $this->target_post );
		}

		return $no_list ? $ids : implode( $separator, (array) $ids );
	}

	/**
	 * Translates object IDs in a block HTML content.
	 *
	 * @since 3.7
	 *
	 * @param array $block An array of block data.
	 * @return array  An array of translated block data.
	 */
	private function parse_html( array $block ): array {
		if ( empty( $block['innerContent'] ) ) {
			return $block;
		}

		$xpath_rules = $this->parsing_rules->html( $block );
		foreach ( $xpath_rules as $object_type => $paths ) {
			$updated_strings = array();
			foreach ( $paths as $path => $ids ) {
				preg_match( '/(?<separator>\s|,)/', $ids, $matches );
				$ids       = wp_parse_id_list( $ids );
				$separator = $matches['separator'] ?? '';
				if ( empty( $ids ) ) {
					continue;
				}

				$tr_ids = array();
				foreach ( $ids as $id ) {
					$tr_ids[] = (string) $this->ids->translate( $id, $object_type, $this->target_language, $this->target_post );
				}
				$updated_strings[ $path ] = implode( $separator, $tr_ids );
			}

			if ( empty( $updated_strings ) ) {
				continue;
			}

			$translated_content    = ( new PLL_DOM_Content(
				implode( PLL_Translation_Walker_Blocks::BLOCK_PLACEHOLDER, $block['innerContent'] )
			) )->replace_content( $updated_strings );
			$block['innerContent'] = explode( PLL_Translation_Walker_Blocks::BLOCK_PLACEHOLDER, $translated_content );
		}

		$block['innerHTML'] = implode( '', $block['innerContent'] );

		return $block;
	}
}
