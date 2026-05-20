<?php
/**
 * @package Polylang Pro
 */

/**
 * Interface for object translation model.
 */
interface PLL_Translation_Data_Model_Interface {
	/**
	 * Translates a piece of data into a given language.
	 *
	 * @since 3.6
	 * @since 3.7 Formerly named `PLL_Translation_Object_Model_Interface`.
	 *
	 * @param  array        $entry           Properties array of an entry.
	 * @param  PLL_Language $target_language A language to translate into.
	 * @return mixed|WP_Error Anything that fits the data model or a `WP_Error` on failure.
	 */
	public function translate( array $entry, PLL_Language $target_language );

	/**
	 * Performs actions after a translation process.
	 *
	 * @since 3.7
	 *
	 * @param int[]|string[] $ids             The entity ids to process after translation.
	 * @param PLL_Language   $target_language The target language.
	 * @return void
	 */
	public function do_after_process( array $ids, PLL_Language $target_language );
}
