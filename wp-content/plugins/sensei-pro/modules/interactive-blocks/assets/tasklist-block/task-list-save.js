/**
 * Internal dependencies
 */
import { TaskList } from './elements';

export function TaskListSave( { children, blockProps } ) {
	return <TaskList { ...blockProps }>{ children }</TaskList>;
}
