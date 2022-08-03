/**
 * External dependencies
 */
import { useSelector } from 'react-redux';

/**
 * WordPress dependencies
 */
import {
	useCallback,
	createContext,
	useContext,
	useEffect,
} from '@wordpress/element';

/**
 * Internal dependencies
 */
import { TASK_BLOCK_NAME } from './constants';
import { registerBlockFrontend } from '../shared/block-frontend';
import blockInfo from './block.json';
import { TaskList, Task, TaskCheckbox, TaskListRequired } from './elements';

const TaskListBlockIdContext = createContext( null );

registerBlockFrontend( {
	name: blockInfo.name,
	run: function TaskListRun( {
		children,
		attributes,
		blockId,
		setAttributes,
	} ) {
		const areAllTasksChecked = useCallback(
			( state ) => {
				const uncheckedTasks = Object.keys( state.attributes )
					.map( ( taskId ) => state.attributes[ taskId ] )
					.filter( ( { taskListId } ) => taskListId === blockId )
					.filter( ( { checked } ) => ! checked );

				return ! uncheckedTasks.length;
			},
			[ blockId ]
		);

		const allTasksChecked = useSelector( areAllTasksChecked );

		useEffect( () => {
			setAttributes( { completed: allTasksChecked } );
		}, [ allTasksChecked, setAttributes ] );

		return (
			<TaskListBlockIdContext.Provider value={ blockId }>
				<TaskList>{ children }</TaskList>
				{ attributes.required && (
					<TaskListRequired completed={ attributes.completed } />
				) }
			</TaskListBlockIdContext.Provider>
		);
	},
} );

registerBlockFrontend( {
	name: TASK_BLOCK_NAME,
	run: function TaskRun( { children, attributes, setAttributes } ) {
		const taskListId = useContext( TaskListBlockIdContext );
		useEffect( () => {
			setAttributes( { taskListId } );
		}, [ taskListId, setAttributes ] );

		const handleChange = useCallback( () => {
			setAttributes( { checked: ! attributes.checked } );
		}, [ attributes.checked, setAttributes ] );

		return (
			<Task>
				<TaskCheckbox
					checked={ attributes.checked }
					onChange={ handleChange }
				/>
				{ children }
			</Task>
		);
	},
} );
