/**
 * Internal dependencies
 */
import { createBemComponent } from '../shared/bem';

export const IMAGE_HOTSPOST_CLASS_NAME = 'sensei-lms-image-hotspots';

export const ImageHotspots = createBemComponent( {
	className: IMAGE_HOTSPOST_CLASS_NAME,
} );

ImageHotspots.Overlay = createBemComponent( {
	className: 'sensei-lms-image-hotspots__markers-overlay',
} );

/**
 * Image renderer.
 *
 * @param {Object} props
 * @param {string} props.url Image URL.
 * @param {string} props.alt Image Alt text.
 */
ImageHotspots.Image = ( { url, alt } ) => (
	<figure>
		<img
			alt={ alt }
			className="sensei-lms-image-hotspots__image"
			src={ url }
		/>
	</figure>
);
