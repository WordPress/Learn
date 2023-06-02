<?php
/**
 * File containing the class \Sensei_Pro_Glossary\Glossary_Markup_Generator.
 *
 * @package sensei-pro-glossary
 * @since   1.11.0
 */

namespace Sensei_Pro_Glossary;

/**
 * The class responsible for generating the glossary markup.
 *
 * @internal
 */
class Glossary_Markup_Generator {
	/**
	 * Get the HTML markup that wraps the phrase.
	 *
	 * @param string         $id An ID used for the HTML element.
	 * @param string         $phrase The phrase as seen in the content.
	 * @param Glossary_Entry $entry The glossary entry.
	 *
	 * @return string
	 */
	public function get_phrase_markup( string $id, string $phrase, Glossary_Entry $entry ): string {
		$definition = apply_filters( 'the_content', $entry->get_definition() ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Intended.

		return <<<MARKUP
<span
	class="sensei-glossary-phrase"
	data-sensei-glossary-phrase-id="$id"
	aria-describedby="$id-definition"
	role="term"
>
	$phrase
	<template id="$id">
		<div class="sensei-glossary-tooltip__inner">
			<h4 class="sensei-glossary-tooltip__title">{$entry->get_phrase()}</h4>
			<div id="$id-definition" class="sensei-glossary-tooltip__content" role="definition">{$definition}</div>
		</div>
	</template>
</span>
MARKUP;
	}
}
