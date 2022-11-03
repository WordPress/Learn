/**
 * Internal dependencies
 */
import { createReducer } from '../reduxHelpers';
import { actionTypes } from './actions';

/**
 * Block parent reducer.
 */
export const reducer = createReducer( {
	[ actionTypes.SET_PARENT ]: ( state, { blockId, parent } ) => {
		return {
			...state,
			[ blockId ]: parent,
		};
	},
} );
