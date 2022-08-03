/**
 * External dependencies
 */
import { Provider, useDispatch, useSelector } from 'react-redux';
import { isEmpty } from 'lodash';

/**
 * WordPress dependencies
 */
import {
	createElement,
	isValidElement,
	render,
	useCallback,
	useEffect,
	useRef,
} from '@wordpress/element';
import { applyFilters } from '@wordpress/hooks';

/**
 * Internal dependencies
 */
import { blocksStore } from './data';
import { actions, selectors } from './data/attributes';
import { getPersistedState } from './data/persistState';

/**
 * @typedef {module:block-frontend/parse~FrontendBlock} FrontendBlock
 */

/**
 * Replace a block's DOM node with the rendered run function.
 *
 * @param {FrontendBlock} block
 */
export function runBlock( block ) {
	const { element } = block;
	render( renderBlock( block ), element );
}

/**
 * Render a runnable or static block.
 *
 * @param {FrontendBlock} block
 */
export function renderBlock( block ) {
	const { element, blockType, clientId, innerBlocks } = block;

	if ( ! blockType ) {
		if ( isValidElement( element ) ) {
			return element;
		}
		return <RawElement key={ clientId } block={ block } />;
	}

	return (
		<Provider store={ blocksStore }>
			<BlockComponent
				block={ block }
				key={ clientId }
				children={ innerBlocks?.map( renderBlock ) ?? [] }
			/>
		</Provider>
	);
}

/**
 * Component to render a block instance manually.
 *
 * @param {FrontendBlock} block Block instance.
 */
export const BlockFrontend = ( { block } ) => {
	return renderBlock( block );
};

/**
 * Render the block's DOM element.
 *
 * @param {Object}        props
 * @param {FrontendBlock} props.block
 */
export function RawElement( { block } ) {
	const { element, blockProps } = block;

	return createElement( element.tagName.toLowerCase(), {
		...blockProps,
		dangerouslySetInnerHTML: { __html: element.innerHTML },
	} );
}

/**
 * The last saved state in local storage.
 */
const persistedState = getPersistedState();

/**
 * Render the block's react component.
 *
 * @param {Object}        props
 * @param {FrontendBlock} props.block
 */
export function BlockComponent( { block, ...rest } ) {
	const {
		blockType,
		blockProps,
		clientId,
		attributes: defaultAttributes,
	} = block;
	const { run: Component } = blockType;
	const blockId = blockProps[ 'data-sensei-block-id' ] || clientId;
	const persistedAttributes = useRef(
		selectors.getBlockAttributes( persistedState, blockId )
	);

	const dispatch = useDispatch();

	// Set the attributes for the block in initial mount.
	useEffect( () => {
		dispatch(
			actions.setAttributes( blockId, {
				// Set the blockId
				blockId,

				// Set block type name.
				blockType: block.blockType,

				// Populate the attributes with the default attributes that comes with the block.
				...defaultAttributes,

				/**
				 * Allows to modify blocks' persistedAttributes before they are
				 * added to the store as initial state for blocks.
				 *
				 * @since 1.2.0
				 *
				 * @name sensei.blockFrontend.persistedAttributes Hook that allows you to
				 * 												  filter persistedAttributes object.
				 *
				 * @param {Object}        persistedAttributes The attribures that were stored in local storage
				 *                                            that are going to be used as initial state for the block.
				 * @param {string}        blockId             The block id.
				 * @param {FrontendBlock} block               The frontend block object.
				 */
				...applyFilters(
					'sensei.blockFrontend.persistedAttributes',
					persistedAttributes.current,
					blockId,
					block
				),
			} )
		);
	}, [] );

	const attributes = useSelector( ( state ) =>
		selectors.getBlockAttributes( state, blockId )
	);

	const setAttributes = useCallback(
		( attr ) => {
			dispatch( actions.setAttributes( blockId, attr ) );
		},
		[ blockId ]
	);

	// Do not render the component if there are no attributes yet.
	if ( isEmpty( attributes ) ) {
		return null;
	}

	return (
		<Component
			{ ...block }
			{ ...rest }
			blockId={ blockId }
			attributes={ attributes }
			setAttributes={ setAttributes }
		/>
	);
}
