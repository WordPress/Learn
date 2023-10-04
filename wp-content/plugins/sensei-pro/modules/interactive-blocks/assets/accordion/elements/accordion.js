/**
 * WordPress dependencies
 */
import { useEffect, createContext } from '@wordpress/element';

/**
 * Internal dependencies
 */
import useAccordionManager from './hooks/use-accordion-manager';
export const AccordionContext = createContext( {} );

/**
 * External dependencies
 */

import { LayoutGroup } from 'framer-motion';

export const Accordion = ( props ) => {
	const { setAttributes, attributes, isEditor, children } = props;
	const { autoClose, required, completed } = attributes;

	const {
		register,
		toggleSection,
		isSectionCompleted,
		isSectionOpened,
		isCompleted,
	} = useAccordionManager( completed, isEditor, autoClose );

	useEffect( () => {
		if ( isCompleted ) setAttributes( { completed: true } );
	}, [ isCompleted, isEditor, setAttributes ] );

	return (
		<AccordionContext.Provider
			value={ {
				register,
				isCompleted,
				toggleSection,
				isSectionCompleted,
				isSectionOpened,
				isEditor,
				isRequired: required,
			} }
		>
			<LayoutGroup>{ children }</LayoutGroup>
		</AccordionContext.Provider>
	);
};
