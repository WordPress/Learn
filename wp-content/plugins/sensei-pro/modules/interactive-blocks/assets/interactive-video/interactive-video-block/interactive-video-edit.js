/**
 * External dependencies
 */
import useEditorPlayer from 'sensei/assets/shared/helpers/player/use-editor-player';

/**
 * WordPress dependencies
 */
import {
	useBlockProps,
	InnerBlocks,
	store as blockEditorStore,
} from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import InteractiveVideoSettings from './interactive-video-settings';
import { EditorPlayerProvider } from '../editor-player-context';
import withRecursionNotAllowed from '../../with-recursion-not-allowed';
import { VideoReplaceProvider, useInvalidVideo } from './invalid-video-handler';
import isValidVideoBlock from './is-valid-video-block';
import VideoChangeConfirmation from './video-change-confirmation';

/**
 * Hook to get the player instance.
 *
 * @param {string} clientId Block client ID.
 *
 * @return {Object|undefined} Player instance.
 */
const usePlayer = ( clientId ) => {
	const { videoBlock } = useSelect(
		( select ) => {
			const { innerBlocks } = select( blockEditorStore ).getBlock(
				clientId
			);

			const block = innerBlocks[ 0 ];

			// Check if it's a valid video block.
			const valid = isValidVideoBlock( block );

			return { videoBlock: valid ? block : undefined };
		},
		[ clientId ]
	);

	return useEditorPlayer( videoBlock );
};

/**
 * Interactive Video Block edit component.
 *
 * @param {Object} props Component props.
 */
const InteractiveVideoEdit = ( props ) => {
	const { clientId } = props;

	const blockProps = useBlockProps();
	const player = usePlayer( clientId );
	const { onReplace } = useInvalidVideo( clientId );

	return (
		<EditorPlayerProvider value={ player }>
			<InteractiveVideoSettings { ...props } />
			<div { ...blockProps }>
				<VideoReplaceProvider value={ onReplace }>
					<InnerBlocks templateLock="all" />
				</VideoReplaceProvider>
			</div>
			<VideoChangeConfirmation clientId={ clientId } />
		</EditorPlayerProvider>
	);
};

export default withRecursionNotAllowed( InteractiveVideoEdit );
