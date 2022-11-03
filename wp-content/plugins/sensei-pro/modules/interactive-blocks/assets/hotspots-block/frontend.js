/**
 * External dependencies
 */
import classnames from 'classnames';
import { useSelector } from 'react-redux';

/**
 * WordPress dependencies
 */
import {
	createContext,
	useCallback,
	useContext,
	useEffect,
	useState,
} from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import { registerBlockFrontend } from '../shared/block-frontend';
import { ImageHotspots } from './elements';
import { HotspotMarker } from './hotspot-marker';
import { HotSpotTooltip, Tooltip } from './hotspot-tooltip';
import { CompletedStatus } from '../shared/supports-required/elements';

const ImageHotspotsContext = createContext();

const ImageHotspotsRun = ( {
	blockProps,
	children,
	attributes,
	setAttributes,
} ) => {
	const { blockId } = attributes;
	const [ selected, setSelected ] = useState( null );
	useEffect( () => {
		const closeOnOutsideClick = ( { target } ) => {
			const isTooltipClick = target?.closest( `.${ Tooltip.bem() }` );

			if ( ! isTooltipClick ) {
				setSelected( null );
			}
		};
		document.body.addEventListener( 'click', closeOnOutsideClick );
		return () =>
			document.body.removeEventListener( 'click', closeOnOutsideClick );
	}, [ setSelected ] );

	const areAllHotspotsVisited = useCallback(
		( state ) => {
			const nonVisitedHotspots = Object.keys( state.attributes )
				.map( ( hotspotId ) => state.attributes[ hotspotId ] )
				.filter(
					( { imageHotspotsId, draft } ) =>
						imageHotspotsId === blockId && ! draft
				)
				.filter( ( { visited } ) => ! visited );

			return ! nonVisitedHotspots.length;
		},
		[ blockId ]
	);

	const allHotspotsVisited = useSelector( areAllHotspotsVisited );

	useEffect( () => {
		setAttributes( { completed: allHotspotsVisited } );
	}, [ allHotspotsVisited ] );

	return (
		<ImageHotspots { ...blockProps }>
			<ImageHotspotsContext.Provider
				value={ { selected, setSelected, imageHotspotsId: blockId } }
			>
				<ImageHotspots.Image { ...attributes.image } />
				{ children }
			</ImageHotspotsContext.Provider>
			{ attributes.required && (
				<CompletedStatus
					message={ __(
						'Required - Open all the hotspots to complete.',
						'sensei-pro'
					) }
					className="sensei-lms-image-hotspots__completed-status"
					completed={ !! attributes.completed }
				/>
			) }
		</ImageHotspots>
	);
};

const HotspotRun = ( { attributes, setAttributes, children, blockProps } ) => {
	const { blockId, visited = false } = attributes;
	const { selected, setSelected, imageHotspotsId } = useContext(
		ImageHotspotsContext
	);

	useEffect( () => {
		setAttributes( { imageHotspotsId } );
	}, [] );

	const isSelected = selected === blockId;
	const onClick = () => {
		if ( isSelected ) {
			setSelected( null );
		} else {
			setSelected( blockId );
			setAttributes( { visited: true } );
		}
	};

	const markerClasses = classnames( {
		'is-draft': attributes.draft,
		'is-visited': visited,
		'is-opened': isSelected,
	} );

	const tooltipClasses = classnames( blockProps?.className, {
		'is-draft': attributes.draft,
		'is-selected': isSelected,
	} );

	return (
		<>
			<HotspotMarker
				x={ attributes.x }
				y={ attributes.y }
				className={ markerClasses }
				onClick={ onClick }
			/>
			<HotSpotTooltip
				{ ...blockProps }
				attributes={ attributes }
				className={ tooltipClasses }
			>
				{ children }
			</HotSpotTooltip>
		</>
	);
};

registerBlockFrontend( {
	name: 'sensei-pro/image-hotspots',
	run: ImageHotspotsRun,
} );

registerBlockFrontend( {
	name: 'sensei-pro/image-hotspots-hotspot',
	run: HotspotRun,
} );

/**
 * Bit of a hack here: Video elements block in hotspot tooltips don't display because the element's style width is set to 0px.
 * Here we set it to 100% so it displays.
 */
domReady( () => {
	document
		.querySelectorAll(
			'.sensei-lms-image-hotspots__hotspot-tooltip .wp-block-video video'
		)
		.forEach( ( el ) => {
			if ( el.style.width === '0px' ) {
				el.style.width = '100%';
			}
		} );
} );
