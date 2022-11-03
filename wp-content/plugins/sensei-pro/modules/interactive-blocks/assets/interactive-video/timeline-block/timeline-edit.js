/**
 * WordPress dependencies
 */
import { useState, useEffect } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import {
	useBlockProps,
	InnerBlocks,
	store as blockEditorStore,
} from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import ProgressBar from './progress-bar';
import useVideoProgress from './use-video-progress';
import { useContextEditorPlayer } from '../editor-player-context';

/**
 * Hook that auto select parent block when current block is selected.
 *
 * @param {string}  clientId   Block client ID.
 * @param {boolean} isSelected Whether block is selected.
 */
const useAutoSelectParent = ( clientId, isSelected ) => {
	const { selectBlock } = useDispatch( blockEditorStore );
	const { parentClientId } = useSelect(
		( select ) => ( {
			parentClientId: select( blockEditorStore )
				.getBlockParents( clientId )
				.slice( -1 )[ 0 ], // Get last.
		} ),
		[ clientId ]
	);

	useEffect( () => {
		if ( isSelected ) {
			selectBlock( parentClientId );
		}
	}, [ isSelected, parentClientId, selectBlock ] );
};

/**
 * Return the number of inner blocks on a given block.
 *
 * @param {string} clientId Block client ID.
 * @return {number} The number of inner blocks on the block
 */
const useInnerBlocksCount = ( clientId ) => {
	const { innerBlocksCount } = useSelect(
		( select ) => ( {
			innerBlocksCount: select( blockEditorStore ).getBlock( clientId )
				.innerBlocks.length,
		} ),
		[ clientId ]
	);
	return innerBlocksCount;
};

/**
 * Hook to get the video current time.
 *
 * @return {number} Video current time.
 */
const useEditorCurrentTime = () => {
	const player = useContextEditorPlayer();
	const [ currentTime, setCurrentTime ] = useState( 0 );

	useEffect( () => {
		if ( ! player ) {
			return;
		}

		const event = player.on( 'timeupdate', setCurrentTime );

		return () => {
			event.then( ( unsubscribe ) => {
				unsubscribe();
			} );
		};
	}, [ player ] );

	return currentTime;
};

/**
 * Hook to get the video progress in the editor.
 *
 * @return {number} Video progress percentage.
 */
const useEditorVideoProgress = () => {
	const player = useContextEditorPlayer();
	const currentTime = useEditorCurrentTime();
	const progress = useVideoProgress( player, currentTime );

	return progress;
};

/**
 * Interactive Video Block edit component.
 *
 * @param {Object}   props               Component props.
 * @param {string}   props.clientId      Block client ID.
 * @param {boolean}  props.isSelected    Whether block is selected.
 * @param {Function} props.setAttributes Callback to set attributes of the block
 */
const TimelineEdit = ( { clientId, isSelected, setAttributes } ) => {
	useAutoSelectParent( clientId, isSelected );

	const progress = useEditorVideoProgress();

	const {
		__unstableMarkNextChangeAsNotPersistent: markNextChangeAsNotPersistent,
	} = useDispatch( blockEditorStore );

	const breakPointsCount = useInnerBlocksCount( clientId );
	useEffect( () => {
		markNextChangeAsNotPersistent();
		setAttributes( { breakPointsCount } );
	}, [ markNextChangeAsNotPersistent, breakPointsCount, setAttributes ] );

	const blockProps = useBlockProps();

	const ALLOWED_BLOCKS = [ 'sensei-pro/break-point' ];

	return (
		<div { ...blockProps }>
			<ProgressBar videoProgress={ progress }>
				<InnerBlocks
					allowedBlocks={ ALLOWED_BLOCKS }
					templateLock={ false }
				/>
			</ProgressBar>
		</div>
	);
};

export default TimelineEdit;
