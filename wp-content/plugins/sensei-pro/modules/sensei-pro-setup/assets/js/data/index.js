/**
 * WordPress dependencies
 */
import { createReduxStore, register } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { DATA_STORE_NAME } from './constants';
import reducer, { initialState } from './reducer';
import * as actions from './actions';
import * as selectors from './selectors';
import controls from './controls';

const senseiProSetupStore = createReduxStore( DATA_STORE_NAME, {
	reducer,
	actions,
	selectors,
	controls,
	initialState,
} );

register( senseiProSetupStore );
