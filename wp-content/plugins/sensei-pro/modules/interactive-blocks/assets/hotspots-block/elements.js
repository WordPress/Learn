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
