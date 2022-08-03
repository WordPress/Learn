/**
 * External dependencies
 */
import { legacy_createStore as createReduxStore, compose } from 'redux';

/**
 * Store name.
 *
 * @member {string}
 */
export const STORE_NAME = 'sensei/blocks';

const composeEnhancers =
	typeof window === 'object' && window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__
		? window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__( {
				name: STORE_NAME,
		  } )
		: compose;

export const createStore = ( reducer, initialState = {} ) => {
	return createReduxStore( reducer, initialState, composeEnhancers() );
};
