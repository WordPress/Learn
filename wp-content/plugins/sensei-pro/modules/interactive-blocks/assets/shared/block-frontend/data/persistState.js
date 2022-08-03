/**
 * External dependencies
 */
import { debounce } from 'lodash';

/**
 * Internal dependencies
 */
import { STORE_NAME } from './createStore';
import { prefixedStorage } from '../../storage';

/**
 * Time to wait before persisting the state into the storage.
 *
 * @member {number}
 */
const DEBOUNCE_TIMEOUT = 2000;

/**
 * The storage for sensei blocks.
 */
const storage = prefixedStorage( `${ STORE_NAME }--` );

const postId = window.sensei?.postId;

/**
 * Persists the store state in storage.
 *
 * @param {Object} store The redux store
 */
export const persistState = ( store ) => {
	const handleUpdate = debounce(
		() => {
			storage.setItem( postId, JSON.stringify( store.getState() ) );
		},
		DEBOUNCE_TIMEOUT,
		{
			trailing: true,
		}
	);

	store.subscribe( handleUpdate );
};

/**
 * Returns the persisted state.
 *
 * @return {Object} The persisted state or empty object if not available.
 */
export const getPersistedState = () => {
	return JSON.parse( storage.getItem( postId ) || '{}' );
};
