import { errors } from './constants';

export const getBlockPlaceholderMessage = ( blockPostType, currentPostType, isBlockInSidebar, defaultMessage ) => {
	if ( currentPostType === null ) {
		if ( ! isBlockInSidebar ) {
			return errors.BLOCK_SIDEBAR_TYPE_INCOMPATIBLE;
		}
	} else if ( blockPostType !== currentPostType ) {
		return errors.BLOCK_POST_TYPE_INCOMPATIBLE;
	}

	return defaultMessage;
};
