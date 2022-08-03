/**
 * External dependencies
 */
import { useAutoInserter } from 'sensei/assets/shared/blocks/use-auto-inserter';

/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import { TaskList, TaskListRequired } from './elements';
import { TASK_BLOCK_NAME } from './constants';

export function TaskListEdit( props ) {
	useAutoInserter(
		{
			name: TASK_BLOCK_NAME,
			isEmptyBlock: ( { text } ) => ! text,
		},
		props
	);
	const { selectBlock } = useDispatch( 'core/block-editor' );
	const { selectedBlockId, innerBlocks } = useSelect(
		( select ) => {
			const blockEditorSelector = select( 'core/block-editor' );
			return {
				selectedBlockId: blockEditorSelector.getSelectedBlockClientId(),
				innerBlocks: blockEditorSelector.getBlocks( props.clientId ),
			};
		},
		[ props.clientId ]
	);

	/**
	 * Autoselect the first empty task item when TaskList
	 * block is inserted.
	 */
	useEffect( () => {
		if (
			selectedBlockId !== props.clientId ||
			innerBlocks.length !== 1 ||
			innerBlocks[ 0 ].attributes.text
		) {
			return;
		}
		selectBlock( innerBlocks[ 0 ].clientId );
	}, [ selectedBlockId, innerBlocks, props.clientId ] );

	const blockProps = useBlockProps();
	return (
		<>
			<TaskList { ...blockProps }>
				<InnerBlocks
					allowedBlocks={ [ TASK_BLOCK_NAME ] }
					template={ [] }
					renderAppender={ false }
				/>
				{ props.attributes.required && <TaskListRequired /> }
			</TaskList>
		</>
	);
}
