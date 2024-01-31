/**
 * External dependencies
 */
import { noop } from 'lodash';
/**
 * Internal dependencies
 */
import { BEM } from '../helpers/bem';
import classNames from 'classnames';
import LimitedTextControl from '../limited-text-control';

export const TextAreaWithCounter = ( {
	value,
	name,
	label,
	maxLength = 300,
	onChange = noop,
	disabled = false,
	placeholder,
} ) => {
	const classes = classNames( BEM( { e: `${ name }-field` } ), {
		warning: value?.length === maxLength,
	} );

	return (
		<fieldset className={ classes }>
			<LimitedTextControl
				label={ label }
				name={ name }
				maxLength={ maxLength }
				value={ value }
				disabled={ disabled }
				multiline={ true }
				onChange={ onChange }
				placeholder={ placeholder }
			/>
		</fieldset>
	);
};
