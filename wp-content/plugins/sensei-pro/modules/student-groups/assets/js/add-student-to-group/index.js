/**
 * External dependencies
 */
import { sanitize } from 'dompurify';
/**
 * WordPress dependencies
 */
import { RawHTML, useMemo, useState } from '@wordpress/element';
import { sprintf, __ } from '@wordpress/i18n';
import { Button, Notice, Spinner } from '@wordpress/components';
/**
 * Internal dependencies
 */
import useResource from '../hooks/use-resource';
import OptionsSelector from '../option-selector';
import { useCallback } from 'react';

import './style.scss';
import useLazyRequest from '../hooks/use-lazy-request';
import { addAll } from '../services/group-students';

const toOption = ( student ) => ( {
	value: student.id,
	label: student.name,
	student,
} );

const ErrorMessage = ( { error } ) => (
	<Notice
		status="error"
		isDismissible={ false }
		className="add-student-to-group__notice"
	>
		<span data-testid="notice-content ">
			{ error?.message ||
				__( 'Something went wrong. Please try again.', 'sensei-pro' ) }
		</span>
	</Notice>
);

const Content = ( { groupName } ) => (
	<RawHTML>
		{ sprintf(
			// Translators: placeholder is the group name
			__(
				'<p>Students added to the <strong>%s</strong> group will be automatically enrolled in any courses assigned to this group.</p>',
				'sensei-pro'
			),
			sanitize( groupName )
		) }
	</RawHTML>
);

const AddToGroupButton = ( { disabled, onClick, isSending } ) => {
	return (
		<Button
			variant="primary"
			disabled={ disabled || isSending }
			onClick={ onClick }
		>
			{ isSending && <Spinner /> }
			{ ! isSending && __( 'Add to Group', 'sensei-pro' ) }
		</Button>
	);
};

/**
 * @typedef {Object} Props
 * @property {string}   groupName  The Group's name to be displayed on the text.
 * @property {groupId}  groupId    The Group's id to assign students.
 * @property {Function} onComplete Called with the students are assigned with success.
 */

/**
 * Component to assign students to a group
 *
 * @param {Props} props The component Props.
 * @return {JSX} The component.
 */
const AddStudentToGroup = ( { groupName, groupId, onComplete } ) => {
	const [ filters, setFilters ] = useState( { term: '', exclude: [] } );
	const [ selectedStudents, setSelectedStudents ] = useState( [] );

	const { resources: students, isLoading } = useResource( {
		resource: 'users',
		fields: [ 'id', 'name' ],
		term: filters.term,
		exclude: selectedStudents.map( ( student ) => student.id ),
	} );

	const {
		run: runAddAll,
		isLoading: isSending,
		error,
		response,
	} = useLazyRequest( addAll, [ groupId ] );

	const options = useMemo( () => students.map( toOption ), [ students ] );

	if ( response ) {
		onComplete( true );
	}

	const addToGroup = useCallback( async () => {
		await runAddAll(
			groupId,
			selectedStudents.map( ( student ) => student.id )
		);
	}, [ selectedStudents ] );

	return (
		<div className="add-student-to-group__wrapper">
			<div className="add-student-to-group__content">
				{ error && <ErrorMessage error={ error } /> }
				<Content groupName={ groupName } />
				<OptionsSelector
					options={ options }
					onSearch={ ( term ) => setFilters( { term } ) }
					onChange={ ( selected ) =>
						setSelectedStudents(
							selected.map( ( { student } ) => student )
						)
					}
					isLoading={ isLoading }
					placeholder={ __( 'Search For Students', 'sensei-pro' ) }
					className="student-selector"
					disabled={ isSending }
				/>
			</div>
			<div className="add-student-to-group__actions">
				<AddToGroupButton
					onClick={ addToGroup }
					disabled={ selectedStudents.length === 0 }
					isSending={ isSending }
				/>
			</div>
		</div>
	);
};

export default AddStudentToGroup;
