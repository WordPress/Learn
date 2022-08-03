/**
 * Creates a reducer from an object of action keys mapped
 * to reducer funcitons.
 *
 * @param {Object} handlers A map of actions to their reducer handlers.
 */
export const createReducer = ( handlers = {} ) => (
	state = {},
	action = {}
) => {
	if ( 'function' === typeof handlers[ action.type ] ) {
		return handlers[ action.type ]( state, action );
	}
	return state;
};

/**
 * Binds the actions to the store so it is possible to dispatch
 * actions to the store directly from store.
 *
 * So you can do `store.addTodo( 'Do Laundry' )` instead of `store.dispatch( addTodo( 'Do Laundry' ) )`
 *
 * @param {Object} store   A redux store.
 * @param {Object} actions A map of action creators.
 */
export const bindActions = ( store, actions ) => {
	Object.keys( actions ).forEach( ( actionName ) => {
		store[ actionName ] = ( ...args ) =>
			store.dispatch( actions[ actionName ]( ...args ) );
	} );
};

/**
 * Binds the selectors to the store so it is possoble to select
 * from store state directly.
 * So you can do `store.getAttributes(blockId)` instead of `getAttributes(store.getState(), blockId)`
 *
 * @param {Object} store     A redux store.
 * @param {Object} selectors A map of state selectors.
 */
export const bindSelectors = ( store, selectors ) => {
	Object.keys( selectors ).forEach( ( selectorName ) => {
		store[ selectorName ] = ( ...args ) =>
			selectors[ selectorName ]( store.getState(), ...args );
	} );
};
