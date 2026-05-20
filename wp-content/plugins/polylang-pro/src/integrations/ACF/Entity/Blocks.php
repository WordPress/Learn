<?php
/**
 * @package  Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Integrations\ACF\Entity;

use WP_Post;
use Translations;
use PLL_Language;
use PLL_Export_Data;
use WP_Syntex\Polylang_Pro\Integrations\ACF\Strategy\Copy;
use WP_Syntex\Polylang_Pro\Integrations\ACF\Strategy\Import;
use WP_Syntex\Polylang_Pro\Integrations\ACF\Strategy\Export;
use WP_Syntex\Polylang_Pro\Integrations\ACF\Strategy\Abstract_Strategy;

/**
 * This class is part of the ACF compatibility.
 * Synchronizes IDs and translates strings in ACF blocks.
 *
 * @since 3.7
 */
class Blocks implements Translatable_Entity_Interface {

	/**
	 * ID of the post that contains blocks.
	 *
	 * @var int
	 */
	private $id;

	/**
	 * Constructor
	 *
	 * @since 3.7
	 *
	 * @param int $id The post ID, defaults to 0.
	 */
	public function __construct( int $id = 0 ) {
		$this->id = $id;
	}

	/**
	 * Copies the field values in blocks.
	 *
	 * @since 3.7
	 *
	 * @param array        $blocks      The blocks.
	 * @param PLL_Language $target_lang The target language.
	 * @param WP_Post      $source_post The source post.
	 * @return array The blocks.
	 */
	public function copy( array $blocks, PLL_Language $target_lang, WP_Post $source_post ) {
		return $this->apply_on_blocks(
			new Copy(),
			$blocks,
			$source_post->ID,
			$target_lang
		);
	}

	/**
	 * Translates fields in blocks during import.
	 *
	 * @since 3.7
	 *
	 * @param WP_Post      $to          The translated post.
	 * @param PLL_Language $target_lang The target language.
	 * @param Translations $translations A set of translations to search the custom fields translations in.
	 * @return WP_Post The translated post.
	 */
	public function translate( WP_Post $to, PLL_Language $target_lang, Translations $translations ): WP_Post {
		$post = get_post( $this->id );

		if ( empty( $post ) || ! has_blocks( $post ) ) {
			return $to;
		}

		$new_post_content = serialize_blocks(
			$this->apply_on_blocks(
				new Import( $translations ),
				parse_blocks( $to->post_content ),
				$post->ID,
				$target_lang
			)
		);

		$to->post_content = $new_post_content;

		return $to;
	}

	/**
	 * Adds ACF fields to the exported data.
	 *
	 * @since 3.7
	 *
	 * @param PLL_Export_Data $export The export data.
	 * @return void
	 */
	public function export( PLL_Export_Data $export ) {
		$post = get_post( $this->id );

		if ( empty( $post ) || ! has_blocks( $post ) ) {
			return;
		}

		$this->apply_on_blocks(
			new Export( $export ),
			parse_blocks( $post->post_content ),
			$post->ID,
			$export->get_target_language()
		);
	}

	/**
	 * Executes a strategy on blocks from the current post to a target post.
	 *
	 * @since 3.7
	 *
	 * @param Abstract_Strategy $strategy Strategy to execute.
	 * @param int               $to       ID of the target post. Not used.
	 * @param array             $args     {
	 *      Array of arguments.
	 *
	 *      @type PLL_Language|null $target_language The language used to apply the strategy.
	 * }
	 * @return void
	 */
	public function apply_to_all_fields( Abstract_Strategy $strategy, int $to = 0, array $args = array() ) {
		$post = get_post( $this->id );

		if ( empty( $post ) || ! has_blocks( $post ) ) {
			return;
		}

		$this->apply_on_blocks(
			$strategy,
			parse_blocks( $post->post_content ),
			$post->ID,
			$args['target_language'] ?? null
		);
	}

	/**
	 * Apply given strategy to field values in blocks.
	 *
	 * @since 3.7
	 *
	 * @param Abstract_Strategy $strategy The strategy.
	 * @param array             $blocks   List of blocks.
	 * @param int               $id       The post ID.
	 * @param PLL_Language|null $language The language used to apply the strategy:
	 *                                    the target language when applying a translate strategy,
	 *                                    unused i.e. null when collecting linked content.
	 * @return array The blocks.
	 */
	private function apply_on_blocks( Abstract_Strategy $strategy, array $blocks, int $id, ?PLL_Language $language = null ): array {
		foreach ( $blocks as &$block ) {
			if ( ! isset( $block['blockName'], $block['attrs'] ) ) {
				// No can do.
				continue;
			}

			if ( empty( acf_get_block_type( $block['blockName'] ) ) ) {
				if ( ! empty( $block['innerBlocks'] ) ) {
					$block['innerBlocks'] = $this->apply_on_blocks( $strategy, $block['innerBlocks'], $id, $language );
				}
				continue;
			}

			/*
			 * Ensure the block has the required keys for `acf_prepare_block()`.
			 */
			$block['name'] = $block['blockName'];
			if ( empty( $block['id'] ) ) {
				$block['id'] = acf_ensure_block_id_prefix( acf_get_block_id( $block['attrs'] ) );
			}

			$block = acf_prepare_block( $block );

			if ( ! isset( $block['data'] ) ) {
				// No can do.
				continue;
			}

			// Backward compatibility with ACF < 6.3.
			if ( function_exists( 'acf_add_block_meta_values' ) && function_exists( 'acf_block_uses_post_meta' ) && acf_block_uses_post_meta( $block ) ) {
				$block                  = acf_add_block_meta_values( $block, $id );
				$block['attrs']['data'] = $block['data'];
			}

			if ( ! isset( $block['attrs']['data'] ) ) {
				// No can do.
				continue;
			}

			/*
			 * Loads block fields values like ACF does.
			 * @see {acf_render_block()}.
			 */
			acf_setup_meta( $block['attrs']['data'], $block['id'], true );

			$values = array();
			foreach ( acf_get_block_fields( $block['attrs'] ) as $field ) {
				$value                    = acf_get_value( $block['id'], $field );
				$values[ $field['key'] ] = $strategy->execute(
					new Post( $id ),
					$value,
					$field,
					array(
						'target_language' => $language,
						'original_value'  => $field['default_value'] ?? null,
					)
				);
			}

			if ( ! empty( $values ) ) {
				/*
				 * Converts "data" to "meta" and ensures the block attributes are all set.
				 * `acf_setup_meta()` cannot be used directly because it relies on form data, which is not the case here.
				 * @see {acf_parse_save_blocks_callback()}.
				 */
				$block['attrs']['data'] = acf_get_instance( 'ACF_Local_Meta' )
					->capture(
						$values,
						$block['id']
					);

				// Backward compatibility with ACF < 6.3.
				if ( function_exists( 'acf_block_uses_post_meta' ) && acf_block_uses_post_meta( $block ) ) {
					$block = $this->prepare_block_post_meta( $block );
				}
			}

			/*
			 * Reset block data for safety, so we don't change its data tree by mistake.
			 * @see {acf_render_block()}.
			 */
			acf_reset_meta( $block['id'] );

			if ( ! empty( $block['innerBlocks'] ) ) {
				$block['innerBlocks'] = $this->apply_on_blocks( $strategy, $block['innerBlocks'], $id, $language );
			}
		}

		return $blocks;
	}

	/**
	 * Converts block data using meta keys to block data using field keys.
	 * ACF requires a specific format for it to be saved in post meta.
	 *
	 * @since 3.7
	 *
	 * @param array $block The block to convert.
	 * @return array The converted block.
	 */
	private function prepare_block_post_meta( array $block ): array {
		$new_data = array();
		foreach ( $block['attrs']['data'] as $key => $value ) {
			if ( ! str_starts_with( $key, '_' ) || empty( $value ) ) {
				continue;
			}

			// We got a field key in `$value`.
			$field = acf_get_field( $value );
			if ( empty( $field ) ) {
				continue;
			}

			$new_data[ $field['key'] ] = acf_get_value( $block['id'], $field );
		}

		$block['attrs']['data'] = $new_data;

		return $block;
	}
}
