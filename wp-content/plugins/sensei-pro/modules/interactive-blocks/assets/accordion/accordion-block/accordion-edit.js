/**
 * WordPress dependencies
 */
import {
	ButtonBlockAppender,
	useBlockProps,
	useInnerBlocksProps,
} from '@wordpress/block-editor';
import { useCallback } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { Accordion } from '../elements/accordion';
import Settings from './accordion-settings';
/**
 * External dependencies
 */
import SectionBlock from '../section-block';
import { createBlock } from '@wordpress/blocks';
import { useDispatch, useSelect } from '@wordpress/data';

const TEMPLATE = [ [ 'sensei-lms/accordion-section', {} ] ];
const ALLOWED_BLOCKS = [ 'sensei-lms/accordion-section' ];

const AccordionEdit = ( props ) => {
	const { attributes, clientId } = props;
	const blockProps = useBlockProps();

	const { insertBlock } = useDispatch( 'core/block-editor' );
	const { isBlockSelected, hasSelectedInnerBlock } = useSelect(
		'core/block-editor'
	);

	const { children, ...innerProps } = useInnerBlocksProps(
		{},
		{
			template: TEMPLATE,
			allowedBlocks: ALLOWED_BLOCKS,
			renderAppender: false,
		}
	);

	const shouldRenderAppender =
		hasSelectedInnerBlock( clientId, true ) || isBlockSelected( clientId );

	const handleEnter = useCallback(
		( e ) => {
			if ( e.key === 'Enter' ) {
				if ( isBlockSelected( clientId ) ) {
					insertBlock(
						createBlock( SectionBlock.name ),
						undefined,
						clientId
					);
					e.nativeEvent.stopImmediatePropagation();
				}
			}
		},
		[ clientId, insertBlock, isBlockSelected ]
	);

	return (
		<div { ...blockProps } onKeyDownCapture={ handleEnter }>
			<Settings { ...props } />
			<Accordion { ...innerProps } isEditor attributes={ attributes }>
				{ children }
			</Accordion>
			{ shouldRenderAppender && (
				<ButtonBlockAppender rootClientId={ clientId } />
			) }
		</div>
	);
};

export default AccordionEdit;
