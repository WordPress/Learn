<?php
/**
 * @package Polylang-Pro
 *
 * @since 3.1
 */

/**
 * Iterator for DOM nodes.
 *
 * @see https://www.php.net/manual/en/class.recursiveiterator.php
 *
 * @since 3.1
 * @since 3.6 Renamed from `PLL_Import_Xliff_Iterator` to `PLL_DOM_Nodes_Iterator`.
 */
class PLL_DOM_Nodes_Iterator implements RecursiveIterator {
	/**
	 * The nodes list.
	 *
	 * @var DOMNodeList.
	 */
	private $nodes;

	/**
	 * The offset.
	 *
	 * @var int.
	 */
	private $offset = 0;

	/**
	 * Constructor.
	 *
	 * @since 3.1
	 *
	 * @param DOMNodeList $nodes Nodes.
	 */
	public function __construct( DOMNodeList $nodes ) {
		$this->nodes = $nodes;
	}

	/**
	 * Rewind the Iterator to the first element.
	 *
	 * @since 3.1
	 *
	 * @return void
	 */
	#[\ReturnTypeWillChange]
	public function rewind() {
		$this->offset = 0;
	}

	/**
	 * Returns the current element.
	 *
	 * @since 3.1
	 *
	 * @return DOMNode|null
	 */
	#[\ReturnTypeWillChange]
	public function current() {
		return $this->nodes->item( $this->offset );
	}

	/**
	 * Returns the key of the current element.
	 * Issues `E_NOTICE` on failure.
	 *
	 * @since 3.1
	 *
	 * @return string|null Returns anything on success, or null on failure.
	 */
	#[\ReturnTypeWillChange]
	public function key() {
		if ( $this->current() ) {
			return $this->current()->nodeName;
		}
		return null;
	}

	/**
	 * Moves forward to next element.
	 *
	 * @since 3.1
	 *
	 * @return void
	 */
	#[\ReturnTypeWillChange]
	public function next() {
		++$this->offset;
	}

	/**
	 * Checks if current position is valid.
	 *
	 * @since 3.1
	 *
	 * @return bool
	 */
	public function valid(): bool {
		return $this->offset < $this->nodes->length;
	}

	/**
	 * Returns if an iterator can be created for the current entry.
	 *
	 * @since 3.1
	 *
	 * @return bool Returns `true` if the current entry can be iterated over, otherwise returns `false`.
	 */
	public function hasChildren(): bool {
		return isset( $this->current()->childNodes->length ) && $this->current()->childNodes->length > 0;
	}

	/**
	 * Returns an iterator for the current entry.
	 *
	 * @since 3.1
	 *
	 * @return RecursiveIterator|null Returns an iterator for the current entry if it exists, or null otherwise.
	 */
	#[\ReturnTypeWillChange]
	public function getChildren() {
		if ( $this->current() ) {
			return new self( $this->current()->childNodes );
		}
		return null;
	}
}
