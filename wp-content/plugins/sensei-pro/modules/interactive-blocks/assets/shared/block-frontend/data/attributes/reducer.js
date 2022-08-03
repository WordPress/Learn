/**
 * Internal dependencies
 */
import { createReducer } from '../reduxHelpers';
import { actionTypes } from './actions';

/**
 * Attributes reducer.
 */
export const reducer = createReducer( {
	[ actionTypes.SET_ATTRIBUTES ]: ( state, { blockId, attributes } ) => {
		return {
			...state,
			[ blockId ]: {
				...( state[ blockId ] || {} ),
				...attributes,
			},
		};
	},

	[ actionTypes.SET_COMPLETED ]: ( state, { blockId, completed } ) => ( {
		...state,
		[ blockId ]: {
			...( state[ blockId ] || {} ),
			completed,
		},
	} ),
} );
