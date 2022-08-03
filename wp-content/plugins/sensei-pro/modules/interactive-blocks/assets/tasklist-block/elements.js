/**
 * External dependencies
 */
import { ReactComponent as IconCheck } from 'sensei/assets/icons/checked.svg';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { createBemComponent } from '../shared/bem';
import { TASK_CLASS_NAME, TASK_LIST_CLASS_NAME } from './constants';

export const TaskList = createBemComponent( {
	className: TASK_LIST_CLASS_NAME,
	as: 'ul',
} );

export const Task = createBemComponent( {
	className: TASK_CLASS_NAME,
	as: 'li',
} );

export const TaskCheckbox = ( {
	checked,
	onChange,
	inputProps = {},
	...props
} ) => {
	return (
		// eslint-disable-next-line jsx-a11y/label-has-for -- Has nested input.
		<label className={ Task.bem( '-checkbox' ) } { ...props }>
			<input
				type="checkbox"
				checked={ checked }
				onChange={ onChange }
				{ ...inputProps }
			/>
			{ /* eslint-disable-next-line jsx-a11y/anchor-is-valid -- Interaction provided by label */ }
			<a role="button" tabIndex="-1">
				<IconCheck />
			</a>
		</label>
	);
};

/**
 * Renders the task-list block's required status.
 *
 * @param {Object}  props
 * @param {boolean} props.completed Wether the block is in completed state.
 */
export const TaskListRequired = ( { completed = false } ) => {
	return (
		<p className="sensei-pro-task-list__required">
			{ completed
				? __(
						'* Required — Mark all the tasks as complete',
						'sensei-pro'
				  )
				: __(
						'* Required — Mark all the tasks as complete to finish the lesson',
						'sensei-pro'
				  ) }
		</p>
	);
};
