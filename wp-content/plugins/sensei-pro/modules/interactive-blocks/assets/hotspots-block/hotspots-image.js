/**
 * WordPress dependencies
 */
import {
	BlockControls,
	InspectorControls,
	MediaPlaceholder,
	MediaReplaceFlow,
} from '@wordpress/block-editor';
import {
	ExternalLink,
	PanelBody,
	TextareaControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { ImageHotspots } from './elements';

const ALLOWED_MEDIA_TYPES = [ 'image' ];

/**
 * Image component for the Image Hotspots block.
 *
 * @param {Object}   props
 * @param {Object}   props.attributes
 * @param {Function} props.setAttributes
 */
export const HotspotsImage = ( { attributes, setAttributes } ) => {
	const { id, url, alt } = attributes.image ?? {};

	const onSelectMedia = ( media ) => {
		setAttributes( {
			image: { ...attributes.image, id: media.id, url: media.url },
		} );
	};

	return (
		<>
			{ ! url && (
				<MediaPlaceholder
					onSelect={ onSelectMedia }
					allowedTypes={ ALLOWED_MEDIA_TYPES }
					multiple={ false }
					value={ { id, src: url } }
					labels={ { title: __( 'Image Hotspots', 'sensei-pro' ) } }
				/>
			) }
			{ url && <ImageHotspots.Image { ...attributes.image } /> }
			<BlockControls group="other">
				<MediaReplaceFlow
					mediaId={ id }
					mediaURL={ url }
					allowedTypes={ ALLOWED_MEDIA_TYPES }
					accept="image/*"
					onSelect={ onSelectMedia }
					name={ ! url ? __( 'Add Image' ) : __( 'Replace Image' ) }
				/>
			</BlockControls>
			<InspectorControls>
				<PanelBody title={ __( 'Image Settings' ) }>
					<TextareaControl
						label={ __( 'Alt Text (Alternative Text)' ) }
						value={ alt }
						onChange={ ( newValue ) =>
							setAttributes( {
								image: { ...attributes.image, alt: newValue },
							} )
						}
						help={
							<>
								<ExternalLink href="https://www.w3.org/WAI/tutorials/images/decision-tree">
									{ __(
										'Describe the purpose of the image'
									) }
								</ExternalLink>
							</>
						}
					/>
				</PanelBody>
			</InspectorControls>
		</>
	);
};
