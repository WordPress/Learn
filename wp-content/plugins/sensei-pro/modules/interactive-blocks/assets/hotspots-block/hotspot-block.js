/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { useCallback, useEffect, useRef } from '@wordpress/element';
import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';
import { useDispatch, useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import meta from './block.json';
import { HotspotMarker } from './hotspot-marker';
import { HotSpotTooltip } from './hotspot-tooltip';
import { useHotspotDragging } from './use-hotspot-dragging';
import { ReactComponent as icon } from '../icons/hotspot-block.svg';

/**
 * An inner Hotspot block.
 */
export const HotspotBlock = {
	...meta,
	name: 'sensei-pro/image-hotspots-hotspot',
	parent: [ 'sensei-pro/image-hotspots' ],
	attributes: {
		x: {
			type: 'integer',
		},
		y: {
			type: 'integer',
		},
		draft: {
			type: 'boolean',
			default: true,
		},
	},
	icon,
	title: __( 'Hotspot', 'sensei-pro' ),
	description: __(
		'A marker for a tooltip that is placed on the image.',
		'sensei-pro'
	),
	supports: {
		color: true,
		sensei: {
			blockId: true,
			frontend: true,
		},
	},
	edit: function EditHotspot( { clientId, attributes, setAttributes } ) {
		const blockProps = useBlockProps();
		const { selectBlock } = useDispatch( 'core/block-editor' );
		const onPositionChange = useCallback(
			( { x, y } ) => {
				setAttributes( { x, y } );
			},
			[ setAttributes ]
		);

		const hotspotRef = useRef();
		const { dragCoords, draggableProps } = useHotspotDragging( {
			clientId,
			hotspotRef,
			onPositionChange,
		} );
		const coordinates = {
			x: attributes.x,
			y: attributes.y,
			...dragCoords,
		};
		const innerBlocks = useSelect( ( select ) =>
			select( 'core/block-editor' ).getBlocks( clientId )
		);

		const blockIsDraft = () =>
			innerBlocks.length === 0 ||
			innerBlocks[ 0 ]?.attributes?.content === '';
		useEffect( () => {
			setAttributes( {
				draft: blockIsDraft(),
			} );
		}, [ innerBlocks ] );

		const draftClass = classnames( {
			'is-editor-draft': blockIsDraft(),
		} );
		const ALLOWED_BLOCKS = [
			'core/paragraph',
			'core/image',
			'core/video',
			'core/audio',
			'core/heading',
			'core/cover',
			'core/list',
			'core/embed',
		];

		return (
			<>
				<HotspotMarker
					{ ...coordinates }
					onClick={ () => selectBlock( clientId ) }
					className={ draftClass }
					ref={ hotspotRef }
					{ ...draggableProps }
				/>
				<HotSpotTooltip { ...blockProps } attributes={ attributes }>
					<InnerBlocks
						template={ [
							[
								'core/paragraph',
								{
									placeholder: __(
										'Add hotspot description. Type / to choose a block.',
										'sensei-lms'
									),
								},
							],
						] }
						allowedBlocks={ ALLOWED_BLOCKS }
					/>
				</HotSpotTooltip>
			</>
		);
	},
	save: ( { attributes, children, blockProps } ) => {
		const isDraft = {
			'is-draft': attributes.draft,
		};
		const tooltipDraftClass = classnames( blockProps?.className, isDraft );
		const markerDraftClass = classnames( isDraft );
		return (
			<div { ...blockProps }>
				<HotspotMarker
					x={ attributes.x }
					y={ attributes.y }
					className={ markerDraftClass }
				/>
				<HotSpotTooltip
					attributes={ attributes }
					className={ tooltipDraftClass }
				>
					{ children }
				</HotSpotTooltip>
			</div>
		);
	},
};
