/**
 * WordPress dependencies
 */
import { useMemo, useState } from '@wordpress/element';
/**
 * Internal dependencies
 */

/**
 * useSectionManager response.
 *
 * @typedef useAccordionManagerResponse
 *
 * @property {boolean}  isCompleted        Indicate if all accordion section was opened
 * @property {Function} isSectionCompleted Method to check if the section is opened
 * @property {Function} register           Method to register a section inside an accordion
 * @property {Function} toggleSection      Method to open/close a section
 * @property {Function} isSectionOpened    Method to check if the section is opened
 * @property {Array}    completedSections  List of completed sections
 * @property {Array}    registeredSections List of registered sections
 */

/**
 * Manage the Accordion state, considering its sections.
 *
 * @param {boolean} completed Indicates all sections was opened previously by the user. Only relevant with the required flag is set.
 * @param {boolean} isEditor  Indicate if the block is running inside the editor
 *
 * @return {useAccordionManagerResponse} Flags and methods to manage the accordion state and its sections.
 */

const useAccordionManager = (
	completed,
	isEditor = false,
	autoClose = false
) => {
	const [ completedSections, setCompleted ] = useState( [] );
	const [ registeredSections, setRegistered ] = useState( [] );
	const [ openedSections, setOpen ] = useState( new Set( [] ) );

	const isCompleted = useMemo( () => {
		if ( isEditor ) return false;

		return (
			registeredSections.length > 0 &&
			completedSections.length === registeredSections.length
		);
	}, [ completedSections.length, isEditor, registeredSections.length ] );

	const shouldUseAutoClose = isEditor ? false : autoClose;

	const complete = ( blockId ) =>
		setCompleted( ( state ) => [ ...new Set( [ ...state, blockId ] ) ] );

	const register = ( blockId, openOnLoad = false ) => {
		setRegistered( ( state ) => [ ...new Set( [ ...state, blockId ] ) ] );

		if ( openOnLoad ) openSection( blockId );
		if ( completed ) complete( blockId );
	};

	const isSectionCompleted = ( sectionBlockId ) =>
		completedSections.includes( sectionBlockId );

	const isSectionOpened = ( sectionBlockId ) =>
		openedSections.has( sectionBlockId );

	const openSection = ( blockId ) => {
		if ( shouldUseAutoClose ) return;

		setOpen( ( state ) => {
			state.add( blockId );
			return new Set( state );
		} );
	};

	const toggleSection = ( id ) => {
		setOpen( ( state ) => {
			if ( state.has( id ) ) {
				state.delete( id );
			} else {
				if ( shouldUseAutoClose ) {
					state.clear();
				}
				state.add( id );
			}

			// It is necessary to make react recognize the changes on the set object.
			return new Set( state );
		} );

		if ( ! isEditor ) complete( id );
	};

	return {
		isCompleted,
		isSectionCompleted,
		register,
		toggleSection,
		isSectionOpened,
		completedSections,
		registeredSections,
	};
};

export default useAccordionManager;
