<?php
/**
 * File containing the class \Sensei_Pro_Glossary\Glossary_Repository.
 *
 * @package sensei-pro-glossary
 * @since   1.11.0
 */

namespace Sensei_Pro_Glossary;

use WP_Query;

/**
 * Class for glossary database operations.
 *
 * @internal
 */
class Glossary_Repository {
	/**
	 * Get all glossary entries.
	 *
	 * @internal
	 *
	 * @return Glossary_Entry[]
	 */
	public function get_entries(): array {
		$cache_key = 'glossary_repository_get_entries';
		$cache     = wp_cache_get( $cache_key, 'sensei_pro' );
		if ( false !== $cache ) {
			return $cache;
		}

		$query = new WP_Query(
			[
				'post_type'      => Glossary_Admin::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
			]
		);

		$entries = [];
		foreach ( $query->get_posts() as $post ) {
			if ( ! $post->post_title || ! $post->post_content ) {
				continue;
			}

			$entries[] = new Glossary_Entry(
				$post->post_title,
				$post->post_content
			);
		}

		wp_cache_set( $cache_key, $entries, 'sensei_pro' );

		return $entries;
	}

	/**
	 * Find the glossary entry by phrase.
	 *
	 * @internal
	 *
	 * @param string $phrase The glossary phrase.
	 *
	 * @return Glossary_Entry|null
	 */
	public function get_entry_by_phrase( string $phrase ): ?Glossary_Entry {
		$lowercase_phrase = mb_strtolower( $phrase );

		foreach ( $this->get_entries() as $entry ) {
			if ( mb_strtolower( $entry->get_phrase() ) === $lowercase_phrase ) {
				return $entry;
			}
		}

		return null;
	}
}
