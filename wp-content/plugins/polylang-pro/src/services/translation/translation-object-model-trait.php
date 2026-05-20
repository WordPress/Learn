<?php
/**
 * @package Polylang Pro
 */

/**
 * Trait for translatable objects models.
 *
 * @since 3.7
 */
trait PLL_Translation_Object_Model_Trait {
	/**
	 * Assigns parents after the translation.
	 *
	 * @since 3.7
	 *
	 * @param int[]        $ids             Array of source object ids.
	 * @param PLL_Language $target_language The target language.
	 * @return void
	 */
	public function do_after_process( array $ids, PLL_Language $target_language ) {
		$ids = array_filter( array_map( 'absint', $ids ) );
		$ids = array_unique( $ids, SORT_NUMERIC );

		if ( empty( $ids ) ) {
			return;
		}

		$this->assign_parents( $ids, $target_language );
	}
}
