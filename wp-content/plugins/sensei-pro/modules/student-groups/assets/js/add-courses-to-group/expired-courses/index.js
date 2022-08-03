/**
 * WordPress dependencies
 */
import { Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { AccessPeriodSelector } from '../../access-period-selector';
import SelectedOptions from '../../selected-options';

const ExpiredOption = ( { option, onChange } ) => (
	<>
		<span>{ option.label }</span>
		<AccessPeriodSelector
			accessPeriod={ option.accessPeriod }
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

/**
 * Component to display expired courses
 *
 * @param {Object}   props                Component props.
 * @param {Array}    props.expiredCourses List of expired courses that should be rendered
 * @param {boolean}  props.isLoading      Enable/Disable the loading state
 * @param {Function} props.onUpdate       Event triggered when the access period is updated
 */
const ExpiredCourses = ( { expiredCourses, isLoading, onUpdate } ) => (
	<div
		className={ 'add-courses-to-group__expired' }
		data-testid="expired-courses"
	>
		<h3>{ __( 'Completed Courses', 'sensei-pro' ) }</h3>
		{ isLoading && <Spinner data-testid="loading-active" /> }
		<SelectedOptions
			options={ expiredCourses.map( ( course ) => ( {
				value: course.id,
				label: course.title,
				accessPeriod: course.accessPeriod,
				course,
			} ) ) }
			onChange={ ( event ) => onUpdate( event.value ) }
			disabled={ true }
			components={ {
				OptionItem: ExpiredOption,
			} }
		/>
	</div>
);

export default ExpiredCourses;
