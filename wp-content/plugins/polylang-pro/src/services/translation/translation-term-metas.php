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
	 * Returns the meta type.
	 *
	 * @since 3.7
	 *
	 * @return string Meta type. Typically 'post' or 'term'.
	 */
	protected function get_type(): string {
		return 'term';
	}

	/**
	 * Returns the context to translate entry.
	 *
	 * @since 3.7
	 *
	 * @return string The context.
	 */
	protected function get_context(): string {
		return PLL_Import_Export::TERM_META;
	}
}
