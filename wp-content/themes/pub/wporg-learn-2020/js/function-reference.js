/* global jQuery, Prism, wporgFunctionReferenceI18n */

/**
 * function-reference.js
 *
 * Handles all interactivity for code blocks.
 *
 * Note: This was forked from the wporg-developer theme.
 */

// eslint-disable-next-line id-length -- $ OK.
jQuery( function ( $ ) {
	// 22.5px (line height) * 15 for 15 lines + 15px top padding + 10px extra.
	// The extra 10px added to partially show next line so it's clear there is more.
	const MIN_HEIGHT = 22.5 * 15 + 15 + 10;

	function collapseCodeBlock( $element, $button ) {
		$button.text( wporgFunctionReferenceI18n.expand );
		$button.attr( 'aria-expanded', 'false' );
		// This uses `css()` instead of `height()` to prevent jQuery from adding
		// in the padding. We want to add in just the top padding, since the
		// bottom is intentionally cut off.
		$element.css( { height: MIN_HEIGHT + 'px' } );
	}

	function expandCodeBlock( $element, $button ) {
		$button.text( wporgFunctionReferenceI18n.collapse );
		$button.attr( 'aria-expanded', 'true' );
		// { height: auto; } can't be used here or the transition effect won't work.
		$element.height( $element.data( 'height' ) );
	}

	// For each code block, add the copy button & expanding functionality.
	$( '.wp-block-code' ).each( function ( i, element ) {
		const $element = $( element );
		let timeoutId;

		const $copyButton = $( document.createElement( 'button' ) );
		$copyButton.text( wporgFunctionReferenceI18n.copy );
		$copyButton.on( 'click', function () {
			clearTimeout( timeoutId );
			const code = $element.find( 'code' ).text();
			if ( ! code ) {
				return;
			}

			// This returns a promise which will resolve if the copy suceeded,
			// and we can set the button text to tell the user it worked.
			// We don't do anything if it fails.
			window.navigator.clipboard.writeText( code ).then( function () {
				$copyButton.text( wporgFunctionReferenceI18n.copied );
				wp.a11y.speak( wporgFunctionReferenceI18n.copied );

				// After 5 seconds, reset the button text.
				timeoutId = setTimeout( function () {
					$copyButton.text( wporgFunctionReferenceI18n.copy );
				}, 5000 );
			} );
		} );

		const $container = $( document.createElement( 'div' ) );
		$container.addClass( 'wp-code-block-button-container' );

		$container.append( $copyButton );

		// Check code block height, and if it's larger, add in the collapse
		// button, and set it to be collapsed differently.
		const originalHeight = $element.height();
		if ( originalHeight > MIN_HEIGHT ) {
			$element.data( 'height', originalHeight );

			const $expandButton = $( document.createElement( 'button' ) );
			$expandButton.on( 'click', function () {
				if ( 'true' === $expandButton.attr( 'aria-expanded' ) ) {
					collapseCodeBlock( $element, $expandButton );
				} else {
					expandCodeBlock( $element, $expandButton );
				}
			} );

			collapseCodeBlock( $element, $expandButton );
			$container.append( $expandButton );
		}

		$element.before( $container );
	} );

	// Runs before the highlight parsing is run.
	// `env` is defined here: https://github.com/PrismJS/prism/blob/2815f699970eb8387d741e3ac886845ce5439afb/prism.js#L583-L588
	Prism.hooks.add( 'before-highlight', function ( env ) {
		// If the code starts with `<`, it's either already got an opening tag,
		// or it starts with HTML. Either way, we don't want to inject here.
		if ( 'php' === env.language && ! env.code.startsWith( '<' ) ) {
			env.code = '<? ' + env.code;
			env.hasAddedTag = true;
		}
	} );

	// Runs before `highlightedCode` is set to the `innerHTML` of the container.
	Prism.hooks.add( 'before-insert', function ( env ) {
		if ( env.hasAddedTag ) {
			env.highlightedCode = env.highlightedCode.replace(
				'<span class="token delimiter important">&lt;?</span> ',
				''
			);
		}
	} );
} );
