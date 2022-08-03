/**
 * WordPress dependencies
 */
import { CheckboxControl } from '@wordpress/components';
/**
 * External dependencies
 */
import { motion, AnimatePresence } from 'framer-motion';
/**
 * External dependencies
 */
import { noop } from 'lodash';
/**
 * Internal dependencies
 */
import './style.scss';

const DefaultOptionItem = ( { option, onChange, disabled } ) => (
	<CheckboxControl
		checked={ true }
		onChange={ () => onChange( option ) }
		value={ option.value }
		label={ option.label }
		disabled={ disabled }
	/>
);

const SelectedOptions = ( {
	onChange = noop,
	components = { OptionItem: DefaultOptionItem },
	options = [],
	className = '',
	disabled = false,
} ) => {
	return (
		<ul
			className={ `${ className } selected-options` }
			data-testid="selected"
		>
			<AnimatePresence initial={ false }>
				{ options.map( ( option ) => (
					<motion.li
						key={ `selected-${ option.value }` }
						initial={ { opacity: 0, backgroundColor: '#FFFF00' } }
						animate={ { opacity: 1, backgroundColor: '#FFFFFF' } }
						exit={ {
							opacity: 0,
							backgroundColor: '#FFcccc',
						} }
					>
						<components.OptionItem
							key={ option.value }
							option={ option }
							onChange={ onChange }
							disabled={ disabled }
						></components.OptionItem>
					</motion.li>
				) ) }
			</AnimatePresence>
		</ul>
	);
};

export default SelectedOptions;
