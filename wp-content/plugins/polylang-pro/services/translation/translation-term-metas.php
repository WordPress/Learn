<?php
/**
 * @package Polylang-Pro
 */

/**
 * Class PLL_Translation_Term_Metas
 *
 * @since 3.3
 *
 * Translate term metas from a set of translation entries.
 */
class PLL_Translation_Term_Metas extends PLL_Translation_Metas {

	/**
	 * The PLL_Translation_Term_Metas constructor that allows to define the meta type.
	 *
	 * @since 3.3
	 *
	 * @param PLL_Sync_Term_Metas $sync_metas Object to manage copied term metas during import.
	 */
	public function __construct( PLL_Sync_Term_Metas $sync_metas ) {
		parent::__construct( $sync_metas );

		$this->meta_type = 'term';
		$this->context   = PLL_Import_Export::TERM_META;
	}
}
