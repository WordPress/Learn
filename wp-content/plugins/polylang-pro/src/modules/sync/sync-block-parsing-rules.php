<?php
/**
 * @package Polylang-Pro
 */

/**
 * Holds the rules defining which part of a block should be "synchronized".
 *
 * @since 3.7
 */
class PLL_Sync_Block_Parsing_Rules {
	/**
	 * Caches values about parsable blocks.
	 *
	 * @var array
	 */
	private $cache = array();

	/**
	 * Holds some rules for block attributes to synchronize.
	 *
	 * @var array
	 */
	private $parsing_rules_attributes = array(
		'core/audio'      => array(
			'attachment' => array( 'id' => true ),
		),
		'core/video'      => array(
			'attachment' => array( 'id' => true ),
		),
		'core/image'      => array(
			'attachment' => array( 'id' => true ),
		),
		'core/cover'      => array(
			'attachment' => array( 'id' => true ),
		),
		'core/gallery'    => array(
			'attachment' => array( 'ids' => true ),
		),
		'core/media-text' => array(
			'attachment' => array( 'mediaId' => true ),
		),
		'core/block'      => array(
			'wp_block' => array( 'ref' => true ),
		),
	);

	/**
	 * Holds some rules as Xpath expressions to evaluate in the blocks content.
	 * Keep this list alphabetically sorted when adding entries.
	 *
	 * @var string[][]
	 * @phpstan-var array<string, array<string, array>>
	 */
	private $parsing_rules = array();

	/**
	 * Extracts translatable parts from HTML block content.
	 * Returns an array of XPath, empty if no rules defined.
	 *
	 * @since 3.7
	 *
	 * @param array $block An array mimicking a {@see WP_Block_Parser_Block}.
	 * @return string[][] Parsing rules as array keys, strings to translate as array values.
	 *
	 * @phpstan-return array<string,array<string, string>>
	 */
	public function html( array $block ): array {
		$content = implode( PLL_Translation_Walker_Blocks::BLOCK_PLACEHOLDER, $block['innerContent'] );
		return array_map(
			function ( $rules ) use ( $content ) {
				return ( new PLL_DOM_Content( $content ) )->get_strings( $rules );
			},
			$this->get_xpath_rules( $block )
		);
	}

	/**
	 * Checks if a block has synchronized attributes (or not) and returns them.
	 *
	 * @since 3.7
	 *
	 * @param array $block An array mimicking a {@see WP_Block_Parser_Block}.
	 * @return array An array with attributes to synchronize or an empty array.
	 *
	 * @phpstan-return array<non-empty-string, array<'post'|'term'|'attachment', array<non-empty-string>>>
	 */
	public function attributes( array $block ): array {
		if ( empty( $block['attrs'] ) ) {
			return array();
		}

		if ( ! isset( $this->cache['parsing_rules_attributes'] ) || ! is_array( $this->cache['parsing_rules_attributes'] ) ) {
			/**
			 * Filters the list of blocks attributes to synchronize.
			 *
			 * @since 3.7
			 *
			 * @param array $parsing_rules_attributes Rules for block attributes to synchronize.
			 *               Array keys are block names for the 1st level, then attribute names for the next levels.
			 *               Arrays values are `post`, `term`, `attachment` or an array containing sub attributes.
			 *               Wildcards are allowed. Ex:
			 *               array(
			 *                   'block/name' => array(
			 *                       'post' => array(
			 *                           'sub_key_1' => true,
			 *                           'sub_key_2' => array(
			 *                               'sub_sub_key_*'         => true,
			 *                               'another_sub_sub_key_*' => true,
			 *                           ),
			 *                       ),
			 *                       'term' => array(
			 *                           'another_key' => true,
			 *                       ),
			 *                   ),
			 *               )
			 */
			$this->cache['parsing_rules_attributes'] = (array) apply_filters( 'pll_sync_block_rules_for_attributes', $this->parsing_rules_attributes );
		}

		if ( ! isset( $this->cache['parsing_rules_attributes'][ $block['blockName'] ] ) ) {
			return array();
		}

		return $this->cache['parsing_rules_attributes'][ $block['blockName'] ];
	}

	/**
	 * Checks if a block has synchronized IDs in its content (or not) and returns their XPath rules.
	 *
	 * @since 3.7
	 *
	 * @param array $block An array mimicking a {@see WP_Block_Parser_Block}.
	 * @return array An array with Xpath rules to synchronize as key, and type of content as value. Or an empty array.
	 *
	 * @phpstan-return array<array<string>>
	 */
	private function get_xpath_rules( array $block ): array {
		if ( empty( $block['innerHTML'] ) ) {
			return array();
		}

		if ( ! isset( $this->cache['parsing_rules'] ) || ! is_array( $this->cache['parsing_rules'] ) ) {
			/**
			 * Filters the rules as Xpath expressions to to synchronize IDs in the blocks content.
			 *
			 * @since 3.7
			 *
			 * @param string[][] $parsing_rules Rules as Xpath expressions to synchronize IDs in the blocks content, with block name as first level key and type as second level key.
			 *                   Such as:
			 *                   array(
			 *                   'block/name' => array(
			 *                       'post'       => array( '//p[@class="some-id"]' ),
			 *                       'term'       => array( '//p[@class="the-id"]' ),
			 *                       'attachment' => array( '//p[@class="another-id"]' ),
			 *                       'wp_block'   => array( '//p[@class="an-id"]' ),
			 *                   ),
			 *               )
			 * @phpstan-param array<string, array<string, array>> $parsing_rules
			 */
			$this->cache['parsing_rules'] = (array) apply_filters( 'pll_sync_blocks_xpath_rules', $this->parsing_rules );
		}

		if ( ! isset( $block['blockName'], $this->cache['parsing_rules'][ $block['blockName'] ] ) ) {
			return array();
		}

		return $this->cache['parsing_rules'][ $block['blockName'] ];
	}
}
