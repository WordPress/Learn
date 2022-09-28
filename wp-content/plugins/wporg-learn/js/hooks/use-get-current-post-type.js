/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';

export const useGetCurrentPostType = () => {
	return useSelect( ( select ) => {
		const { getCurrentPostType } = select( 'core/editor' );

		return getCurrentPostType();
	} );
};
