/**
 * WordPress dependencies
 */
import { TextControl } from '@wordpress/components';
import { useCallback } from '@wordpress/element';

export const AfterCourseStart = ( props ) => {
	const { onChange, attributes } = props;
	const handleDaysAfterChange = useCallback(
		( value ) => {
			const daysAfterCourseStart = parseInt( value, 10 );
			onChange( {
				SCHEDULE: daysAfterCourseStart
					? { daysAfterCourseStart }
					: undefined,
			} );
		},
		[ onChange ]
	);
	return (
		<TextControl
			type="number"
			value={
				attributes.senseiVisibility?.SCHEDULE?.daysAfterCourseStart || 0
			}
			onChange={ handleDaysAfterChange }
			min={ 0 }
		/>
	);
};
