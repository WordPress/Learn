/**
 * External dependencies
 */
import {
	useConfirmDialogProps,
	ConfirmDialog,
} from 'sensei/assets/blocks/editor-components/confirm-dialog';

/**
 * WordPress dependencies
 */
import { useEffect, useState } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { createBlock } from '@wordpress/blocks';
import { store as blockEditorStore } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import usePrevious from '../use-previous';

/**
 * Hook that displays confirmation when trying to change the video.
 * If confirmed, it will clear the timeline, otherwise it will revert the change.
 *
 * @param {string} clientId Block client ID.
 *
 * @return {Object} Confirm dialog props.
 */
const useVideoChangeConfirmation = ( clientId ) => {
	const [ confirmDialogProps, confirm ] = useConfirmDialogProps();

	const { replaceBlock, replaceInnerBlocks } = useDispatch(
		blockEditorStore
	);

	// Get blocks references.
	const { interactiveVideoBlock } = useSelect(
		( select ) => ( {
			interactiveVideoBlock: select( blockEditorStore ).getBlock(
				clientId
			),
		} ),
		[ clientId ]
	);
	const [ videoBlock, timelineBlock ] = interactiveVideoBlock.innerBlocks;

	// Video URL for embed or video.
	const videoUrl = videoBlock?.attributes?.url || videoBlock?.attributes?.src;

	// Previous states.
	const previousVideoUrl = usePrevious( videoUrl );
	const previousInteractiveVideoBlock = usePrevious( interactiveVideoBlock );

	// State to avoid showing confirmation after reverted.
	const [ revertedTo, setRevertedTo ] = useState( null );

	useEffect( () => {
		if (
			// If previous video was set.
			previousVideoUrl &&
			// If timeline had content.
			timelineBlock.innerBlocks.length > 0 &&
			// If video URL was changed.
			previousVideoUrl !== videoUrl &&
			// If the change wasn't the revert.
			videoUrl !== revertedTo
		) {
			confirm(
				__(
					"Changing the video will delete all content that you've added to the Interactive Timeline. Are you sure you want to continue?",
					'sensei-pro'
				)
			).then( ( value ) => {
				if ( value ) {
					replaceInnerBlocks( timelineBlock.clientId, [] );
				} else {
					replaceBlock(
						clientId,
						createBlock(
							previousInteractiveVideoBlock.name,
							previousInteractiveVideoBlock.attributes,
							previousInteractiveVideoBlock.innerBlocks
						)
					);
					setRevertedTo( previousVideoUrl );
				}
			} );
		}
	}, [
		previousVideoUrl,
		videoUrl,
		revertedTo,
		timelineBlock,
		previousInteractiveVideoBlock,
		clientId,
		confirm,
		replaceBlock,
		replaceInnerBlocks,
	] );

	return confirmDialogProps;
};

/**
 * A component that monitors the video change to show a confirmation dialog.
 *
 * @param {Object} props          Component props.
 * @param {string} props.clientId Interactive Video block client ID.
 */
const VideoChangeConfirmation = ( { clientId } ) => {
	const confirmDialogProps = useVideoChangeConfirmation( clientId );

	return (
		<ConfirmDialog
			title={ __( 'Change video', 'sensei-pro' ) }
			confirmButtonText={ __( 'Change video', 'sensei-pro' ) }
			{ ...confirmDialogProps }
		/>
	);
};

export default VideoChangeConfirmation;
