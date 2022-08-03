/**
 * Visibility options list.
 */
export const options = window.sensei?.blockVisibility?.options || [];

/**
 * Visibility options map.
 */
export const optionsMap = options.reduce(
	( map, option ) => ( { ...map, [ option.value ]: option } ),
	{}
);

/**
 * Visibility option that is a placeholder for empty value.
 */
export const emptyOption =
	window.sensei?.blockVisibility?.emptyType || 'EVERYONE';
