<?php
/**
 * @package Polylang-Pro
 */

defined( 'ABSPATH' ) || exit;

/**
 * An abstract class allowing to bulk edit template slugs.
 *
 * @since 3.2
 */
abstract class PLL_FSE_Abstract_Bulk_Edit_Template_Slugs_Module extends PLL_FSE_Abstract_Module {

	/**
	 * Adds a language suffix to the slugs belonging to templates in the given language.
	 *
	 * @since 3.2
	 *
	 * @param PLL_Language $lang          The language to use to find the templates to suffix.
	 * @param string|null  $new_lang_slug Optional. The new lang slug to use. Default is `$lang`'s slug.
	 * @return int Number of posts updated.
	 */
	protected function update_language_suffix_in_post_names( PLL_Language $lang, $new_lang_slug = null ) {
		$language_slugs = $this->get_languages_slugs();
		$new_lang_slug  = ! empty( $new_lang_slug ) ? $new_lang_slug : $lang->slug;
		$posts          = $this->get_template_posts_from_language( $lang );

		// Make sure the slug of the language we're looking for is in the list of language slugs.
		$language_slugs[] = $lang->slug;
		$language_slugs   = array_unique( $language_slugs );

		foreach ( $posts as $i => $post ) {
			$new_slug = ( new PLL_FSE_Template_Slug( $post->post_name, $language_slugs ) )->update_language( $new_lang_slug );

			if ( $post->post_name === $new_slug ) {
				unset( $posts[ $i ] );
			} else {
				$posts[ $i ]->post_name = $new_slug;
				wp_cache_delete( $post->ID, 'posts' );
			}
		}

		return $this->update_template_post_slugs( $posts );
	}

	/**
	 * Removes the language suffix from the slugs belonging to templates in the given language.
	 *
	 * @since 3.2
	 *
	 * @param PLL_Language $lang The language to use to find the templates to unsuffix.
	 * @return int Number of posts updated.
	 */
	protected function remove_language_suffix_from_post_names( PLL_Language $lang ) {
		$language_slugs = $this->get_languages_slugs();
		$posts          = $this->get_template_posts_from_language( $lang );

		// Make sure the slug of the language we're looking for is in the list of language slugs.
		$language_slugs[] = $lang->slug;
		$language_slugs   = array_unique( $language_slugs );

		foreach ( $posts as $i => $post ) {
			$new_slug = ( new PLL_FSE_Template_Slug( $post->post_name, $language_slugs ) )->remove_language();

			if ( $post->post_name === $new_slug ) {
				unset( $posts[ $i ] );
			} else {
				$posts[ $i ]->post_name = $new_slug;
				wp_cache_delete( $post->ID, 'posts' );
			}
		}

		return $this->update_template_post_slugs( $posts );
	}

	/**
	 * Returns a list of template posts associated with the given language.
	 *
	 * @since 3.2
	 *
	 * @param PLL_Language $lang The language.
	 * @return stdClass[] {
	 *     An array of objects with the following properties.
	 *
	 *     @type int    $ID        A post ID.
	 *     @type string $post_name A post slug.
	 * }
	 */
	protected function get_template_posts_from_language( PLL_Language $lang ) {
		/** @var WP_Post[] */
		$results = ( new WP_Query() )->query(
			array(
				'post_type'              => PLL_FSE_Tools::get_template_post_types(),
				'post_status'            => array( 'publish', 'draft' ),
				'tax_query'              => array(
					array(
						'terms' => array( $lang->get_tax_prop( 'language', 'term_taxonomy_id' ) ),
						'field' => 'term_taxonomy_id',
					),
				),
				'posts_per_page'         => -1,
				'orderby'                => 'ID',
				'order'                  => 'ASC',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'lang'                   => '',
			)
		);

		/** @var stdClass[] $results */
		foreach ( $results as $k => $result ) {
			$results[ $k ] = (object) array(
				'ID'        => (int) $result->ID,
				'post_name' => $result->post_name,
			);
		}

		return $results;
	}

	/**
	 * Updates the slug of the given post.
	 *
	 * @since 3.2
	 *
	 * @global wpdb $wpdb
	 *
	 * @param stdClass[] $posts {
	 *     An array of objects with the following properties.
	 *
	 *     @type int    $ID        A post ID.
	 *     @type string $post_name The new post slug.
	 * }
	 * @return int Number of posts updated.
	 */
	protected function update_template_post_slugs( array $posts ) {
		global $wpdb;

		if ( empty( $posts ) ) {
			return 0;
		}

		$case     = array();
		$post_ids = array();

		foreach ( $posts as $post ) {
			$case[]     = array( $post->ID, $post->post_name );
			$post_ids[] = $post->ID;
		}

		return $wpdb->query(
			$wpdb->prepare(
				sprintf(
					"UPDATE {$wpdb->posts} SET post_name = ( CASE ID %s END ) WHERE ID IN (%s)",
					implode( ' ', array_fill( 0, count( $case ), 'WHEN %d THEN %s' ) ),
					implode( ',', array_fill( 0, count( $post_ids ), '%d' ) )
				),
				array_merge( array_merge( ...$case ), $post_ids )
			)
		);
	}
}
