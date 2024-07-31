function init() {
	/**
	 * Enhance the accessibility of the sensei lesson actions.
	 *
	 * Because there are additional visible buttons, the extra screen reader text is removed.
	 */
	const footerElement = document.querySelector( '.sensei-lesson-footer' );
	const screenReaderText = footerElement.parentElement.querySelector( '.screen-reader-text' );
	if ( screenReaderText ) {
		screenReaderText.remove();
	}
}

document.addEventListener( 'DOMContentLoaded', init );
