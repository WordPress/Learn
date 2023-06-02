/**
 * WordPress dependencies
 */
import { Button, Modal, Notice, Spinner } from '@wordpress/components';
import { useCallback, useState, RawHTML } from '@wordpress/element';
import { search } from '@wordpress/icons';
import { __, _n, sprintf } from '@wordpress/i18n';
import { escapeHTML } from '@wordpress/escape-html';

/**
 * Internal dependencies
 */
import GroupList from './group-list';
import InputControl from '../../blocks/editor-components/input-control';
import useAbortController from '../../hooks/use-abort-controller';
import { addAll, removeAll } from '../../services/group-students';

const getAction = ( action, studentCount, studentDisplayName ) => {
	const safeStudentDisplayName = escapeHTML( studentDisplayName );

	const possibleActions = {
		addToGroup: {
			description:
				studentCount > 1
					? sprintf(
							// Translators: placeholder is the number of selected students.
							__(
								'Select the group(s) you would like to add <strong>%d students</strong> to:',
								'sensei-lms'
							),
							studentCount
					  )
					: sprintf(
							// Translators: placeholder is the student's name.
							__(
								'Select the group(s) you would like to add <strong>%s</strong> to:',
								'sensei-lms'
							),
							safeStudentDisplayName
					  ),
			buttonLabel: __( 'Add to Groups', 'sensei-lms' ),
			errorMessage: ( students ) =>
				_n(
					'Unable to add student. Please try again.',
					'Unable to add students. Please try again.',
					students.length,
					'sensei-lms'
				),
			sendAction: async ( students, groups ) =>
				await Promise.all(
					groups.map( ( group ) => addAll( group, students ) )
				),
			isDestructive: false,
		},
		removeFromGroup: {
			description:
				studentCount > 1
					? sprintf(
							// Translators: placeholder is the number of selected students.
							__(
								'Select the group(s) you would like to remove <strong>%d students</strong> from:',
								'sensei-lms'
							),
							studentCount
					  )
					: sprintf(
							// Translators: placeholder is the student's name.
							__(
								'Select the group(s) you would like to remove <strong>%s</strong> from:',
								'sensei-lms'
							),
							safeStudentDisplayName
					  ),
			buttonLabel: __( 'Remove from Groups', 'sensei-lms' ),
			errorMessage: ( students ) =>
				_n(
					'Unable to remove student. Please try again.',
					'Unable to remove students. Please try again.',
					students.length,
					'sensei-lms'
				),
			sendAction: async ( students, groups ) =>
				await Promise.all(
					groups.map( ( group ) => removeAll( group, students ) )
				),
			isDestructive: true,
		},
	};
	return possibleActions[ action ];
};

/**
 * Student Actions Modal.
 *
 * @param {Object}   props
 * @param {Object}   props.action             Action that is being performed.
 * @param {Function} props.onClose            Close callback.
 * @param {Array}    props.students           A list of Student ids related to the action should be applied.
 * @param {string}   props.studentDisplayName Name of the student, shown when there's only one student.
 */
export const StudentModal = ( {
	action,
	onClose,
	students,
	studentDisplayName,
} ) => {
	const {
		description,
		buttonLabel,
		errorMessage,
		isDestructive,
		sendAction,
	} = getAction( action, students.length, studentDisplayName );
	const [ selectedGroups, setGroups ] = useState( [] );
	const [ searchQuery, setSearchQuery ] = useState( '' );
	const [ isSending, setIsSending ] = useState( false );
	const [ error, setError ] = useState( false );
	const { getSignal } = useAbortController();

	const send = useCallback( async () => {
		setIsSending( true );

		try {
			await sendAction(
				students,
				selectedGroups.map( ( group ) => group.id ),
				{ signal: getSignal() }
			);
			onClose( true );
		} catch ( e ) {
			if ( ! getSignal().aborted ) {
				setError( true );
			}
		} finally {
			setIsSending( false );
		}
	}, [ sendAction, students, selectedGroups, onClose, getSignal ] );

	const searchGroups = ( value ) => setSearchQuery( value );

	return (
		<Modal
			className="sensei-student-modal"
			title={ __( 'Choose Group', 'sensei-lms' ) }
			onRequestClose={ () => onClose() }
		>
			<RawHTML>{ description }</RawHTML>

			<InputControl
				iconRight={ search }
				onChange={ searchGroups }
				placeholder={ __( 'Search groups', 'sensei-lms' ) }
				value={ searchQuery }
			/>

			<GroupList
				searchQuery={ searchQuery }
				onChange={ ( groups ) => {
					setGroups( groups );
				} }
			/>

			{ error && (
				<Notice
					status="error"
					isDismissible={ false }
					className="sensei-student-modal__notice"
				>
					{ errorMessage( students ) }
				</Notice>
			) }

			<div className="sensei-student-modal__action">
				<Button
					className={ `sensei-student-modal__action` }
					variant={ isDestructive ? '' : 'primary' }
					onClick={ () => send() }
					disabled={ isSending || selectedGroups.length === 0 }
					isDestructive={ isDestructive }
				>
					{ isSending && <Spinner /> }
					{ buttonLabel }
				</Button>
			</div>
		</Modal>
	);
};

export default StudentModal;
