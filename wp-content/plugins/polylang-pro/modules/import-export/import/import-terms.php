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
	 * Handle translation of terms
	 *
	 * @var PLL_Translation_Term_Model
	 */
	private $translation_term_model;

	/**
	 * The success counter.
	 *
	 * @var int
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
	 * @param PLL_Translation_Term_Model $translation_term_model The PLL_Translation_Term_Model object.
	 */
	public function __construct( $translation_term_model ) {
		$this->translation_term_model = $translation_term_model;

		add_action( 'pll_after_term_import', array( $this, 'process_translated_terms' ), 10, 2 );
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
		$is_success = $this->translation_term_model->translate( $entry, $target_language );
		if ( $is_success ) {
			++$this->success;

			// Store the term ids during the import process.
			$this->term_ids[] = $entry['id'];
		}
	}

	/**
	 * Performs actions on imported terms.
	 * Translates terms parent.
	 *
	 * @since 3.3
	 *
	 * @param PLL_Language $target_language The targeted language for import.
	 * @param int[]        $term_ids        The imported term ids of the import.
	 * @return void
	 */
	public function process_translated_terms( $target_language, $term_ids ) {
		$term_ids = array_filter( array_map( 'absint', (array) $term_ids ) );
		if ( ! empty( $term_ids ) && $target_language instanceof PLL_Language ) {
			$this->translation_term_model->translate_parents( $term_ids, $target_language );
		}
	}

	/**
	 * Get update notices to display.
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
	 * Get warnings notices to display.
	 *
	 * @since 3.3
	 *
	 * @return WP_Error
	 */
	public function get_warning_notice() {
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
