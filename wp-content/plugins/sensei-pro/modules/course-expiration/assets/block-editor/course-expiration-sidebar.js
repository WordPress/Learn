/**
 * WordPress dependencies
 */
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { __ } from '@wordpress/i18n';
import { SelectControl, TextControl } from '@wordpress/components';
import { useEntityProp } from '@wordpress/core-data';

/**
 * Internal dependencies
 */
import {
	EXPIRATION_TYPE,
	EXPIRATION_LENGTH,
	EXPIRATION_PERIOD,
	NO_EXPIRATION,
	EXPIRES_AFTER,
	MONTH,
	WEEK,
	DAY,
} from './constants';

/**
 * A hook that provides a value from course meta and a setter for that value.
 *
 * @param {string} metaName The name of the meta.
 *
 * @return {Array} An array containing the value and the setter.
 */
const useCourseMeta = ( metaName ) => {
	const [ meta, setMeta ] = useEntityProp( 'postType', 'course', 'meta' );

	const value = meta[ metaName ];
	const setter = ( newValue ) => setMeta( { [ metaName ]: newValue } );

	return [ value, setter ];
};

/**
 * Course Expiration Sidebar component.
 */
const CourseExpirationSidebar = () => {
	const [ expirationType, setExpirationType ] = useCourseMeta(
		EXPIRATION_TYPE
	);

	const [ expirationLength, setExpirationLength ] = useCourseMeta(
		EXPIRATION_LENGTH
	);

	const [ expirationPeriod, setExpirationPeriod ] = useCourseMeta(
		EXPIRATION_PERIOD
	);

	const onExpirationLengthChange = ( value ) => {
		// Sanitize it to a number greater than or equal 1.
		const sanitizedValue = Math.max(
			1,
			parseInt( value.replace( /\D/g, '' ) || 1, 10 )
		);
		setExpirationLength( sanitizedValue );
	};

	const onExpirationLengthKeyPress = ( e ) => {
		// Avoid non-digit chars.
		if ( /\D/.test( e.key ) ) {
			e.preventDefault();
		}
	};

	const expiresAfterForm = (
		<>
			<div className="sensei-wcpc-course-expiration__expires-after">
				<TextControl
					className="sensei-wcpc-course-expiration__expiration-length"
					label={ __( 'Expiration Length', 'sensei-pro' ) }
					hideLabelFromVision
					type="number"
					step={ 1 }
					min={ 1 }
					value={ expirationLength }
					onChange={ onExpirationLengthChange }
					onKeyPress={ onExpirationLengthKeyPress }
				/>
				<SelectControl
					label={ __( 'Expiration Period', 'sensei-pro' ) }
					hideLabelFromVision
					value={ expirationPeriod }
					options={ [
						{
							label: __( 'Month(s)', 'sensei-pro' ),
							value: MONTH,
						},
						{
							label: __( 'Week(s)', 'sensei-pro' ),
							value: WEEK,
						},
						{
							label: __( 'Day(s)', 'sensei-pro' ),
							value: DAY,
						},
					] }
					onChange={ setExpirationPeriod }
				/>
			</div>

			{ DAY === expirationPeriod && 1 === expirationLength && (
				<small className="sensei-wcpc-course-expiration__help-text">
					{ __(
						'The learner access will expire at midnight on the day of enrollment.',
						'sensei-pro'
					) }
				</small>
			) }
		</>
	);

	return (
		<PluginDocumentSettingPanel
			name="sensei-wcpc-course-access-period"
			title={ __( 'Access Period', 'sensei-pro' ) }
			className="sensei-wcpc-course-expiration"
		>
			<p className="sensei-wcpc-course-expiration__intro">
				{ __(
					'Set how long learners will have access to this course.',
					'sensei-pro'
				) }
			</p>

			<SelectControl
				label={ __( 'Expiration', 'sensei-pro' ) }
				value={ expirationType }
				options={ [
					{
						label: __( 'No expiration', 'sensei-pro' ),
						value: NO_EXPIRATION,
					},
					{
						label: __( 'Expires after', 'sensei-pro' ),
						value: EXPIRES_AFTER,
					},
				] }
				onChange={ setExpirationType }
			/>

			{ EXPIRES_AFTER === expirationType && expiresAfterForm }
		</PluginDocumentSettingPanel>
	);
};

export default CourseExpirationSidebar;
