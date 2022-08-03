/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import meta from './block.json';
import { TASK_BLOCK_NAME } from './constants';
import { example } from './example';
import { TaskListEdit } from './task-list-edit';
import { TaskListSave } from './task-list-save';
import { TaskEdit } from './task-edit';
import { TaskSave } from './task-save';
import { ReactComponent as taskBlockIcon } from '../icons/task-block.svg';
import { ReactComponent as taskListBlockIcon } from '../icons/tasklist-block.svg';

const updatedMeta = {
	...meta,
	supports: {
		...meta.supports,
		sensei: {
			...meta.supports.sensei,
			colors: [
				{
					name: 'color',
					title: __( 'Text', 'sensei-pro' ),
				},
				{
					name: 'backgroundColor',
					title: __( 'Background', 'sensei-pro' ),
				},
				{
					name: '--checkbox-color',
					title: __( 'Checkbox', 'sensei-pro' ),
				},
			],
		},
	},
};

/**
 * Task list block definition.
 */
const TaskListBlock = {
	...updatedMeta,
	supports: {
		...updatedMeta.supports,
		sensei: {
			...updatedMeta.supports.sensei,
			required: true,
		},
	},
	example,
	title: __( 'Tasklist', 'sensei-pro' ),
	icon: taskListBlockIcon,
	description: __(
		'A list for users to check off as items are completed.',
		'sensei-pro'
	),
	keywords: [
		__( 'Sensei', 'sensei-pro' ),
		__( 'check list', 'sensei-pro' ),
		__( 'checklist', 'sensei-pro' ),
		__( 'tasks', 'sensei-pro' ),
		__( 'task list', 'sensei-pro' ),
		__( 'assignments', 'sensei-pro' ),
		__( 'checkboxes', 'sensei-pro' ),
	],
	edit: TaskListEdit,
	save: TaskListSave,
};

/**
 * Task block definition.
 */
const TaskBlock = {
	...updatedMeta,
	name: TASK_BLOCK_NAME,
	title: __( 'Task', 'sensei-pro' ),
	icon: taskBlockIcon,
	description: __( 'An individual item in a tasklist.', 'sensei-pro' ),
	parent: [ updatedMeta.name ],
	attributes: {
		text: {
			default: '',
			source: 'html',
			selector: 'p',
		},
		checked: {
			type: 'boolean',
			default: false,
			source: 'attribute',
			attribute: 'checked',
			selector: 'input',
		},
	},
	edit: TaskEdit,
	save: TaskSave,
};

const blocks = [ TaskListBlock, TaskBlock ];
blocks.forEach( ( block ) => registerBlockType( block.name, block ) );
