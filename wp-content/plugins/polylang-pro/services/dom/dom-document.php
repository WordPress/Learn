<?php
/**
 * @package Polylang-Pro
 */

/**
 * Class PLL_DOM_Document
 *
 * Extends the PHP's {@see DOMDocument} to include safe instantiation through its factory function.
 * Adds internal error management for each instance.
 *
 * @since 3.1
 */
class PLL_DOM_Document extends DOMDocument {

	/**
	 * Store the errors that happenned during the loading process.
	 *
	 * @since 3.1
	 * @var array
	 */
	private $errors = array();

	/**
	 * Creates a PLL_DOM_Document instance from a XML string.
	 *
	 * @since 3.1
	 * @since 3.3 Added parameter $flags.
	 *
	 * @param string $xml      A XML valid string.
	 * @param string $version  Optional. XML version to use. Default is '1.0'.
	 * @param string $encoding Optional. Encoding to use. Default is 'UTF-8'.
	 * @param int    $flags    Optional. A series of libxml flags to parameterize the loading. Default is 0.
	 *                         {@link https://www.php.net/manual/en/libxml.constants.php}.
	 * @return PLL_DOM_Document
	 */
	public static function from_xml( $xml, $version = '1.0', $encoding = 'UTF-8', $flags = 0 ) {
		$document = new self( $version, $encoding );
		$document->preserveWhiteSpace = false;
		$document->formatOutput       = true;

		return self::from_string( $xml, $document, array( $document, 'loadXML' ), $flags );
	}

	/**
	 * Creates a PLL_DOM_Document instance from a HTML string.
	 *
	 * @since 3.1
	 * @since 3.3 Added parameters $version, $encoding, and $flags.
	 * @since 3.3 Doesn't format the output anymore.
	 * @since 3.3 Doesn't add the `<html>` and `<body>` tags by default anymore.
	 *
	 * Doctype declaration is disallowed for security reasons (XEE vulnerability).
	 *
	 * @param string $html     A HTML valid string.
	 * @param string $version  Optional. XML version to use. Default is '1.0'.
	 * @param string $encoding Optional. Encoding to use. Default is 'UTF-8'.
	 * @param int    $flags    Optional. A series of libxml flags to parameterize the loading.
	 *                         Default is `LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD`.
	 *                         {@link https://www.php.net/manual/en/libxml.constants.php}.
	 * @return PLL_DOM_Document
	 */
	public static function from_html( $html, $version = '1.0', $encoding = 'UTF-8', $flags = 0 ) {
		$document = new self( $version, $encoding );
		$document->strictErrorChecking = false;

		/*
		 * Hack to enforce that the string will be processed with the right encoding by DOMDocument.
		 * The added processing instruction is then removed by contains_not_allowed_node().
		 */
		$html = '<?xml encoding="' . $encoding . '">' . $html;

		$flags = ! empty( $flags ) ? $flags : LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD;

		$document = self::from_string( $html, $document, array( $document, 'loadHTML' ), $flags );
		$document->encoding = $encoding; // Enforce encoding, as it is not set by DOMDocument.

		return $document;
	}

	/**
	 * Factory function to safely generate DOMDocument from strings.
	 *
	 * @since 3.1
	 *
	 * Entity loading is disabled to prevent External Entity Injections {@link https://phpsecurity.readthedocs.io/en/latest/Injection-Attacks.html#xml-external-entity-injection}.
	 *
	 * @param string           $string   A XML content to load.
	 * @param PLL_DOM_Document $document A document parameterized to load the content into.
	 * @param callable         $function Method name which will handle the loading.
	 * @param int              $flags    A series of libxml flags to parameterize the loading. {@link https://www.php.net/manual/en/libxml.constants.php}.
	 * @return PLL_DOM_Document
	 */
	private static function from_string( $string, $document, $function, $flags = 0 ) {
		if ( ! empty( $string ) ) {
			// libxml2 version 2.9.0 and superior doesn't load external entities by default. libxml_disable_entity_loader() is deprecated since PHP 8.0.0 .
			$internal_errors = libxml_use_internal_errors( true );
			libxml_clear_errors();
			if ( ! defined( 'LIBXML_DOTTED_VERSION' ) || version_compare( LIBXML_DOTTED_VERSION, '2.9.0', '<' ) ) {
				$entity_loader = libxml_disable_entity_loader( true ); // phpcs:ignore Generic.PHP.DeprecatedFunctions.Deprecated
				$document = self::safe_load_string( $string, $document, $function, $flags );
				libxml_disable_entity_loader( $entity_loader ); // phpcs:ignore Generic.PHP.DeprecatedFunctions.Deprecated
			} else {
				$document = self::safe_load_string( $string, $document, $function, $flags );
			}
			libxml_clear_errors();
			libxml_use_internal_errors( $internal_errors );
		}

		return $document;
	}

	/**
	 * Loads the string into the given document, returns the document if it's safe, or return an empty document with errors.
	 *
	 * @since 3.1
	 *
	 * @param string           $string   A string to be loaded and parsed as the document.
	 * @param PLL_DOM_Document $document A configured instance of PLL_DOM_Document to load the string into.
	 * @param callable         $function Name of the loading method to use.
	 * @param int              $flags    A series of libxml flags to parameterize the loading. {@link https://www.php.net/manual/en/libxml.constants.php}.
	 * @return PLL_DOM_Document
	 */
	private static function safe_load_string( $string, $document, $function, $flags = 0 ) {
		call_user_func( $function, $string, LIBXML_NONET | $flags );
		if ( $document->contains_not_allowed_node() ) {
			$document = new self();
		}
		$document->errors = array_merge( $document->errors, libxml_get_errors() );

		return $document;
	}

	/**
	 * Verifies that the document contains only nodes of allowed types.
	 *
	 * @since 3.1
	 *
	 * @see https://www.php.net/manual/en/dom.constants.php.
	 *
	 * @return bool
	 */
	public function contains_not_allowed_node() {
		foreach ( $this->childNodes as $node ) {
			if ( ! $node instanceof DOMNode || ! in_array(
				$node->nodeType,
				array(
					XML_DOCUMENT_NODE,
					XML_ELEMENT_NODE,
					XML_ATTRIBUTE_NODE,
					XML_TEXT_NODE,
					XML_COMMENT_NODE,
					XML_CDATA_SECTION_NODE,
					XML_PI_NODE,
				)
			) ) {
				return true;
			}

			if ( XML_PI_NODE === $node->nodeType ) {
				$this->removeChild( $node ); // Remove our hacky <?xml node.
			}
		}
		return false;
	}

	/**
	 * Returns the first level HTML nodes of the document.
	 *
	 * @since 3.1
	 *
	 * Note: DOMDocument automatically wraps the loaded nodes in a <body> element.
	 *
	 * @return DOMNodeList
	 */
	public function get_first_level_html_nodes() {
		$body = $this->getElementsByTagName( 'body' )->item( 0 );

		return null !== $body ? $body->childNodes : new DOMNodeList();
	}

	/**
	 * Whether the document contains errors or not
	 *
	 * @since 3.1
	 *
	 * @return bool
	 */
	public function has_errors() {
		return ! empty( $this->errors );
	}

	/**
	 * Returns the document's errors.
	 *
	 * @since 3.3
	 *
	 * @return LibXMLError[]
	 */
	public function get_errors() {
		return $this->errors;
	}
}
