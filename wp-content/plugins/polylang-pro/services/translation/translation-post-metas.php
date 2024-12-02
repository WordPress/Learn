<?php
/**
 * @package Polylang-Pro
 */

/**
 * Class PLL_Translation_Post_Metas
 *
 * @since 3.3
 *
 * Translate post metas from a set of translation entries.
 */
class PLL_Translation_Post_Metas extends PLL_Translation_Metas {

	/**
	 * The PLL_Translation_Post_Metas constructor that allows to define the meta type.
	 *
	 * @since 3.3
	 *
	 * @param PLL_Sync_Post_Metas $sync_metas Object to manage copied post metas during import.
	 */
	public function __construct( PLL_Sync_Post_Metas $sync_metas ) {
		parent::__construct( $sync_metas );

		$this->meta_type = 'post';
		$this->context   = PLL_Import_Export::POST_META;
	}
}
