/**
 * WordPress dependencies
 */
import { useContext, useEffect } from '@wordpress/element';
/**
 * Internal dependencies
 */
import { AccordionContext } from '../accordion';

/**
 * useSectionManager response.
 *
 * @typedef UseSectionManagerResponse
 *
 * @property {boolean}  isEditor             Indicate if the block is running inside the editor
 * @property {boolean}  isOpen               Indicate if the section is open/close
 * @property {Function} toggleCurrentSection Method to open the current section
 * @property {boolean}  isRequired           Indicate if root accordion is required
 * @property {boolean}  isComplete           Indicate if the current section is complete
 */

/**
 * Manage the Accordion Section Open/Close state and methods
 *
 * @param {string}  blockId    The section block id. It is used to identify is the current section is open/close.
 * @param {boolean} openOnLoad Indicates if the default state is open or closed
 *
 * @return {UseSectionManagerResponse} Auxiliary flags and methods to manage the accordion section.
 */
const useSectionManager = ( blockId, openOnLoad = false ) => {
	const {
		register,
		toggleSection,
		isSectionCompleted,
		isSectionOpened,
		isEditor,
		isRequired,
	} = useContext( AccordionContext );

	const toggleCurrentSection = () => {
		toggleSection( blockId );
	};

	// eslint-disable-next-line react-hooks/exhaustive-deps
	useEffect( () => register( blockId, openOnLoad || isEditor ), [] );

	return {
		isEditor,
		toggleCurrentSection,
		isRequired,
		isComplete: isSectionCompleted( blockId ),
		isOpen: isSectionOpened( blockId ),
	};
};

export default useSectionManager;
