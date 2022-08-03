/**
 * External dependencies
 */
import { intersection } from 'lodash';

/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { useCallback, useMemo } from '@wordpress/element';
import { SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const excludeFrom = {
	course: [
		'COMPLETED_LESSON',
		'NOT_COMPLETED_LESSON',
		'GROUPS',
		'SCHEDULE',
	],
	lesson: [ 'GROUPS', 'SCHEDULE' ],
};

/**
 * Internal dependencies
 */
import { emptyOption, options, optionsMap } from './options';

const userStatusValues = options.map( ( option ) => option.value );
const getUserStatusValue = ( senseiVisibility = {} ) => {
	const senseiVisibilityValues = Object.keys( senseiVisibility );
	return (
		intersection( userStatusValues, senseiVisibilityValues )[ 0 ] ||
		emptyOption
	);
};

export const UserStatus = ( props ) => {
	const { onChange, attributes } = props;

	const handleUserStatusChange = useCallback(
		( userStatus ) => {
			const prevValue = getUserStatusValue( attributes.senseiVisibility );
			// Remove the setting for previous user status
			const changes = { [ prevValue ]: undefined };

			// Set the new user status setting if it is not empty option
			if ( userStatus !== emptyOption ) {
				changes[ userStatus ] = {};
			}

			onChange( changes );
		},
		[ onChange, attributes.senseiVisibility ]
	);

	const currentPostType = useSelect( ( select ) =>
		select( 'core/editor' ).getCurrentPostType()
	);

	const userStatusOptions = useMemo(
		() =>
			options.filter(
				( { value } ) =>
					! excludeFrom[ currentPostType ].includes( value )
			),
		[ currentPostType ]
	);

	const value = getUserStatusValue( attributes.senseiVisibility );
	const description = optionsMap[ value || emptyOption ]?.description || '';

	return (
		<div className="sensei-block-visibility__option">
			<p className="sensei-block-visibility__option-title">
				{ __( 'Who should see this block?', 'sensei-pro' ) }
			</p>
			<SelectControl
				options={ userStatusOptions }
				value={ value }
				onChange={ handleUserStatusChange }
			/>
			{ description && (
				<p className="sensei-block-visibility__option-description">
					{ description }
				</p>
			) }
		</div>
	);
};
