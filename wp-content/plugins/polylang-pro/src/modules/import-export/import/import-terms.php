<?php
/**
 * @package Polylang-Pro
 */

/**
 * A class to handle terms import.
 *
 * @since 3.3
 */
class PLL_Import_Terms implements PLL_Import_Object_Interface {
	/**
	 * Handles the translation of terms.
	 *
	 * @var PLL_Translation_Term_Model
	 */
	private $translation_model;

	/**
	 * The success counter.
	 *
	 * @var int|null
	 */
	protected $success;

	/**
	 * The imported source term ids.
	 *
	 * @var int[]
	 */
	protected $term_ids = array();

	/**
	 * Constructor.
	 *
	 * @since 3.3
	 *
	 * @param PLL_Translation_Term_Model $translation_model The object to handle translations.
	 */
	public function __construct( PLL_Translation_Term_Model $translation_model ) {
		$this->translation_model = $translation_model;
	}

	/**
	 * Handles the import of terms.
	 *
	 * @since 3.3
	 *
	 * @param array        $entry           The current entry to import.
	 * @param PLL_Language $target_language The targeted language for import.
	 */
	public function translate( $entry, $target_language ) {
		// Make sure `$this->success` is not `null`.
		$this->success = (int) $this->success;

		$result = $this->translation_model->translate( $entry, $target_language );
		if ( ! is_wp_error( $result ) ) {
			++$this->success;

			// Store the term ids during the import process.
			$this->term_ids[] = $entry['id'];
		}
	}

	/**
	 * Performs actions after an import process.
	 *
	 * @since 3.7
	 *
	 * @param int[]        $ids             The entity ids to process after import.
	 * @param PLL_Language $target_language The target language.
	 * @return void
	 */
	public function do_after_import_process( array $ids, PLL_Language $target_language ) {
		$this->translation_model->do_after_process( $ids, $target_language );
	}

	/**
	 * Returns update notices to display.
	 *
	 * @since 3.3
	 *
	 * @return WP_Error
	 */
	public function get_updated_notice() {
		if ( ! $this->success ) {
			return new WP_Error();
		}

		return new WP_Error(
			'pll_import_terms_success',
			sprintf(
				/* translators: %d is a number of terms translations */
				_n( '%d term translation updated.', '%d terms translations updated.', $this->success, 'polylang-pro' ),
				$this->success
			),
			'success'
		);
	}

	/**
	 * Returns warnings notices to display.
	 *
	 * @since 3.3
	 *
	 * @return WP_Error
	 */
	public function get_warning_notice() {
		if ( isset( $this->success ) && ! $this->success ) {
			return new WP_Error(
				'pll_import_terms_nothing_imported',
				__( 'No terms were translated. Please check that the original terms in your file match those on the site.', 'polylang-pro' ),
				'warning'
			);
		}

		return new WP_Error();
	}

	/**
	 * Returns the object type.
	 *
	 * @since 3.3
	 *
	 * @return string
	 */
	public function get_type() {
		return PLL_Import_Export::TYPE_TERM;
	}

	/**
	 * Returns the imported term ids.
	 *
	 * @since 3.3
	 *
	 * @return int[]
	 */
	public function get_imported_object_ids() {
		return $this->term_ids;
	}
}
