/**
 * WordPress dependencies
 */
import { useDispatch, useSelect } from '@wordpress/data';
import { createBlock } from '@wordpress/blocks';
import { useEffect } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Automatically move the inner block to root.
 *
 * @param {string}   clientId         The block client id that contains the inner blocks.
 * @param {string[]} disallowedBlocks List of block names that should be moved to the root.
 */
const useInnerBlockProtection = ( clientId, disallowedBlocks = [] ) => {
	const innerBlocks = useSelect( ( select ) => {
		const parentBlocks = select( 'core/editor' ).getBlocksByClientId(
			clientId
		)[ 0 ];
		return parentBlocks.innerBlocks;
	} );

	const { createInfoNotice } = useDispatch( 'core/notices' );
	const {
		removeBlock,
		insertBlock,
		replaceInnerBlocks,
		__unstableMarkNextChangeAsNotPersistent: markNextChangeAsNotPersistent = () => {},
	} = useDispatch( 'core/block-editor' );

	const blocksToRemove = innerBlocks.filter( ( block ) =>
		disallowedBlocks.includes( block.name )
	);

	useEffect( () => {
		blocksToRemove.forEach( ( block ) => {
			removeBlock( block.clientId );

			insertBlock( createBlock( block.name ) );
			createInfoNotice(
				//
				sprintf(
					/* translators: %s replace with the block name  */
					__(
						"The Accordion section can't contain the block %s.",
						'sensei-pro'
					),
					block.name
				),
				{ type: 'snackbar' }
			);
		} );
	}, [
		blocksToRemove,
		clientId,
		createInfoNotice,
		disallowedBlocks,
		innerBlocks,
		insertBlock,
		markNextChangeAsNotPersistent,
		removeBlock,
		replaceInnerBlocks,
	] );
};

export default useInnerBlockProtection;
