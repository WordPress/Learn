/** @module block-frontend/registry */

/**
 * Block type registry.
 *
 * @member FrontendBlockType[]
 */
const registry = {};

/**
 * @typedef FrontendBlockType
 * Block definition.
 *
 * @property {string}   name     Block name.
 * @property {string}   selector CSS selector for the block root element. Defaults to .wp-block-[blockName]
 * @property {Function} run      Run function.
 *
 */

/**
 * Register a block type's frontend component.
 *
 * @param {FrontendBlockType} settings
 */
export function registerBlockFrontend( settings ) {
	const { name } = settings;
	registry[ name ] = settings;
}

/**
 * Get block types registered for the frontend.
 *
 * @return {FrontendBlockType[]} Block definitions.
 */
export function getFrontendBlockTypes() {
	return Object.values( registry );
}

/**
 * Get a block type by name.
 *
 * @param {string} name Block name.
 * @return {FrontendBlockType} Block definition.
 */
export function getFrontendBlockType( name ) {
	return registry[ name ];
}

/**
 * Get CSS selector for a block type.
 *
 * @param {FrontendBlockType} props
 * @return {string} CSS selector.
 */
export function getBlockTypeSelector( { selector, name } ) {
	// @wordpress/blocks::getBlockDefaultClassName
	const defaultClassName =
		'.wp-block-' + name.replace( /\//, '-' ).replace( /^core-/, '' );

	return selector ?? defaultClassName;
}
