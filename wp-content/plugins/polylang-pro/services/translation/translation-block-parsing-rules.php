<?php
/**
 * @package Polylang-Pro
 */

/**
 * Holds the rules defining which part of a block should be "translated".
 * Translated may mean different actions, like exporting it into a translation file, or updating the database...
 *
 * @since 3.3
 *
 * @phpstan-type XpathParsingRules array<string, array<string>>
 * @phpstan-type AttributesParsingRules array<non-falsy-string, array<non-empty-string, array|true>>
 */
class PLL_Translation_Block_Parsing_Rules {

	/**
	 * Caches values about parsable and non-parsable blocks.
	 *
	 * @var array
	 *
	 * @phpstan-var array{
	 *     parsing_rules?: XpathParsingRules,
	 *     parsing_rules_for_attributes?: AttributesParsingRules,
	 *     blocks_not_to_parse?: string[]
	 * }
	 */
	private $cache = array();

	/**
	 * Holds some rules for block attributes to translate.
	 * Keep this list alphabetically sorted when adding entries.
	 *
	 * @var array
	 * @phpstan-var AttributesParsingRules
	 */
	private $parsing_rules_attributes = array(
		'core/more'                 => array(
			'customText' => true,
		),
		'core/navigation-link'      => array(
			'description' => true,
		),
		'core/post-navigation-link' => array(
			'label' => true,
		),
		'core/read-more'            => array(
			'content' => true,
		),
		'core/search'               => array(
			'label'       => true,
			'placeholder' => true,
			'buttonText'  => true,
		),
	);

	/**
	 * Holds some rules as Xpath expressions to evaluate in the blocks content.
	 * Keep this list alphabetically sorted when adding entries.
	 *
	 * @var string[][]
	 * @phpstan-var XpathParsingRules
	 */
	private $parsing_rules = array(
		'core/audio'        => array(
			'//figure/figcaption',
		),
		'core/button'       => array(
			'//a',
			'//a/@href',
		),
		'core/cover'        => array(
			'//div/p',
		),
		'core/cover-image'  => array(
			'//div/p',
		),
		'core/embed'        => array(
			'//figure/figcaption',
		),
		'core/file'         => array(
			'//div/a',
		),
		'core/gallery'      => array(
			'//figure/figcaption',
			'//figure/img/@alt', // Backward compatibility.
		),
		'core/heading'      => array(
			'//*[self::h1 or self::h2 or self::h3 or self::h4 or self::h5 or self::h6]',
		),
		'core/image'        => array(
			'//figure/figcaption',
			'//figure/img/@alt|//figure/a/img/@alt',
			'//figure/img/@title|//figure/a/img/@title',
			'//figure/a/@href',
		),
		'core/list'         => array(
			'//ul/li|//ol/li',
		),
		'core/media-text'   => array(
			'//figure/img/@alt',
		),
		'core/paragraph'    => array(
			'//p',
		),
		'core/preformatted' => array(
			'//pre',
		),
		'core/pullquote'    => array(
			'//blockquote/p',
			'//blockquote/cite',
		),
		'core/quote'        => array(
			'//blockquote/p',
			'//blockquote/cite',
		),
		'core/subhead'      => array(
			'//p',
		),
		'core/table'        => array(
			'//th',
			'//td',
			'//figure/figcaption',
		),
		'core/text-columns' => array(
			'//div[@class="wp-block-column"]',
		),
		'core/verse'        => array(
			'//pre',
		),
		'core/video'        => array(
			'//figure/figcaption',
		),
	);

	/**
	 * List of known blocks that don't need to be parsed, because they don't contain contents to be translated.
	 * Though, they may contain blocks that need to be parsed.
	 * Keep this list alphabetically sorted when adding entries.
	 *
	 * @var string[]
	 */
	private $blocks_not_to_parse = array(
		'core/buttons',
		'core/code',
		'core/column',
		'core/columns',
		'core/group',
		'core/nextpage',
		'core/separator',
		'core/shortcode',
		'core/spacer',
	);

	/**
	 * Holds the name of the block type being currently parsed.
	 *
	 * @var string $block_type Similar to {@see WP_Block_Parser_Block::$blockName}.
	 */
	private $block_type;

	/**
	 * Only keeps the rules matching a certain block type.
	 *
	 * @since 3.3
	 *
	 * @param string $block_type {@see WP_Block_Parser_Block::$blockName}.
	 * @return PLL_Translation_Block_Parsing_Rules $this This object with its $rules property updated.
	 */
	public function set_block_name( $block_type ) {
		$this->block_type = $block_type;
		return $this;
	}

	/**
	 * Extracts translatable parts from the block content.
	 * Returns an empty array if the parsing rules are not defined.
	 *
	 * @since 3.3
	 *
	 * @param string $content {@see WP_Block_Parser_Block::$innerContent}.
	 * @return string[] Parsing rules as array keys, strings to translate as array values.
	 *
	 * @phpstan-return array<string,string>
	 */
	public function parse( $content ) {
		$rules = $this->get_parsing_rules( $this->block_type );

		if ( ! isset( $rules[ $this->block_type ] ) ) {
			return array();
		}

		// Check if there's HTML encoded non-breaking-space, if so decode it for consistency.
		$content = str_replace( '&nbsp;', html_entity_decode( '&nbsp;' ), $content );

		$rules             = $rules[ $this->block_type ];
		$sanitized_content = wp_kses_post( $content );

		if ( empty( $sanitized_content ) ) {
			return array();
		}

		return ( new PLL_DOM_Content( $content ) )->get_strings( $rules );
	}

	/**
	 * Tells if a block should be parsed using Xpath rules.
	 *
	 * @since 3.3
	 *
	 * @param array $block An array mimicking a {@see WP_Block_Parser_Block}.
	 * @return bool
	 */
	public function has_parsing_rules( $block ) {
		$rules = $this->get_parsing_rules( $block['blockName'] );
		return isset( $rules[ $block['blockName'] ] );
	}

	/**
	 * Tells if a block needs to be parsed, because it contains contents to be translated.
	 * Though, even if not, it may contain blocks that need to be parsed.
	 *
	 * @since 3.3
	 *
	 * @param array $block An array mimicking a {@see WP_Block_Parser_Block}.
	 * @return bool
	 */
	public function should_be_parsed( $block ) {
		if ( '' === trim( $block['innerHTML'] ) && empty( $block['attrs'] ) ) {
			// A block that doesn't have contents and attributes.
			return false;
		}

		if ( in_array( $block['blockName'], $this->get_blocks_not_to_parse(), true ) ) {
			/**
			 * A known block that doesn't contain contents to be translated.
			 * Let's avoid useless operations.
			 */
			return false;
		}

		return true;
	}

	/**
	 * Returns the rules as Xpath expressions to evaluate in the blocks content.
	 *
	 * @since 3.3
	 *
	 * @param string|null $block_name Optional. The block name we want to get the parsing rules for.
	 *                                Only necessary for back-compatibility with the old `core-embed/` blocks.
	 * @return string[][]
	 *
	 * @phpstan-return XpathParsingRules
	 */
	private function get_parsing_rules( $block_name = null ) {
		if ( ! isset( $this->cache['parsing_rules'] ) || ! is_array( $this->cache['parsing_rules'] ) ) {
			/**
			 * Filters the rules as Xpath expressions to evaluate in the blocks content.
			 *
			 * @since 3.3
			 *
			 * @param string[][] $parsing_rules Rules as Xpath expressions to evaluate in the blocks content.
			 *
			 * @phpstan-param XpathParsingRules $parsing_rules
			 */
			$this->cache['parsing_rules'] = (array) apply_filters( 'pll_blocks_xpath_rules', $this->parsing_rules );
		}

		$parsing_rules = $this->cache['parsing_rules'];

		if ( is_string( $block_name ) && ! isset( $parsing_rules[ $block_name ] ) && strpos( $block_name, 'core-embed/' ) === 0 ) {
			$parsing_rules[ $block_name ] = array(
				'//figure/figcaption',
			);
		}

		return $parsing_rules;
	}

	/**
	 * Returns the rules for the attributes to translate.
	 *
	 * @since 3.3
	 * @since 3.6 Format changed from `array<string>` to `array<non-falsy-string, array<non-empty-string, array|true>>`.
	 *
	 * @return array Rules for block attributes to translate.
	 *               Array keys are block names for the 1st level, then attribute names for the next levels.
	 *               Arrays values are `true` or an array containing sub attributes.
	 *               Wildcards are allowed. Ex:
	 *               array(
	 *                   'block/name' => array(
	 *                       'sub_key_1' => true,
	 *                       'sub_key_2' => array(
	 *                           'sub_sub_key_*' => true,
	 *                       ),
	 *                   ),
	 *               )
	 *
	 * @phpstan-return AttributesParsingRules
	 */
	private function get_parsing_rules_for_attributes() {
		if ( ! isset( $this->cache['parsing_rules_for_attributes'] ) || ! is_array( $this->cache['parsing_rules_for_attributes'] ) ) {
			/**
			 * Filters the list of blocks attributes to translate.
			 *
			 * @since 3.3
			 * @since 3.6 Format changed from `array<string>` to `array<non-falsy-string, array<non-empty-string, array|true>>`.
			 *
			 * @param array $parsing_rules_attributes Rules for block attributes to translate.
			 *               Array keys are block names for the 1st level, then attribute names for the next levels.
			 *               Arrays values are `true` or an array containing sub attributes.
			 *               Wildcards are allowed. Ex:
			 *               array(
			 *                   'block/name' => array(
			 *                       'sub_key_1' => true,
			 *                       'sub_key_2' => array(
			 *                           'sub_sub_key_*' => true,
			 *                       ),
			 *                   ),
			 *               )
			 */
			$this->cache['parsing_rules_for_attributes'] = (array) apply_filters( 'pll_blocks_rules_for_attributes', $this->parsing_rules_attributes );

			// Backward compatibility with Polylang < 3.6.
			$this->cache['parsing_rules_for_attributes'] = $this->handle_blocks_rules_for_attributes_old_format( $this->cache['parsing_rules_for_attributes'] );
		}

		return $this->cache['parsing_rules_for_attributes'];
	}

	/**
	 * Converts the old format for the rules for attributes (used from v3.3)
	 * by changing it to the new format (used since v3.6).
	 *
	 * @since 3.6
	 *
	 * @param array $rules Rules for attributes (old and new formats).
	 * @return array Rules for attributes (new format only).
	 *
	 * @phpstan-param array<non-falsy-string, (array<non-empty-string, array|true>|array<int, non-empty-string>)> $rules
	 * @phpstan-return AttributesParsingRules
	 */
	private function handle_blocks_rules_for_attributes_old_format( array $rules ): array {
		$deprecated = array();

		// Change old format into new format.
		foreach ( $rules as $block_name => $attributes ) {
			foreach ( $attributes as $index => $value ) {
				if ( is_string( $value ) ) {
					$deprecated[] = $value;
					$rules[ $block_name ][ $value ] = true;
					unset( $rules[ $block_name ][ $index ] );
				}
			}
		}

		/**
		 * Filters whether to trigger an error for deprecated argument format.
		 *
		 * @since 3.6
		 *
		 * @param bool $trigger Whether to trigger the error for deprecated argument format. Default true.
		 */
		if ( ! empty( $deprecated ) && WP_DEBUG && apply_filters( 'deprecated_argument_trigger_error', true ) ) {
			$message  = "Filter 'pll_blocks_rules_for_attributes' was used with an argument format that is <strong>deprecated</strong> since version 3.6!";
			$message .= "\n<pre>\n" . wp_strip_all_tags( var_export( $deprecated, true ) ) . "\n</pre>"; // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export

			trigger_error( $message, E_USER_DEPRECATED ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error, WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		/** @var AttributesParsingRules */
		return $rules;
	}

	/**
	 * Returns the list of blocks not to parse.
	 *
	 * @since 3.3
	 *
	 * @return string[]
	 */
	private function get_blocks_not_to_parse() {
		$blocks_not_to_parse = isset( $this->cache['blocks_not_to_parse'] ) ? $this->cache['blocks_not_to_parse'] : null;

		if ( is_array( $blocks_not_to_parse ) ) {
			return $blocks_not_to_parse;
		}

		/**
		 * Filters the list of blocks not to parse.
		 *
		 * @since 3.3
		 *
		 * @param string[] $blocks_not_to_parse List of blocks not to parse.
		 */
		$this->cache['blocks_not_to_parse'] = (array) apply_filters( 'pll_blocks_not_to_parse', $this->blocks_not_to_parse );

		return $this->cache['blocks_not_to_parse'];
	}

	/**
	 * Checks if a block has translatable attributes (or not) and returns them.
	 *
	 * @since 3.3
	 *
	 * @param array $block An array mimicking a {@see WP_Block_Parser_Block}.
	 * @return array An array with attributes to translate or an empty array.
	 *
	 * @phpstan-return array<non-empty-string, array|true>
	 */
	public function get_attributes_to_translate( $block ) {
		if ( empty( $block['attrs'] ) ) {
			return array();
		}

		$rules = $this->get_parsing_rules_for_attributes();
		if ( ! isset( $rules[ $block['blockName'] ] ) ) {
			return array();
		}

		return $rules[ $block['blockName'] ];
	}
}
