export const SET_PARENT = 'SET_PARENT';

export const actionTypes = {
	SET_PARENT,
};

export const setParent = ( blockId = '', parent = null ) => ( {
	type: SET_PARENT,
	blockId,
	parent,
} );

export const actions = {
	setParent,
};
