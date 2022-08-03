/**
 * Internal dependencies
 */
import { createStore } from './createStore';
import { reducer } from './reducer';
import { persistState } from './persistState';
import { bindActions, bindSelectors } from './reduxHelpers';
import { actions as attributesActions } from './attributes/actions';
import { selectors as attributesSelectors } from './attributes/selectors';

export const blocksStore = createStore( reducer );

persistState( blocksStore );

bindActions( blocksStore, attributesActions );
bindSelectors( blocksStore, attributesSelectors );

if ( ! window.sensei ) {
	window.sensei = {};
}

if ( ! window.sensei.store ) {
	window.sensei.store = {};
}

window.sensei.store.blocks = blocksStore;
