/**
 * External dependencies
 */
import { combineReducers } from 'redux';
/**
 * Internal dependencies
 */
import { reducer as attributes } from './attributes';
import { reducer as parents } from './parents';

export const reducer = combineReducers( {
	attributes,
	parents,
} );
