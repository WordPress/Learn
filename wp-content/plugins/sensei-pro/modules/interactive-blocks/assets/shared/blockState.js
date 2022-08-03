/**
 * External dependencies
 */
import { merge } from 'lodash';

/**
 * Internal dependencies
 */
import { prefixedStorage } from './storage';

const storage = prefixedStorage( 'sensei_block_' );

/**
 * Retrieves the block state from storage for the given ids, if there are any.
 *
 * @param {string[]} blockIds The list of block ids to fetch.
 * @return {Object} The map of block ids and their state.
 */
const getStateFromStorage = async ( blockIds = [] ) => {
	const storageState = {};
	blockIds.forEach( ( blockId ) => {
		storageState[ blockId ] = JSON.parse(
			storage.getItem( blockId ) || '{}'
		);
	} );
	return storageState;
};

/**
 * Retrieves the block state from the backend api for the given ids.
 *
 * @todo Implement this method.
 * @param {string[]} blockIds The list of block ids to fetch.
 * @return {Object} The map of block ids and their state.
 */
const getStateFromApi = async ( blockIds = [] ) => {
	const apiState = {};
	blockIds.forEach( ( blockId ) => {
		apiState[ blockId ] = {};
	} );
	return apiState;
};

/**
 * Creates the state getter. The resulting getter makes attempt to retrieve the state
 * from the DOM, the localStorage and the backend REST api.
 *
 * @param {Function} getStateFromDom The function that given a list of block ids
 *                                   retrieves the state for that block from the DOM.
 * @return {Function} The function that given the list of block ids retrieves the block state.
 */
export const createStateGetter = ( getStateFromDom = () => ( {} ) ) => async (
	blockIds = []
) => {
	return merge(
		{},
		await getStateFromDom( blockIds ),
		await getStateFromStorage( blockIds ),
		await getStateFromApi( blockIds )
	);
};

/**
 * Saves the block state to the localStorage.
 *
 * @param {Object} state The map of block ids and their state.
 * @return {boolean} True if saved successfully, false otherwise.
 */
const saveStateToStorage = async ( state = {} ) => {
	try {
		Object.keys( state ).forEach( ( blockId ) => {
			storage.setItem( blockId, JSON.stringify( state[ blockId ] ) );
		} );
		return true;
	} catch {
		return false;
	}
};

/**
 * Saves the block state in the backend via REST API.
 *
 * @todo Implement this.
 * @return {Object} The map of block ids and their state.
 */
const saveStateToApi = async () => {
	return false;
};

/**
 * Saves the block state. Attempts to save into the backend
 * if user is logged in or fallbacks to localStorage if user is not
 * logged in.
 *
 * @param {Object} state The map of block ids and their state.
 * @return {boolean} True if saved successfully or false otherwise.
 */
export const saveState = async ( state = {} ) => {
	let saved = await saveStateToApi( state );

	if ( ! saved ) {
		saved = await saveStateToStorage( state );
	}

	return saved;
};
