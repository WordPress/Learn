/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { SelectControl } from '@wordpress/components';
/**
 * External dependencies
 */
import { noop } from 'lodash';
import classNames from 'classnames';
/**
 * Internal dependencies
 */
import { BEM } from '../helpers/bem';

export const SkillLevelSelection = ( {
	onChange = noop,
	isLoading = false,
	value,
	name,
} ) => {
	const classes = classNames( BEM( { e: `${ name }-field` } ) );

	return (
		<fieldset className={ classes }>
			<SelectControl
				label={ __( 'Skill Level', 'sensei-pro' ) }
				name={ name }
				id={ name }
				value={ value }
				disabled={ isLoading }
				onChange={ ( newValue ) => onChange( newValue ) }
				options={ [
					{ label: __( 'Please select', 'sensei-pro' ), value: '' },
					{
						label: __( 'Beginner Level', 'sensei-pro' ),
						value: 'beginner',
					},
					{
						label: __( 'Intermediate Level', 'sensei-pro' ),
						value: 'intermediate',
					},
					{
						label: __( 'Advanced Level', 'sensei-pro' ),
						value: 'advanced',
					},
				] }
			></SelectControl>
		</fieldset>
	);
};
