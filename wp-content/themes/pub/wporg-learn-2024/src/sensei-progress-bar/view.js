function init() {
	/**
	 * Enhance the accessibility of the course progress bars.
	 *
	 * This function adds an `aria-labelledby` attribute to each progress bar
	 * to associate it with the label element above
	 */
	function enhanceCourseProgressAccessibility() {
		document.querySelectorAll( '.sensei-progress-bar__bar' ).forEach( ( progressBar, index ) => {
			const wrapper = progressBar.closest( '.sensei-block-wrapper' );

			if ( wrapper ) {
				const labelElement = wrapper.querySelector( '.sensei-progress-bar__label' );

				if ( labelElement ) {
					const id = `sensei-progress-bar__label-${ index }`;
					labelElement.id = id;
					progressBar.setAttribute( 'aria-labelledby', id );
				}
			}
		} );
	}

	enhanceCourseProgressAccessibility();
}

document.addEventListener( 'DOMContentLoaded', init );
