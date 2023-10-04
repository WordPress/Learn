<?php
/**
 * File containing the class \Sensei_Pro_Glossary\Glossary_Entry.
 *
 * @package sensei-pro-glossary
 * @since   1.11.0
 */

namespace Sensei_Pro_Glossary;

/**
 * Class for the glossary entry model.
 *
 * @internal
 */
class Glossary_Entry {
	/**
	 * The glossary phrase.
	 *
	 * @var string
	 */
	private $phrase;

	/**
	 * The definition of the glossary phrase.
	 *
	 * @var string
	 */
	private $definition;

	/**
	 * Class constructor.
	 *
	 * @internal
	 *
	 * @param string $phrase     The glossary phrase.
	 * @param string $definition The phrase definition.
	 */
	public function __construct( string $phrase, string $definition ) {
		$this->phrase     = $phrase;
		$this->definition = $definition;
	}

	/**
	 * Get the glossary phrase.
	 *
	 * @internal
	 *
	 * @return string
	 */
	public function get_phrase(): string {
		return $this->phrase;
	}

	/**
	 * Get the phrase definition.
	 *
	 * @internal
	 *
	 * @return string
	 */
	public function get_definition(): string {
		return $this->definition;
	}
}
