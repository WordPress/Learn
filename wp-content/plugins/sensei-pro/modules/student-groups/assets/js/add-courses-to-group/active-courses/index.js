/**
 * WordPress dependencies
 */
import { CheckboxControl } from '@wordpress/components';
/**
 * Internal dependencies
 */
import { AccessPeriodSelector } from '../../access-period-selector';
import SelectedOptions from '../../selected-options';
import { useState } from '@wordpress/element';
import ConfirmationDialog from '../../confirmation-dialog';
import { __ } from '@wordpress/i18n';
import './style.scss';

const ActiveOption = ( { option, onChange, disabled } ) => {
	return (
		<>
			<CheckboxControl
				checked={ option.course.removed === true ? false : true }
				onChange={ ( unchecked ) => {
					if ( ! unchecked ) {
						onChange( {
							type: 'UNSELECTED',
							value: option.course,
						} );
					} else {
						onChange( {
							type: 'SELECTED',
							value: option.course,
						} );
					}
				} }
				value={ option.value }
				label={ option.label }
				disabled={ disabled }
			/>
			<AccessPeriodSelector
				accessPeriod={ option.course.accessPeriod }
				disabled={ disabled || option.course.removed }
				onChange={ ( accessPeriod ) =>
					onChange( {
						type: 'ACCESS_PERIOD_UPDATED',
						value: {
							...option.course,
							accessPeriod,
						},
					} )
				}
			/>
		</>
	);
};

const CourseRemoveConfirmation = ( {
	showConfirmation,
	setShowConfirmation,
	selectedEvent,
	onUnSelect,
} ) => {
	const [ removeEnrolments, setRemoveEnrolments ] = useState( false );

	return (
		<ConfirmationDialog
			title={ __( 'Remove Course from Group', 'sensei-pro' ) }
			onCancel={ () => {
				setShowConfirmation( false );
			} }
			showDialog={ showConfirmation }
			okButtonText={ __( 'Remove Course', 'sensei-pro' ) }
			onConfirm={ () => {
				onUnSelect( { ...selectedEvent.value }, removeEnrolments );
				setShowConfirmation( false );
			} }
		>
			<div>
				{ __(
					'Are you sure you want to remove the course?',
					'sensei-pro'
				) }
			</div>
			<CheckboxControl
				className="sensei-group-remove-course-enrolment-checkbox"
				label={ __(
					'Remove the students from this course.',
					'sensei-pro'
				) }
				onChange={ setRemoveEnrolments }
				checked={ removeEnrolments }
			></CheckboxControl>
		</ConfirmationDialog>
	);
};

/**
 * Component to render courses with active access period
 *
 * @param {Object}   Props
 * @param {boolean}  Props.disabled      Enable/Disable the commponent
 * @param {Array}    Props.activeCourses List of active courses that should be rendered
 * @param {Function} Props.onSelect      Event triggered when a removed course is selected
 * @param {Function} Props.onUnSelect    Event triggered when a course is removed
 * @param {Function} Props.onUpdate      Event triggered when the access period is updated
 */
const ActiveCourses = ( {
	disabled,
	activeCourses,
	onUnSelect,
	onSelect,
	onUpdate,
} ) => {
	const [ showConfirmation, setShowConfirmation ] = useState( false );
	const [ selectedEvent, setSelectedEvent ] = useState( false );

	return (
		<div
			className={ 'add-courses-to-group__active' }
			data-testid="active-courses"
		>
			<CourseRemoveConfirmation
				showConfirmation={ showConfirmation }
				setShowConfirmation={ setShowConfirmation }
				selectedEvent={ selectedEvent }
				onUnSelect={ onUnSelect }
			/>
			<SelectedOptions
				options={ activeCourses.map( ( course ) => ( {
					value: course.id,
					label: course.title,
					course,
				} ) ) }
				components={ {
					OptionItem: ActiveOption,
				} }
				disabled={ disabled }
				onChange={ ( event ) => {
					switch ( event.type ) {
						case 'UNSELECTED':
							setSelectedEvent( event );
							setShowConfirmation( true );
							break;

						case 'SELECTED':
							onSelect( event.value );
							break;
						case 'ACCESS_PERIOD_UPDATED':
							onUpdate( event.value );
							break;
					}
				} }
			/>
		</div>
	);
};

export default ActiveCourses;
