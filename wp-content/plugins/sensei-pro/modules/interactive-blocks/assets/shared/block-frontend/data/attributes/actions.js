export const SET_ATTRIBUTES = 'SET_ATTRIBUTES';

export const SET_COMPLETED = 'SET_COMPLETED';

export const actionTypes = {
	SET_ATTRIBUTES,
	SET_COMPLETED,
};

export const setAttributes = ( blockId = '', attributes = {} ) => ( {
	type: SET_ATTRIBUTES,
	blockId,
	attributes,
} );

export const setCompleted = ( blockId = '', completed = true ) => ( {
	type: SET_COMPLETED,
	blockId,
	completed,
} );

export const actions = {
	setAttributes,
	setCompleted,
};
