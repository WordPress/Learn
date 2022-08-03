/**
 * External dependencies
 */
import { combineReducers } from 'redux';
/**
 * Internal dependencies
 */
import { reducer as attributes } from './attributes';

export const reducer = combineReducers( {
	attributes,
} );
