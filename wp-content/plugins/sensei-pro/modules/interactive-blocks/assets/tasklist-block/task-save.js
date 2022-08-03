/**
 * WordPress dependencies
 */
import { RichText } from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import { Task, TaskCheckbox } from './elements';
import { InnerBlocksWrapper } from '../shared/block-frontend/editor';

export function TaskSave( { attributes, blockProps } ) {
	// Do not save the task if there is no text in it.
	if ( ! attributes?.text ) {
		return null;
	}

	return (
		<Task { ...blockProps }>
			<TaskCheckbox checked={ attributes.checked } />
			<InnerBlocksWrapper>
				<RichText.Content
					value={ attributes.text }
					tagName="p"
					className={ Task.bem( '-text' ) }
				/>
			</InnerBlocksWrapper>
		</Task>
	);
}
