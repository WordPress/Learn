<?php
/**
 * @package Polylang-Pro
 */

/**
 * A Service to collect linked terms.
 *
 * @since 3.3
 */
class PLL_Collect_Linked_Terms {

	/**
	 * Stores the ID's of the posts that have been parsed already.
	 * The idea is to prevent processing the same posts several times via the reusable block, or worse, infinite loops.
	 *
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	protected $processed_posts = array();

	/**
	 * Gets all the term objects linked to a set of posts.
	 *
	 * @since 3.3
	 *
	 * @param  WP_Post[] $posts      An array of post objects.
	 * @param  string[]  $taxonomies Optional. Terms will be limited to the given taxonomies.
	 * @return WP_Term[]             An array of linked term objects.
	 */
	public function get_linked_terms( array $posts, array $taxonomies = array() ) {
		$this->processed_posts = array();
		$linked_ids            = array();

		foreach ( $posts as $post ) {
			$linked_ids = array_merge( $linked_ids, $this->get_term_ids_from_post( $post ) );
		}

		$this->processed_posts = array();

		if ( empty( $linked_ids ) ) {
			return array();
		}

		$linked_ids = array_unique( $linked_ids );

		$terms = get_terms(
			array(
				'include'    => $linked_ids,
				'taxonomy'   => $taxonomies,
				'hide_empty' => false,
			)
		);

		return is_array( $terms ) ? $terms : array();
	}

	/**
	 * Returns all the term IDs linked in a post.
	 *
	 * @since 3.3
	 *
	 * @param WP_Post $post A given WP_Post object.
	 * @return int[] An array of term IDs.
	 * @phpstan-return array<int<0, max>, positive-int>
	 */
	protected function get_term_ids_from_post( WP_Post $post ) {
		if ( isset( $this->processed_posts[ $post->ID ] ) ) {
			return array();
		}

		$this->processed_posts[ $post->ID ] = $post->ID;

		$linked_ids = array();

		if ( has_blocks( $post->post_content ) ) {
			$linked_ids = $this->get_term_ids_from_block_content( $post->post_content );
		}

		/**
		 * Filters the term IDs linked in a post.
		 *
		 * @since 3.3
		 *
		 * @param int[]   $linked_ids Term IDs linked in a post.
		 * @param WP_Post $post       The post we get term IDs from.
		 */
		$linked_ids = apply_filters( 'pll_collect_term_ids', $linked_ids, $post );

		/** @phpstan-var array<int<0, max>, positive-int> */
		return array_unique( $linked_ids );
	}

	/**
	 * Returns the term IDs from block type content.
	 *
	 * @since 3.3
	 *
	 * @param string $post_content The content of the post.
	 * @return int[] An array of term IDs.
	 *
	 * @phpstan-return array<int<0, max>, positive-int>
	 */
	protected function get_term_ids_from_block_content( $post_content ) {
		return $this->get_term_ids_from_blocks( parse_blocks( $post_content ) );
	}

	/**
	 * Returns the term IDs from blocks.
	 *
	 * @since 3.3
	 *
	 * @param array[] $blocks An array of blocks.
	 * @return int[] An array of term IDs.
	 *
	 * @phpstan-return array<int<0, max>, positive-int>
	 */
	protected function get_term_ids_from_blocks( array $blocks ) {
		$term_ids = array();

		foreach ( $blocks as $block ) {
			$term_ids = array_merge( $term_ids, $this->get_term_ids_from_block( $block ) );
		}

		return array_unique( $term_ids );
	}

	/**
	 * Returns the term IDs from a block.
	 *
	 * @since 3.3
	 *
	 * @param array $block A representative array of a block.
	 * @return int[] An array of term IDs.
	 *
	 * @phpstan-return array<int<0, max>, positive-int>
	 */
	protected function get_term_ids_from_block( array $block ) {
		$term_ids = array();

		switch ( $block['blockName'] ) {
			case 'core/block':
				$term_ids = array_merge( $term_ids, $this->get_term_ids_from_reusable_block( $block ) );
				break;

			case 'core/latest-posts':
				$term_ids = array_merge( $term_ids, $this->get_term_ids_from_latest_posts_block( $block ) );
				break;

			case 'core/query':
				$term_ids = array_merge( $term_ids, $this->get_term_ids_from_query_block( $block ) );
				break;
		}

		if ( ! empty( $block['innerBlocks'] ) ) {
			$term_ids = array_merge( $term_ids, $this->get_term_ids_from_blocks( $block['innerBlocks'] ) );
		}

		return array_unique( $term_ids );
	}

	/**
	 * Returns the term IDs from a reusable block.
	 *
	 * @since 3.3
	 *
	 * @param array $block A representative array of a block.
	 * @return int[] An array of term IDs.
	 *
	 * @phpstan-return array<int<0, max>, positive-int>
	 */
	protected function get_term_ids_from_reusable_block( array $block ) {
		if ( empty( $block['attrs']['ref'] ) || ! is_int( $block['attrs']['ref'] ) ) {
			return array();
		}

		$post_id = $block['attrs']['ref'];

		if ( isset( $this->processed_posts[ $post_id ] ) ) {
			return array();
		}

		$this->processed_posts[ $post_id ] = $post_id;

		$linked_post = get_post( $post_id );

		if ( ! $linked_post instanceof WP_Post ) {
			return array();
		}

		return $this->get_term_ids_from_block_content( $linked_post->post_content );
	}

	/**
	 * Returns the term IDs from a latest posts block.
	 *
	 * @since 3.3
	 *
	 * @param array $block A representative array of a block.
	 * @return int[] An array of term IDs.
	 *
	 * @phpstan-return array<int<0, max>, positive-int>
	 */
	protected function get_term_ids_from_latest_posts_block( array $block ) {
		if ( empty( $block['attrs']['categories'] ) || ! is_array( $block['attrs']['categories'] ) ) {
			return array();
		}

		/**
		 * The terms are available like this:
		 * array(
		 *     'categories' => array(
		 *         0 => array(
		 *             'id' => 12,
		 *             // ...
		 *         ),
		 *         1 => array(
		 *             'id' => 16,
		 *             // ...
		 *         ),
		 *     ),
		 *   )
		 */
		/** @phpstan-var array<int<0, max>, positive-int> */
		return array_column( $block['attrs']['categories'], 'id' );
	}

	/**
	 * Returns the term IDs from a query block.
	 *
	 * @since 3.3
	 *
	 * @param array $block A representative array of a block.
	 * @return int[] An array of term IDs.
	 *
	 * @phpstan-return array<int<0, max>, positive-int>
	 */
	protected function get_term_ids_from_query_block( array $block ) {
		if ( empty( $block['attrs']['query']['taxQuery'] ) || ! is_array( $block['attrs']['query']['taxQuery'] ) ) {
			return array();
		}

		/**
		 * The terms are available like this:
		 * array(
		 *     // ...
		 *     'query' => array(
		 *         // ...
		 *         'taxQuery' => array(
		 *             'post_tag' => array(
		 *                 0 => 261,
		 *             ),
		 *             'category' => array(
		 *                 0 => 12,
		 *             ),
		 *         ),
		 *     ),
		 *   )
		 */
		/** @phpstan-var array<int<0, max>, positive-int> */
		return array_merge( ...array_values( $block['attrs']['query']['taxQuery'] ) );
	}
}
