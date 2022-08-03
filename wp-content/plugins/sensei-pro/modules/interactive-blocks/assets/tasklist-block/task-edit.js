/**
 * WordPress dependencies
 */
import { useCallback } from '@wordpress/element';
import { useSelect, useDispatch, select } from '@wordpress/data';
import { useBlockProps, RichText } from '@wordpress/block-editor';
import { createBlock } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import meta from './block.json';
import { Task, TaskCheckbox } from './elements';
import { TASK_BLOCK_NAME } from './constants';

export function TaskEdit( props ) {
	const { attributes, setAttributes, clientId } = props;
	const blockProps = useBlockProps();
	const {
		replaceBlocks,
		selectionChange,
		removeBlock,
		selectBlock,
		updateBlockAttributes,
		stopTyping,
		selectNextBlock,
	} = useDispatch( 'core/block-editor' );
	const { previousTask, nextTask, taskListId } = useSelect(
		( s ) => {
			const blockEditorSelector = s( 'core/block-editor' );
			const previousTaskId =
				blockEditorSelector.getAdjacentBlockClientId( clientId, -1 ) ||
				'';
			const nextTaskId =
				blockEditorSelector.getAdjacentBlockClientId( clientId, 1 ) ||
				'';
			return {
				taskListId:
					blockEditorSelector.getBlockParentsByBlockName(
						clientId,
						meta.name
					)?.[ 0 ] || '',
				previousTask:
					blockEditorSelector.getBlock( previousTaskId ) || {},
				nextTask: blockEditorSelector.getBlock( nextTaskId ) || {},
			};
		},
		[ clientId ]
	);

	/**
	 * Creates the block from the split action.
	 *
	 * @param {string} text The text for which the block should
	 *                      be created.
	 * @return {Object} The block instance.
	 */
	const handleSplit = useCallback(
		( text = '' ) =>
			createBlock( TASK_BLOCK_NAME, {
				...attributes,
				text,
				blockId: null,
			} ),
		[ attributes ]
	);

	/**
	 * Puts the blocks that were created by the split action
	 * into their correct places.
	 *
	 * @param {Object[]} blocks The list of blocks created by the split
	 *                          action
	 */
	const handleReplace = useCallback(
		( blocks ) => {
			const secondBlock = blocks[ 1 ];
			const blocksToRemove = [ clientId ];
			const secondBlockIsEmpty = secondBlock.attributes.text === '';
			const noNextBlock = ! nextTask.clientId;
			const nextBlockIsEmpty = nextTask.attributes?.text === '';

			// The newly created task block is unchecked initially.
			secondBlock.attributes = {
				...secondBlock.attributes,
				checked: false,
			};

			if ( secondBlockIsEmpty && noNextBlock ) {
				selectNextBlock( taskListId );
				return;
			}

			if ( nextBlockIsEmpty ) {
				blocksToRemove.push( nextTask.clientId );
			}

			replaceBlocks( blocksToRemove, blocks );
		},
		[ clientId, nextTask, taskListId ]
	);

	/**
	 * Combines the current task block with the previous one
	 * when available.
	 */
	const handleMerge = useCallback( () => {
		if ( ! previousTask.clientId ) {
			if ( nextTask.clientId && ! attributes.text ) {
				removeBlock( clientId );
				selectBlock( nextTask.clientId );
			} else if ( ! attributes.text ) {
				removeBlock( clientId );
				if ( taskListId ) {
					removeBlock( taskListId );
				}
			}
			return;
		}
		const text = `${ previousTask.attributes.text }${ attributes.text }`;
		const mergedBlock = createBlock(
			TASK_BLOCK_NAME,
			previousTask.attributes
		);
		replaceBlocks( [ clientId, previousTask.clientId ], [ mergedBlock ] );
		selectBlock( mergedBlock.clientId, -1 );
		setTimeout( () => {
			const selection = select( 'core/block-editor' ).getSelectionEnd();
			updateBlockAttributes( mergedBlock.clientId, { text } );
			selectionChange(
				mergedBlock.clientId,
				selection.attributeKey,
				selection.offset,
				selection.offset
			);
			stopTyping();
		} );
	}, [
		clientId,
		attributes.text,
		previousTask.attributes,
		previousTask.clientId,
		nextTask.clientId,
		replaceBlocks,
		taskListId,
		removeBlock,
		selectBlock,
	] );

	return (
		<Task { ...blockProps }>
			<TaskCheckbox
				checked={ attributes.checked }
				onChange={ () => {
					setAttributes( { checked: ! attributes.checked } );
				} }
				inputProps={ {
					tabIndex: '-1',
				} }
			/>
			<RichText
				tagName="p"
				className={ Task.bem( '-text' ) }
				placeholder={ __( 'New Task', 'sensei-pro' ) }
				value={ attributes.text || '' }
				onChange={ ( text ) => setAttributes( { text } ) }
				onSplit={ handleSplit }
				onReplace={ handleReplace }
				onMerge={ handleMerge }
				identifier={ `${ clientId }-text` }
			/>
		</Task>
	);
}
