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

		return $entries;
	}
}
