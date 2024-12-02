<?php
/**
 * @package Polylang-Pro
 */

use WP_Syntex\Polylang_Pro\Modules\Import_Export\Services\Context;

/**
 * Class PLL_Translation_Walker_Classic
 *
 * @since 3.3
 *
 * Applies a callback over an HTML formatted string.
 */
class PLL_Translation_Walker_Classic implements PLL_Translation_Walker_Interface {

	/**
	 * Original content.
	 *
	 * @var string
	 */
	private $content;

	/**
	 * Callback to execute on translatable content.
	 *
	 * @var callable
	 */
	private $callback;

	/**
	 * List of contents that are not translatable, like placeholders.
	 *
	 * @var string[]
	 */
	private $non_translatable_contents;

	/**
	 * PLL_Content_Walker_Classic constructor.
	 *
	 * @since 3.3
	 *
	 * @param string   $content                   Classic editor HTML content.
	 * @param string[] $non_translatable_contents List of contents that are not translatable, like placeholders.
	 */
	public function __construct( $content, array $non_translatable_contents = array() ) {
		$this->non_translatable_contents = $non_translatable_contents;
		$this->content                   = $content;
	}

	/**
	 * Applies the defined callback on the content, and then returns a transformed content.
	 *
	 * @since 3.3
	 *
	 * @param callable $callback A transformation to apply to the content, whether it is for export or import.
	 * @return string
	 */
	public function walk( $callback ) {
		$this->callback = $callback;

		return $this->apply( $this->content );
	}

	/**
	 * Applies a callback on a given post content, whether it is to add a translation entry or translate it.
	 *
	 * @since 3.3
	 *
	 * @param string $content A post content to apply a callback onto.
	 * @return string The processed content.
	 */
	private function apply( $content ) {
		$translatable_content = str_replace( $this->non_translatable_contents, '', $content );
		if ( '' === str_replace( "\n", '', wp_strip_all_tags( $translatable_content ) ) ) {
			return $content;
		}

		$args  = array(
			'singular' => $content,
			'context'  => Context::to_string(
				array(
					Context::FIELD => PLL_Import_Export::POST_CONTENT,
				)
			),
		);
		$entry = new Translation_Entry( $args );

		$return = call_user_func_array( $this->callback, array( &$entry ) );

		if ( $return instanceof Translation_Entry ) {
			return $this->maybe_translate_entry( $return );
		}

		return $content;
	}

	/**
	 * Checks if the translation entry exists and return it, otherwise return the source text.
	 *
	 * @since 3.3
	 *
	 * @param Translation_Entry $entry A translation entry parsed from a translation document.
	 * @return string The translated string.
	 */
	private function maybe_translate_entry( $entry ) {
		return isset( $entry->translations[0] ) ? $entry->translations[0] : $entry->singular;
	}
}
