/**
 * External dependencies
 */
import { useVideoDuration } from 'sensei/assets/shared/helpers/player';

/**
 * Hook to get the video progress.
 *
 * @param {Object} player      Player instance.
 * @param {number} currentTime Video current time.
 *
 * @return {number} Video progress percentage.
 */
const useVideoProgress = ( player, currentTime ) => {
	const duration = useVideoDuration( player );

	if ( ! duration ) {
		return 0;
	}

	return ( currentTime / duration ) * 100;
};

export default useVideoProgress;
