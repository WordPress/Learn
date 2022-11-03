/**
 * External dependencies
 */
import { useVideoDuration } from 'sensei/assets/shared/helpers/player';

/**
 * Internal dependencies
 */
import ProgressBar from './progress-bar';
import useVideoProgress from './use-video-progress';
import { registerBlockFrontend } from '../../shared/block-frontend';
import meta from './block.json';
import { useContextFrontendPlayer } from '../frontend-player-context';
import ignorePersistedAttributes from '../../shared/ignore-persisted-attributes';

/**
 * Hook to get the video progress in the frontend.
 *
 * @return {number} Video progress percentage.
 */
const useFrontendVideoProgress = () => {
	const { player, currentTime } = useContextFrontendPlayer();
	return useVideoProgress( player, currentTime );
};

/**
 * Hook the section of the video that should be "blocked" for the user.
 *
 * @return {number|null} The percentage of the video that should be blocked, or null.
 */
const useVideoBlockedSection = () => {
	const { player, firstIncomplete } = useContextFrontendPlayer();
	const duration = useVideoDuration( player );
	if ( ! firstIncomplete || ! duration ) {
		return null;
	}
	return ( firstIncomplete.time / duration ) * 100;
};

/**
 * Break Point component to be used while to render in the frontend.
 *
 * @param {Object} props            Component props.
 * @param {Array}  props.children   Inner blocks, specifically, break points.
 * @param {Object} props.blockProps Block Props.
 */
const TimelineFrontend = ( { children, blockProps } ) => {
	const progress = useFrontendVideoProgress();
	const blockedAfter = useVideoBlockedSection();

	return (
		<div { ...blockProps }>
			<ProgressBar
				videoProgress={ progress }
				blockedAfter={ blockedAfter }
			>
				{ children }
			</ProgressBar>
		</div>
	);
};

ignorePersistedAttributes( meta.name );

registerBlockFrontend( {
	name: meta.name,
	run: TimelineFrontend,
} );
