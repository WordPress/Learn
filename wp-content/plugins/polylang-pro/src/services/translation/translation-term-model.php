<?php
/**
 * @package Polylang-Pro
 */

use WP_Syntex\Polylang_Pro\Modules\Import_Export\Services\Context;


/**
 * Translate post taxonomies from a set of translation entries.
 *
 * @since 3.3
 */
class PLL_Translation_Term_Model implements PLL_Translation_Data_Model_Interface {
	use PLL_Translation_Object_Model_Trait;

	/**
	 * Used to manage languages and translations.
	 *
	 * @var PLL_Model
	 */
	private $model;

	/**
	 * Dependency to translate term metas.
	 *
	 * @var PLL_Sync_Term_Metas
	 */
	private $sync_term_metas;

	/**
	 * PLL_Translation_Term_Model constructor.
	 *
	 * @since 3.3
	 *
	 * @param PLL_Settings|PLL_Admin $polylang Polylang object.
	 */
	public function __construct( &$polylang ) {
		$this->model           = &$polylang->model;
		$this->sync_term_metas = &$polylang->sync->term_metas;
	}

	/**
	 * Translates a term.
	 *
	 * @since 3.3
	 *
	 * @param array        $entry           Properties array of an entry.
	 * @param PLL_Language $target_language The target language.
	 * @return int|WP_Error The translated term id, `WP_Error` on failure.
	 */
	public function translate( array $entry, PLL_Language $target_language ) {
		if ( ! $entry['data'] instanceof Translations ) {
			/* translators: %d is a term ID. */
			return new WP_Error( 'pll_translate_term_no_translations', sprintf( __( 'The term with ID %d could not be translated.', 'polylang-pro' ), (int) $entry['id'] ) );
		}

		$source_term = get_term( $entry['id'] );

		if ( ! $source_term instanceof WP_Term ) {
			/* translators: %d is a term ID. */
			return new WP_Error( 'pll_translate_term_no_source_term', sprintf( __( 'The term with ID %d could not be translated as it doesn\'t exist.', 'polylang-pro' ), (int) $entry['id'] ) );
		}

		$tr_term_name        = $this->get_translated_term_name( $source_term, $entry['data'] );
		$tr_term_description = $this->get_translated_term_description( $source_term, $entry['data'] );
		$tr_term_id          = $this->model->term->get( $entry['id'], $target_language );
		$translation_exists  = $tr_term_id > 0;

		if ( $tr_term_id ) {
			// The translation already exists.
			$args = array();
			// Don't update name or description if not provided in translations.
			if ( $source_term->name !== $tr_term_name ) {
				$args['name'] = $tr_term_name;
			}
			if ( $source_term->description !== $tr_term_description ) {
				$args['description'] = $tr_term_description;
			}

			$tr_term = $this->model->term->update( $tr_term_id, $args );
			if ( is_wp_error( $tr_term ) ) {
				/* translators: %d is a term ID. */
				return new WP_Error( 'pll_translate_update_term_failed', sprintf( __( 'The term with ID %d could not be updated.', 'polylang-pro' ), (int) $tr_term_id ) );
			}
		} else {
			$args = array(
				'translations' => $this->model->term->get_translations( $source_term->term_id ),
				'description'  => $tr_term_description,
			);

			$tr_term = $this->model->term->insert( $tr_term_name, $source_term->taxonomy, $target_language, $args );
			if ( is_wp_error( $tr_term ) ) {
				/* translators: %d is a term ID. */
				return new WP_Error( 'pll_translate_term_failed', sprintf( __( 'The term with ID %d could not be translated.', 'polylang-pro' ), (int) $entry['id'] ) );
			}
			$tr_term_id = (int) $tr_term['term_id'];
		}

		( new PLL_Translation_Term_Metas( $this->sync_term_metas, $entry['data'] ) )
			->translate(
				$source_term->term_id,
				$tr_term_id,
				$target_language,
				! $translation_exists
			);

		/** @var WP_Term $tr_term */
		$tr_term = get_term( $tr_term_id );

		/**
		 * Fires once a term has been translated.
		 *
		 * @since 3.7
		 *
		 * @param WP_Term      $source_term     The source term.
		 * @param WP_Term      $tr_term         The target term.
		 * @param PLL_Language $target_language The language to translate into.
		 * @param Translations $translations    The set of translations for the entry.
		 */
		do_action( 'pll_after_term_translation', $source_term, $tr_term, $target_language, $entry['data'] );

		/** This action is documented in polylang/src/crud-terms.php. */
		do_action( 'pll_save_term', $tr_term_id, $source_term->taxonomy, $this->model->term->get_translations( $tr_term_id ) ); // Triggers the term metas synchronization.

		return $tr_term_id;
	}

	/**
	 * Returns the translated term name if exists, the source name otherwise.
	 *
	 * @since 3.3
	 * @since 3.7 $translations parameter added.
	 *
	 * @param WP_Term      $source_term  The source term object.
	 * @param Translations $translations Translated data object.
	 * @return string The translated name.
	 */
	private function get_translated_term_name( WP_Term $source_term, Translations $translations ) {
		$translated = $translations->translate(
			$source_term->name,
			Context::to_string( array( Context::FIELD => PLL_Import_Export::TERM_NAME ) )
		);

		return ! empty( $translated ) ? $translated : $source_term->name;
	}

	/**
	 * Returns the translated term description if exists, the source description otherwise.
	 *
	 * @since 3.3
	 * @since 3.7 $translations parameter added.
	 *
	 * @param WP_Term      $source_term  The source term object.
	 * @param Translations $translations Translated data object.
	 * @return string The translated description.
	 */
	private function get_translated_term_description( WP_Term $source_term, Translations $translations ) {
		$translated = $translations->translate(
			$source_term->description,
			Context::to_string( array( Context::FIELD => PLL_Import_Export::TERM_DESCRIPTION ) )
		);

		return ! empty( $translated ) ? $translated : $source_term->description;
	}

	/**
	 * Assigns the parents to terms creating during the import.
	 *
	 * @since 3.3
	 * @since 3.7 Renamed from `translate_parents`.
	 *
	 * @param int[]        $ids             Array of source term ids.
	 * @param PLL_Language $target_language The target language.
	 * @return void
	 */
	public function assign_parents( array $ids, PLL_Language $target_language ) {
		// Get the terms with their parents (or 0).
		$terms = get_terms(
			array(
				'include'    => $ids,
				'hide_empty' => false,
				'fields'     => 'id=>parent',
			)
		);

		if ( ! is_array( $terms ) ) {
			// No terms with parents.
			return;
		}

		// ‘id=>parent’ returns an array of numeric strings, so let's cast it into int.
		$terms = array_map( 'intval', array_filter( $terms, 'is_numeric' ) );

		// Keep only the terms that have a parent.
		$terms = array_filter( $terms );

		if ( empty( $terms ) ) {
			// No terms with parents.
			return;
		}

		$tr_ids = array();
		foreach ( $terms as $child => $term_id ) {
			$tr_ids[ $child ] = $this->model->term->get( $child, $target_language->slug );
		}
		$tr_ids = array_filter( $tr_ids );

		if ( empty( $tr_ids ) ) {
			// No translations.
			return;
		}

		foreach ( $terms as $child => $term_id ) {
			if ( empty( $tr_ids[ $child ] ) ) {
				// Not translated.
				continue;
			}

			$tr_parent_term = $this->model->term->get( $term_id, $target_language->slug );
			if ( empty( $tr_parent_term ) ) {
				// The parent term is not translated.
				continue;
			}

			$tr_term_id = $this->model->term->get( $tr_ids[ $child ], $target_language->slug );
			if ( empty( $tr_term_id ) ) {
				continue;
			}

			$tr_term = get_term( $tr_term_id );
			if ( ! $tr_term instanceof WP_Term ) {
				continue;
			}

			// Set term parent for shared slugs.
			$this->model->term->update( $tr_term->term_id, array( 'parent' => $tr_parent_term ) );
		}
	}
}
