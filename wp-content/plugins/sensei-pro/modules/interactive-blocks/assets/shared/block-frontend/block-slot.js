/**
 * WordPress dependencies
 */
import { createContext, useContext } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { BlockFrontend } from './index';

/**
 * Sensei Slot Fill context object.
 */
const BlockSlotFillContext = createContext( {} );

/**
 * Slot component which consumes the fills from the provider.
 *
 * @param {Object}   props          Component props.
 * @param {Object}   props.name     Slot name.
 * @param {Function} props.children An optional render prop that calls the render with the
 *                                  respective `reactElement`.
 */
export const BlockSlot = ( { name, children: render } ) => {
	const blocks = useContext( BlockSlotFillContext );
	const fill = blocks?.find( ( { attributes: { slot } } ) => name === slot );
	const reactElement = fill ? <BlockFrontend block={ fill } /> : null;

	// Optional render prop.
	if ( render ) {
		return render( reactElement );
	}

	return reactElement;
};

/**
 * Block Slot Fill Provider.
 */
BlockSlot.Provider = BlockSlotFillContext.Provider;
