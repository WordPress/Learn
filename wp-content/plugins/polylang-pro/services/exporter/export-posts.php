<?php
/**
 * @package Polylang-Pro
 */

/**
 * Class handling multiple or single posts export.
 *
 * @since 3.6
 */
class PLL_Export_Posts extends PLL_Export_Translated_Objects {
	/**
	 * Allows to add post metas to export file.
	 *
	 * @var PLL_Export_Post_Metas
	 */
	private $post_metas;

	/**
	 * Constructor.
	 *
	 * @since 3.6
	 *
	 * @param PLL_Translated_Post $translated_object Post translation object.
	 */
	public function __construct( PLL_Translated_Post $translated_object ) {
		parent::__construct( $translated_object );

		$this->post_metas = new PLL_Export_Post_Metas();
	}

	/**
	 * Adds one post to export.
	 *
	 * @since 3.6
	 *
	 * @param PLL_Export_Data $export Export object.
	 * @param WP_Post         $item   Post to export.
	 * @return void
	 */
	public function add_item( PLL_Export_Data $export, $item ) {
		$tr_id   = $this->translated_object->get( $item->ID, $export->get_target_language() );
		$tr_item = get_post( $tr_id );

		$default_fields = array(
			PLL_Import_Export::POST_TITLE,
			PLL_Import_Export::POST_CONTENT,
			PLL_Import_Export::POST_EXCERPT,
		);

		/**
		 * Filters which post fields we want to export.
		 *
		 * @since 3.5
		 *
		 * @param string[] $allowed_fields List of post fields names.
		 * @param WP_Post  $item           Post object.
		 */
		$allowed_fields = apply_filters( 'pll_export_post_fields', $default_fields, $item );
		$allowed_fields = array_intersect( $default_fields, $allowed_fields );

		foreach ( $allowed_fields as $field ) {
			if ( '' === $item->$field ) {
				continue;
			}

			if ( PLL_Import_Export::POST_CONTENT === $field ) {
				$this->add_post_content(
					$export,
					$item,
					$tr_item instanceof WP_Post ? $tr_item->post_content : ''
				);
			} else {
				$export->add_translation_entry(
					array(
						'object_type' => PLL_Import_Export::TYPE_POST,
						'field_type'  => $field,
						'object_id'   => $item->ID,
					),
					$item->$field,
					$tr_item instanceof WP_Post ? $tr_item->$field : ''
				);
			}
		}

		$this->post_metas->export( $export, $item->ID, $tr_item instanceof WP_Post ? $tr_item->ID : 0 );
	}

	/**
	 * Caches posts to avoid too many SQL queries during export.
	 *
	 * @since 3.6
	 *
	 * @param int[] $ids Post IDs.
	 * @return void
	 *
	 * @phpstan-param non-empty-array<positive-int> $ids
	 */
	protected function add_to_cache( array $ids ) {
		_prime_post_caches( $ids );
	}

	/**
	 * Returns ID corresponding to the given post.
	 *
	 * @since 3.6
	 *
	 * @param WP_Post $item Post to get ID from.
	 * @return int Post ID.
	 */
	protected function get_item_id( $item ): int {
		return $item->ID;
	}

	/**
	 * Adds post content to exported data.
	 *
	 * @since 3.6
	 *
	 * @param PLL_Export_Data $export      Export object.
	 * @param WP_Post         $post        Source post.
	 * @param string          $translation Translated post content.
	 * @return void
	 */
	private function add_post_content( PLL_Export_Data $export, WP_Post $post, string $translation ) {
		$content      = PLL_Translation_Walker_Factory::create_from( $post->post_content );
		$translations = new Translations();
		$content->walk( array( $translations, 'add_entry' ) );

		foreach ( $translations->entries as $entry ) {
			if ( '' === $entry->singular ) {
				continue;
			}

			// The translated post content isn't exported when source or translated posts have blocks.
			$translation = has_blocks( $translation ) || has_blocks( $post->post_content ) ? '' : $translation;
			$export->add_translation_entry(
				array(
					'object_type' => PLL_Import_Export::TYPE_POST,
					'field_type'  => PLL_Import_Export::POST_CONTENT,
					'object_id'   => $post->ID,
				),
				$entry->singular,
				$translation
			);
		}
	}
}
