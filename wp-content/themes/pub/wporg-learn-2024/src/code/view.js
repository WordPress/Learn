/* global wporgCodeI18n */
/**
 * WordPress dependencies
 */
import { speak } from '@wordpress/a11y';

/**
 * Internal dependencies
 */
import './style.scss';

// Index for
let _instanceID = 0;

function init() {
	// 27px (line height) * 12.7 for 12 lines + 18px top padding.
	// The extra partial line is to show that there is more content.
	const MIN_HEIGHT = 27 * 12.7 + 18;

	function collapseCodeBlock( element, button ) {
		button.innerText = wporgCodeI18n.expand;
		button.setAttribute( 'aria-expanded', 'false' );
		element.style.height = MIN_HEIGHT + 'px';
	}

	function expandCodeBlock( element, button ) {
		button.innerText = wporgCodeI18n.collapse;
		button.setAttribute( 'aria-expanded', 'true' );
		// Add 5px to ensure the vertical scrollbar is not displayed.
		const height = parseInt( element.dataset.height, 10 ) + 5;
		element.style.height = height + 'px';
	}

	// Run over all code blocks that use the syntax highlighter.
	const codeBlocks = document.querySelectorAll( '.wp-block-code[class*=language]' );

	codeBlocks.forEach( function ( element ) {
		let timeoutId;

		// Create a unique ID for the `pre` element, which can be used for aria later.
		const instanceId = 'wporg-source-code-' + _instanceID++;
		element.id = instanceId;

		// Create the top-level container. This will contain the buttons & sits above the `pre`.
		const container = document.createElement( 'div' );
		container.classList.add( 'wp-code-block-button-container' );

		const buttonContainer = document.createElement( 'div' );
		buttonContainer.classList.add( 'wp-block-buttons' );

		const copyButtonBlock = document.createElement( 'div' );
		copyButtonBlock.classList.add( 'wp-block-button', 'is-style-outline', 'is-small' );

		const copyButton = document.createElement( 'button' );
		copyButton.classList.add( 'wp-block-button__link', 'wp-element-button' );
		copyButton.innerText = wporgCodeI18n.copy;

		copyButton.addEventListener( 'click', function ( event ) {
			event.preventDefault();
			clearTimeout( timeoutId );
			const code = element.querySelector( 'code' ).innerText;
			if ( ! code ) {
				return;
			}

			// This returns a promise which will resolve if the copy suceeded,
			// and we can set the button text to tell the user it worked.
			// We don't do anything if it fails.
			window.navigator.clipboard.writeText( code ).then( function () {
				copyButton.innerText = wporgCodeI18n.copied;
				speak( wporgCodeI18n.copied );

				// After 5 seconds, reset the button text.
				timeoutId = setTimeout( function () {
					copyButton.innerText = wporgCodeI18n.copy;
				}, 5000 );
			} );
		} );

		copyButtonBlock.append( copyButton );
		buttonContainer.append( copyButtonBlock );

		// Check code block height. If it's too tall, add in the collapse button,
		// and shrink down the `pre` to MIN_HEIGHT.
		const originalHeight = element.clientHeight;
		if ( originalHeight > MIN_HEIGHT ) {
			element.dataset.height = originalHeight;

			const expandButtonBlock = document.createElement( 'div' );
			expandButtonBlock.classList.add( 'wp-block-button', 'is-style-outline', 'is-small' );

			const expandButton = document.createElement( 'button' );
			expandButton.classList.add( 'wp-block-button__link', 'wp-element-button' );
			expandButton.setAttribute( 'aria-controls', instanceId );
			expandButton.innerText = wporgCodeI18n.expand;

			expandButton.addEventListener( 'click', function ( event ) {
				event.preventDefault();
				if ( 'true' === expandButton.getAttribute( 'aria-expanded' ) ) {
					collapseCodeBlock( element, expandButton );
				} else {
					expandCodeBlock( element, expandButton );
				}
			} );

			collapseCodeBlock( element, expandButton );

			expandButtonBlock.append( expandButton );
			buttonContainer.append( expandButtonBlock );
		}

		container.append( buttonContainer );

		const wrapper = document.createElement( 'div' );
		wrapper.classList.add( 'wporg-code-block' );

		element.replaceWith( wrapper );
		wrapper.append( container, element );
	} );
}

document.addEventListener( 'DOMContentLoaded', init );
