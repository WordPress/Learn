/**
 * External dependencies
 */
import { noop } from 'lodash';

/**
 * WordPress dependencies
 */
import { useDispatch, useSelect } from '@wordpress/data';
import { useCallback, useEffect, useState } from '@wordpress/element';

/**
 * Synchronize height of the cover blocks in front and back of the card.
 *
 * @param {Object} props
 * @param {string} props.clientId Card block clientId.
 */
export const useSyncHeight = ( { clientId } ) => {
	const {
		__unstableMarkNextChangeAsNotPersistent: markNextChangeAsNotPersistent = noop,
		updateBlockAttributes,
	} = useDispatch( 'core/block-editor' );

	const coverBlocks = useSelect(
		( select ) => select( 'core/block-editor' ).getBlocks( clientId ),
		[]
	);

	const [ height, setHeight ] = useState( 300 );
	const [ front, back ] = coverBlocks;
	const [ frontHeight, backHeight ] = coverBlocks.map(
		( block ) => block?.attributes?.minHeight
	);

	const setBlockHeight = useCallback(
		( block, newHeight ) => {
			setHeight( newHeight );
			markNextChangeAsNotPersistent();
			updateBlockAttributes( block?.clientId, { minHeight: newHeight } );
		},
		[ setHeight, markNextChangeAsNotPersistent, updateBlockAttributes ]
	);

	useEffect( () => {
		if ( frontHeight !== height ) {
			setBlockHeight( back, frontHeight );
		}
		if ( backHeight !== height ) {
			setBlockHeight( front, backHeight );
		}
	}, [ height, frontHeight, backHeight, setBlockHeight ] );
};
