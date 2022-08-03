/**
 * The mock object for the storage, in case it's not available.
 */
export const mockStorage = {
	setItem() {},
	getItem() {},
	removeItem() {},
	clear() {},
};

/**
 * The local storage. Uses localStorage. Fallbacks to sessionStorage in case localStorage is not available.
 * Fallbacks to noop storage if browser storage is not available.
 */
const storage = window.localStorage || window.sessionStorage || mockStorage;

export const prefixedStorage = ( prefix = '' ) => ( {
	setItem: ( key, value ) => storage.setItem( `${ prefix }${ key }`, value ),
	getItem: ( key ) => storage.getItem( `${ prefix }${ key }` ),
	removeItem: ( key ) => storage.removeItem( `${ prefix }${ key }` ),
	clear: storage.clear,
} );

export default storage;
