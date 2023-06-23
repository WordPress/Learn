/**
 * WordPress dependencies
 */
import { useCallback, useContext } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import { createBlock } from '@wordpress/blocks';
/**
 * Internal dependencies
 */
import SectionBlock from '../../section-block';
import ContentBlock from '../../content-block';
/**
 * External dependencies
 */
import { head } from 'lodash';
import { SectionContext } from '../section';

const useForceContentSelection = ( clientId ) => {
	const { toggleCurrentSection, isOpen } = useContext( SectionContext );

	const { selectBlock, insertBlock } = useDispatch( 'core/block-editor' );
	const { getBlockParentsByBlockName, getBlock } = useSelect(
		'core/block-editor'
	);

	const section = getBlock(
		head( getBlockParentsByBlockName( clientId, SectionBlock.name ) )
	);

	const content = section.innerBlocks?.find(
		( block ) => block.name === ContentBlock.name
	);

	const contentClientId = content.clientId;
	const blockToSelect = head( content?.innerBlocks )?.clientId;
	const canSelect = Boolean( blockToSelect );

	const selectContent = useCallback(
		( block ) => {
			if ( ! isOpen ) toggleCurrentSection();

			if ( canSelect ) return selectBlock( blockToSelect );

			insertBlock(
				createBlock( block.name, block.attributes ),
				0,
				contentClientId
			);
		},
		[
			blockToSelect,
			canSelect,
			contentClientId,
			insertBlock,
			isOpen,
			toggleCurrentSection,
			selectBlock,
		]
	);

	return selectContent;
};

export default useForceContentSelection;
