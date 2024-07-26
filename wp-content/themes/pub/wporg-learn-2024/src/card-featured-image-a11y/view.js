function init() {
	/**
	 * Enhance the accessibility of the card course template.
	 *
	 * This function adds an `aria-hidden="true"` and `tabindex="-1"`
	 * to the post featured image block in the card course template.
	 */
	function enhanceCardCourseAccessibility() {
		document
			.querySelectorAll( '.wporg-learn-card-grid figure.wp-block-post-featured-image > a' )
			.forEach( ( featuredImgLink ) => {
				featuredImgLink.setAttribute( 'aria-hidden', 'true' );
				featuredImgLink.setAttribute( 'tabindex', '-1' );

				const featuredImg = featuredImgLink.querySelector( 'img' );
				if ( featuredImg ) {
					featuredImg.setAttribute( 'role', 'presentation' );
					featuredImg.setAttribute( 'alt', '' );
				}
			} );
	}

	enhanceCardCourseAccessibility();
}

document.addEventListener( 'DOMContentLoaded', init );
