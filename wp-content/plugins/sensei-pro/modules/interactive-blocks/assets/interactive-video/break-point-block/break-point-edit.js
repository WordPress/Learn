/**
 * External dependencies
 */
import classnames from 'classnames';
import { useVideoDuration } from 'sensei/assets/shared/helpers/player';
import roundWithDecimals from 'sensei/assets/shared/helpers/player/round-with-decimals.js';

/**
 * WordPress dependencies
 */
import { useEffect, useState, useRef, useCallback } from '@wordpress/element';
import {
	useBlockProps,
	InnerBlocks,
	BlockControls,
	store as blockEditorStore,
} from '@wordpress/block-editor';
import {
	ToolbarButton,
	ToolbarGroup,
	ToolbarItem,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useDispatch, useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import BreakPointButton from './break-point-button';
import EditTime from './edit-time';
import useBreakPointPositionStyle from './use-break-point-position-style';
import { useContextEditorPlayer } from '../editor-player-context';
import usePrevious from '../use-previous';
import useDragging from '../../use-dragging';
import BreakPointPopover from './break-point-popover';

/**
 * Hook to sync video time while updating point time.
 *
 * @param {number}  time       Point time in seconds.
 * @param {boolean} isSelected If point block is selected.
 * @param {Object}  player     Player instance.
 */
const useSyncVideoTime = ( time, isSelected, player ) => {
	const previousTime = usePrevious( time );

	const goToTimeAndPause = useCallback(
		() => player.setCurrentTime( time ).then( () => player.pause() ),
		[ time, player ]
	);

	useEffect( () => {
		if ( undefined !== previousTime && time !== previousTime ) {
			goToTimeAndPause();
		}
	}, [ previousTime, time, goToTimeAndPause ] );

	useEffect( () => {
		if ( isSelected ) {
			goToTimeAndPause();
		}
	}, [ isSelected, goToTimeAndPause ] );
};

/**
 * Point dragging hook.
 *
 * @param {Object}   options               Hook options.
 * @param {Object}   options.pointRef      Break point ref.
 * @param {number}   options.duration      Duration of the video.
 * @param {number}   options.time          Current time of the video.
 * @param {Function} options.setAttributes Function to set Block Attributes.
 *
 * @return {Object} Same useDragging return.
 */
const usePointDragging = ( { pointRef, duration, time, setAttributes } ) => {
	const [ timeBeforeDragging, setTimeBeforeDragging ] = useState();

	/**
	 * Drag event.
	 */
	const onDrag = useCallback(
		( position ) => {
			const barWidth = pointRef.current.getBoundingClientRect().width;

			if ( duration && barWidth ) {
				const movedTime = ( duration * position.diffX ) / barWidth;

				// It prevents JS math issues with decimals.
				const roundedMovedTime = roundWithDecimals( movedTime, 3 );

				setAttributes( {
					time: Math.min(
						Math.max( 0, timeBeforeDragging + roundedMovedTime ),
						duration
					),
				} );
			}
		},
		[ duration, setAttributes, timeBeforeDragging, pointRef ]
	);

	/**
	 * Save time before dragging for calculation on drag start.
	 */
	const onDragStart = useCallback( () => {
		setTimeBeforeDragging( time );
	}, [ time ] );

	return useDragging( { onDrag, onDragStart } );
};

/**
 * Custom hook that manages the popover.
 *
 * @param {string} clientId The block ID.
 */
const usePopover = ( clientId ) => {
	const hasContent = useHasContent( clientId );
	const { isBlockSelected, hasSelectedInnerBlock, getBlock } = useSelect(
		blockEditorStore
	);
	const { selectBlock } = useDispatch( blockEditorStore );

	const isSelectedDeep =
		isBlockSelected( clientId ) || hasSelectedInnerBlock( clientId, true );

	const [ isPopoverOpen, setIsPopoverOpen ] = useState( false );
	const togglePopover = () => {
		setIsPopoverOpen( ( prev ) => ! prev );
	};
	const closePopover = () => {
		setIsPopoverOpen( false );
	};

	const selectFirstBlockIfNonSelected = useCallback( () => {
		if ( ! hasSelectedInnerBlock( clientId ) ) {
			const innerBlocks = getBlock( clientId ).innerBlocks;
			if ( innerBlocks.length > 0 ) {
				selectBlock( innerBlocks[ 0 ].clientId );
			}
		}
	}, [ clientId, getBlock, hasSelectedInnerBlock, selectBlock ] );

	useEffect( () => {
		if ( ! isSelectedDeep ) {
			closePopover();
			return;
		}

		if ( ! hasContent ) {
			setIsPopoverOpen( true );
		}
	}, [ isSelectedDeep, hasContent ] );

	useEffect( () => {
		if ( isPopoverOpen ) {
			selectFirstBlockIfNonSelected();
		}
	}, [ isPopoverOpen, selectFirstBlockIfNonSelected ] );

	return {
		isSelectedDeep,
		isPopoverOpen,
		togglePopover,
		closePopover,
	};
};

/**
 * Check if the given block ID has content or not.
 *
 * @param {string} clientId The block ID.
 * @return {boolean} Whether the block has content or not.
 */
const useHasContent = ( clientId ) => {
	const { getBlocks } = useSelect( blockEditorStore );
	const blocks = getBlocks( clientId );
	const hasContent = ( block ) => {
		// Check if paragraph block is not empty.
		if ( block.name === 'core/paragraph' ) {
			return block.attributes.content !== '';
		}
		// Any other block is considered as non-empty.
		return true;
	};
	return blocks.some( hasContent );
};

/**
 * Break Point Block edit component.
 *
 * @param {Object}   props                 Component props.
 * @param {string}   props.clientId        Block client ID.
 * @param {Object}   props.attributes      Block attributes.
 * @param {number}   props.attributes.time Video time in seconds.
 * @param {boolean}  props.isSelected      true if the block is selected.
 * @param {Function} props.setAttributes   Function to set block attributes.
 */
const BreakPointEdit = ( {
	clientId,
	attributes: { time },
	setAttributes,
	isSelected,
} ) => {
	const pointRef = useRef();
	const player = useContextEditorPlayer();
	const positionStyle = useBreakPointPositionStyle( time, player );
	const duration = useVideoDuration( player );
	const blockProps = useBlockProps( { ref: pointRef } );
	const { selectBlock } = useDispatch( blockEditorStore );

	const { isDragging, draggableProps } = usePointDragging( {
		pointRef,
		duration,
		time,
		setAttributes,
	} );

	useSyncVideoTime( time, isSelected, player );

	const {
		isSelectedDeep,
		isPopoverOpen,
		togglePopover,
		closePopover,
	} = usePopover( clientId );

	const hasContent = useHasContent( clientId );

	return (
		<div { ...blockProps }>
			<BreakPointButton
				className={ classnames( {
					'wp-block-sensei-pro-break-point__button--is-selected': isSelectedDeep,
					'wp-block-sensei-pro-break-point__button--is-dragging': isDragging,
				} ) }
				style={ positionStyle }
				hasContent={ hasContent }
				onClick={ () => selectBlock( clientId ) }
				{ ...draggableProps }
			/>
			<BlockControls>
				<ToolbarGroup>
					<ToolbarButton
						onClick={ togglePopover }
						isActive={ isPopoverOpen }
					>
						{ __( 'Edit content', 'sensei-pro' ) }
					</ToolbarButton>
					<ToolbarItem
						as={ EditTime }
						time={ time }
						setAttributes={ setAttributes }
						duration={ duration }
					/>
				</ToolbarGroup>
			</BlockControls>
			{ isPopoverOpen && (
				<BreakPointPopover onClose={ closePopover }>
					<InnerBlocks
						template={ [
							[
								'core/paragraph',
								{
									placeholder: __(
										'Type / to choose a block',
										'sensei-pro'
									),
								},
							],
						] }
						templateLock={ false }
					/>
				</BreakPointPopover>
			) }
		</div>
	);
};

export default BreakPointEdit;
