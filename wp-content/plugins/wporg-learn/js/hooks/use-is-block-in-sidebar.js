/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';

export const useIsBlockInSidebar = ( clientId, sidebarName ) => {
	return useSelect( ( select ) => {
		const { getBlockAttributes, getBlockName, getBlockParents } =
			select( 'core/block-editor' );
		const parents = getBlockParents( clientId );
		return parents.some( ( parent ) => {
			if ( 'core/widget-area' !== getBlockName( parent ) ) {
				return false;
			}
			const { id } = getBlockAttributes( parent );
			return id === sidebarName;
		} );
	} );
};
