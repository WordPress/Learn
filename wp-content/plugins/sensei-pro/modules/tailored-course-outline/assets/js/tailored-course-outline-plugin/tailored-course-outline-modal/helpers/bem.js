/**
 * External dependencies
 */
import { isEmpty, trimEnd } from 'lodash';

const bemBlockName = 'sensei-tailored-course-outline-modal';

export const BEM = ( { e = '', m = '' } = {} ) => {
	const element = isEmpty( e ) ? bemBlockName : `${ bemBlockName }__${ e }`;
	const modifier = isEmpty( m ) ? '' : `${ element }--${ m }`;

	return trimEnd( [ element, modifier ].join( ' ' ) );
};
