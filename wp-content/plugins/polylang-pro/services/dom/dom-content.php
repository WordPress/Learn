<?php
/**
 * @package Polylang-Pro
 */

/**
 * Provides a few tools to manipulate content by using `DOMDocument`.
 *
 * @since 3.3
 */
class PLL_DOM_Content {

	/**
	 * The content to work with.
	 *
	 * @var string
	 */
	private $content;

	/**
	 * The site's charset.
	 *
	 * @var string
	 */
	private $charset;

	/**
	 * Placeholder used in {@see PLL_DOM_Content::replace_content()}.
	 *
	 * @var string
	 * @phpstan-var non-empty-string
	 */
	private $placeholder;

	/**
	 * Constructor.
	 *
	 * @since 3.3
	 *
	 * @param string $content The content to work with.
	 */
	public function __construct( $content ) {
		$this->content     = $content;
		$this->charset     = get_bloginfo( 'charset' );
		$this->charset     = is_string( $this->charset ) && ! empty( $this->charset ) ? $this->charset : 'UTF-8';
		$this->placeholder = substr( uniqid( 'pll_' ), 0, 10 );
		$this->placeholder = "@@{$this->placeholder}-%d@@";
	}

	/**
	 * Extracts strings from content, given a list of parsing rules.
	 *
	 * @since 3.3
	 *
	 * @uses DOMXPath
	 *
	 * @param string[] $rules Parsing rules. Ex: `[ '//figure/img/@alt' ]`.
	 * @return string[] Path to matching nodes as array keys, extracted strings as array values.
	 *                  Ex: `[ '/figure/img[1]/@alt' => 'Image alt text.' ]`.
	 *
	 * @phpstan-return array<string,string>
	 */
	public function get_strings( array $rules ) {
		$matched_parts = array();
		$document      = PLL_DOM_Document::from_html( $this->content );
		$xpath         = new DOMXPath( $document );

		foreach ( $rules as $parsing_rule ) {
			$node_list = $xpath->query( $parsing_rule );

			if ( empty( $node_list ) ) {
				// Error.
				continue;
			}

			foreach ( $node_list as $node ) {
				if ( ! $node instanceof DOMNode ) {
					continue;
				}

				$node_path = $node->getNodePath();

				if ( ! is_string( $node_path ) ) {
					// Trouble.
					continue;
				}

				$node_content = $this->get_node_content( $node );

				if ( '' === $node_content ) {
					continue;
				}

				$node_path = preg_replace( '@/text\(\)$@', '', $node_path );

				$matched_parts[ $node_path ] = $node_content;
			}
		}

		return $matched_parts;
	}

	/**
	 * Replaces strings in the content, given a list of parsing rules.
	 *
	 * @since 3.3
	 *
	 * @uses DOMXPath
	 *
	 * @param string[] $new_strings Path to matching nodes as array keys, new strings as array values.
	 *                              Ex: `[ '/figure/img[1]/@alt' => 'New image alt text.' ]`.
	 * @return string Content with replaced strings.
	 *
	 * @phpstan-param array<string,string> $new_strings
	 */
	public function replace_content( array $new_strings ) {
		$document    = PLL_DOM_Document::from_html( $this->content );
		$xpath       = new DOMXPath( $document );
		$node_values = array();
		$incr        = 0;

		foreach ( $new_strings as $node_path => $new_string ) {
			$node_list = $xpath->query( $node_path );

			if ( ! $node_list instanceof DOMNodeList ) {
				// Error.
				continue;
			}

			// Each node path corresponds to only one node: get the first node then.
			$node = $this->get_first_dom_node( $node_list );

			if ( ! $node instanceof DOMNode ) {
				continue;
			}

			$node_placeholder                 = sprintf( $this->placeholder, ++$incr );
			$node_values[ $node_placeholder ] = html_entity_decode( $new_string, ENT_QUOTES, $this->charset );

			if ( $node instanceof DOMAttr ) {
				/**
				 * Tag attribute.
				 * Escape the value and insert the placeholder.
				 */
				$node_values[ $node_placeholder ] = esc_attr( $node_values[ $node_placeholder ] );
				$node->nodeValue                  = $node_placeholder;
				continue;
			}

			/**
			 * Tag content.
			 */
			if ( ! $node->ownerDocument instanceof DOMDocument ) {
				// Trouble.
				continue;
			}

			// 1- Remove all child nodes.
			while ( $node->hasChildNodes() ) {
				if ( ! empty( $node->firstChild ) ) {
					$node->removeChild( $node->firstChild );
				}
			}

			// 2- Insert a placeholder node.
			$node_values[ $node_placeholder ] = wp_kses_post( $node_values[ $node_placeholder ] );
			$node->appendChild( $node->ownerDocument->createTextNode( $node_placeholder ) );
		}

		$content = $document->saveHTML();

		if ( is_string( $content ) && '' !== trim( $content ) ) {
			// Decode entities, then put back the translated texts.
			$this->content = html_entity_decode( $content, ENT_QUOTES, $this->charset );
			$this->content = str_replace( array_keys( $node_values ), $node_values, $this->content );
		}

		return $this->content;
	}

	/**
	 * Returns the first `DOMNode` element from a `DOMNodeList`.
	 *
	 * @since 3.3
	 *
	 * @param DOMNodeList $node_list A `DOMNodeList` element.
	 * @return DOMNode|null A `DOMNode` element, or null if no elements have been found.
	 */
	private function get_first_dom_node( DOMNodeList $node_list ) {
		foreach ( $node_list as $node ) {
			if ( $node instanceof DOMNode ) {
				return $node;
			}
		}

		return null;
	}

	/**
	 * Returns a node's content.
	 * If the node is an attribute, the attribute's value is returned.
	 * If the node is a tag, the tag's HTML is returned.
	 *
	 * @since 3.3
	 *
	 * @param DOMNode $node A node.
	 * @return string
	 */
	private function get_node_content( DOMNode $node ) {
		if ( $node instanceof DOMAttr || ! $node->hasChildNodes() ) {
			// Tag attribute.
			if ( ! is_string( $node->nodeValue ) ) {
				return '';
			}

			return trim( html_entity_decode( $node->nodeValue, ENT_QUOTES, $this->charset ) );
		}

		// Tag content.
		if ( ! $node->ownerDocument instanceof DOMDocument ) {
			// Trouble.
			return '';
		}

		$content = '';

		foreach ( iterator_to_array( $node->childNodes ) as $node ) {
			if ( ! $node instanceof DOMNode || ! $node->ownerDocument instanceof DOMDocument ) {
				// Don't return partial content: if there is at least 1 error, return an empty content.
				return '';
			}

			$node_content = $node->ownerDocument->saveHTML( $node );

			if ( ! is_string( $node_content ) ) {
				// Don't return partial content: if there is at least 1 error, return an empty content.
				return '';
			}

			$content .= $node_content;
		}

		return trim( html_entity_decode( $content, ENT_QUOTES, $this->charset ) );
	}
}
