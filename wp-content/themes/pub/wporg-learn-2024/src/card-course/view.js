function init() {
	/**
	 * Enhance the accessibility of the card course template.
	 *
	 * This function adds an `aria-hidden="true"` and `tabindex="-1"`
	 * to the post featured image block in the card course template.
	 */
	function enhanceCardCourseAccessibility() {
		document
			.querySelectorAll( '.wporg-learn-card-grid figure.wp-block-post-featured-image' )
			.forEach( ( featuredImg ) => {
				featuredImg.setAttribute( 'aria-hidden', 'true' );
				featuredImg.setAttribute( 'tabindex', '-1' );

				const childLink = featuredImg.querySelector( 'a' );
				if ( childLink ) {
					childLink.setAttribute( 'aria-hidden', 'true' );
					childLink.setAttribute( 'tabindex', '-1' );
				}
			} );
	}

	enhanceCardCourseAccessibility();
}

document.addEventListener( 'DOMContentLoaded', init );
