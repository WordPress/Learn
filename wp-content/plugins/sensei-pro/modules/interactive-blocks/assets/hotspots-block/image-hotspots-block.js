/**
 * WordPress dependencies
 */
import {
	InnerBlocks,
	useBlockProps,
	BlockControls,
} from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { ToolbarGroup, ToolbarButton } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import meta from './block.json';
import { example } from './example';
import { HotspotBlock } from './hotspot-block';
import { ImageHotspots } from './elements';
import { HotspotsImage } from './hotspots-image';
import { HotspotsAppender } from './hotspots-appender';
import { ReactComponent as icon } from '../icons/image-hotspots-block.svg';
import { CompletedStatus } from '../shared/supports-required/elements';

/**
 * Image hotspots block.
 */
export const ImageHotspotsBlock = {
	...meta,
	example,
	supports: {
		...meta.supports,
		sensei: {
			blockId: true,
			frontend: true,
			required: true,
			colors: [
				{
					name: '--marker-color',
					title: __( 'Marker', 'sensei-pro' ),
				},
			],
		},
	},
	name: 'sensei-pro/image-hotspots',
	title: __( 'Image Hotspots', 'sensei-pro' ),
	icon,
	description: __(
		'Add hotspots and tooltips with more information to any image.',
		'sensei-pro'
	),
	keywords: [
		__( 'sensei', 'sensei-pro' ),
		__( 'picture', 'sensei-pro' ),
		__( 'image map', 'sensei-pro' ),
		__( 'regions', 'sensei-pro' ),
		__( 'image tooltip', 'sensei-pro' ),
		__( 'image overlay', 'sensei-pro' ),
	],
	attributes: {
		image: {
			type: 'object',
			default: {},
		},
	},
	edit: function EditImageHotspots( props ) {
		const blockProps = useBlockProps();
		const hasImage = !! props.attributes.image?.url;

		const [ addingMarker, setAddingMarker ] = useState( false );

		useEffect( () => {
			if ( addingMarker && ! props.isSelected ) {
				setAddingMarker( false );
			}
		}, [ addingMarker, props.isSelected ] );

		const toggleAddingMarker = () => {
			setAddingMarker( ! addingMarker );
		};

		return (
			<ImageHotspots { ...blockProps }>
				{
					<BlockControls>
						<ToolbarGroup>
							<ToolbarButton
								onClick={ toggleAddingMarker }
								isActive={ addingMarker }
							>
								{ __( 'Add Hotspot', 'sensei-pro' ) }
							</ToolbarButton>
						</ToolbarGroup>
					</BlockControls>
				}
				<HotspotsImage { ...props } />
				{ hasImage && (
					<HotspotsAppender
						addingMarker={ addingMarker }
						clientId={ props.clientId }
					/>
				) }
				<InnerBlocks
					allowedBlocks={ [ HotspotBlock.name ] }
					template={ [] }
					renderAppender={ false }
				/>
				{ props.attributes.required && (
					<CompletedStatus
						className="sensei-lms-image-hotspots__completed-status"
						completed={ false }
						showTooltip={ false }
					/>
				) }
			</ImageHotspots>
		);
	},
	save: ( { attributes, children, blockProps } ) => {
		return (
			<ImageHotspots { ...blockProps }>
				<HotspotsImage.Image { ...attributes.image } />
				{ children }
			</ImageHotspots>
		);
	},
};
