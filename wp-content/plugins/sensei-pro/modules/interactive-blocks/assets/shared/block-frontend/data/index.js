/**
 * Internal dependencies
 */
import { createStore } from './createStore';
import { reducer } from './reducer';
import { persistState } from './persistState';
import { bindActions, bindSelectors } from './reduxHelpers';
import * as attributes from './attributes';
import * as parents from './parents';

export const blocksStore = createStore( reducer );

persistState( blocksStore );

bindActions( blocksStore, attributes.actions );
bindActions( blocksStore, parents.actions );
bindSelectors( blocksStore, attributes.selectors );
bindSelectors( blocksStore, parents.selectors );

if ( ! window.sensei ) {
	window.sensei = {};
}

if ( ! window.sensei.store ) {
	window.sensei.store = {};
}

window.sensei.store.blocks = blocksStore;
