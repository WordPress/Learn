/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';
import { render, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
/**
 * Internal dependencies
 */
import BulkActionSelector from '../bulk-action-selector';
import GroupStudentBulkRemove from '../group-student-bulk-remove';

import './style.scss';

const options = [
	{
		label: 'Remove from Group',
		value: 'remove-from-group',
	},
];

const SelectStudentBulkAction = ( { groupId, items } ) => {
	const [ action, setAction ] = useState( '' );

	return (
		<>
			<BulkActionSelector
				options={ options }
				placeholder={ __( 'Bulk Student actions', 'sensei-pro' ) }
				disabled={ items.length === 0 }
				onApply={ setAction }
			/>
			{ action === 'remove-from-group' && (
				<GroupStudentBulkRemove
					studentIds={ items }
					groupId={ groupId }
					onCancel={ () => setAction( '' ) }
				/>
			) }
		</>
	);
};

export const init = () => {
	const inputs = Array.from(
		document.querySelectorAll( '.students-selector' )
	);
	const selectAllInput = document.getElementById( 'cb-select-all-1' );
	const selectedValues = new Map();

	const toggleSelection = ( input ) => {
		if ( input.checked === true ) selectedValues.set( input.value, true );
		else selectedValues.delete( input.value );
	};

	const toggleAll = () => {
		inputs.forEach( ( input ) => toggleSelection( input ) );
	};

	selectAllInput.addEventListener( 'change', () => {
		toggleAll();
		renderComponent( selectedValues.keys() );
	} );

	inputs.forEach( ( input ) => {
		input.addEventListener( 'change', ( e ) => {
			toggleSelection( e.target );
			renderComponent( selectedValues.keys() );
		} );
	} );

	renderComponent();
};

export const renderComponent = ( items = [] ) => {
	const target = document.getElementById( 'group-students-bulk-action' );
	if ( ! target ) return null;
	render(
		<SelectStudentBulkAction
			{ ...target?.dataset }
			items={ Array.from( items ) }
		/>,
		target
	);
};

export default SelectStudentBulkAction;

domReady( init );
