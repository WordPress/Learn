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
	 * Returns the meta type.
	 *
	 * @since 3.7
	 *
	 * @return string Meta type. Typically 'post' or 'term'.
	 */
	protected function get_type(): string {
		return 'post';
	}

	/**
	 * Returns the context to translate entry.
	 *
	 * @since 3.7
	 *
	 * @return string The context.
	 */
	protected function get_context(): string {
		return PLL_Import_Export::POST_META;
	}
}
