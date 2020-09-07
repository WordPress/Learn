export const getHours = ( duration ) => {
	return Math.floor( ~~( duration / 3600 ) );
};

export const getMinutes = ( duration ) => {
	return Math.floor( ~~( ( duration % 3600 ) / 60 ) );
};

export const getSeconds = ( duration ) => {
	return Math.floor( ~~duration % 60 );
};

export const getDurationDisplay = ( duration ) => {
	return `${ getHours( duration ) }hr ${ getMinutes(
		duration
	) }m ${ getSeconds( duration ) }s`;
};
