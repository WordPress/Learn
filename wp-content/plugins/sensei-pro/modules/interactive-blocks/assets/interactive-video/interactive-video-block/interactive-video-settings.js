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
import { useState, useEffect } from '@wordpress/element';
import {
	store as blockEditorStore,
	BlockControls,
	BlockIcon,
	InspectorControls,
} from '@wordpress/block-editor';
import { useSelect, useDispatch } from '@wordpress/data';
import {
	createBlocksFromInnerBlocksTemplate,
	createBlock,
	store as blocksStore,
} from '@wordpress/blocks';
import {
	ToolbarGroup,
	ToolbarDropdownMenu,
	ToolbarButton,
	Tooltip,
	PanelBody,
	ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { useContextEditorPlayer } from '../editor-player-context';
import isValidVideoBlock from './is-valid-video-block';

/**
 * Hook to return the block data of the inner blocks of the interactive video
 * block based on its client ID.
 *
 * @param {string} clientId Block client ID of the interactive video block.
 * @return {Object} A object containing data for the playerBlock and timelineBlock.
 */
const useInteractiveVideoInnerBlocks = ( clientId ) => {
	return useSelect(
		( select ) => {
			const { innerBlocks } = select( blockEditorStore ).getBlock(
				clientId
			);
			const [ playerBlock, timelineBlock ] = innerBlocks;
			return { playerBlock, timelineBlock };
		},
		[ clientId ]
	);
};

/**
 * Hook to sync the video type when it's changed through the embed block.
 *
 * @param {string}   videoType     Current video type in the Interactive Video Block.
 * @param {string}   clientId      Interactive Video Block client ID.
 * @param {Function} setAttributes Interactive Video Block set attributes function.
 */
const useSyncEmbedVideoType = ( videoType, clientId, setAttributes ) => {
	const { playerBlock } = useInteractiveVideoInnerBlocks( clientId );

	const {
		__unstableMarkNextChangeAsNotPersistent: markNextChangeAsNotPersistent = () => {},
	} = useDispatch( blockEditorStore );

	if (
		'core/embed' === playerBlock.name &&
		isValidVideoBlock( playerBlock ) &&
		playerBlock.attributes?.providerNameSlug !== videoType
	) {
		markNextChangeAsNotPersistent();
		setAttributes( { videoType: playerBlock.attributes.providerNameSlug } );
	}
};

/**
 * Hook that creates the dropdown props for video type selection.
 *
 * @param {Object}   attributes    Block attributes.
 * @param {Function} setAttributes Block set attributes function.
 * @param {string}   clientId      Block client ID.
 * @param {string}   name          Block name.
 * @param {Function} asyncConfirm  Async function to get the confirmation for the event listener.
 *
 * @return {Object} Dropdown props.
 */
const useVideoTypeDropdownProps = (
	attributes,
	setAttributes,
	clientId,
	name,
	asyncConfirm
) => {
	const { blockVariations, activeVariation } = useSelect(
		( select ) => ( {
			blockVariations: select( blocksStore ).getBlockVariations(
				name,
				'block'
			),
			activeVariation: select( blocksStore ).getActiveBlockVariation(
				name,
				attributes
			),
		} ),
		[ name, attributes ]
	);
	const { replaceInnerBlocks } = useDispatch( blockEditorStore );

	const { timelineBlock } = useInteractiveVideoInnerBlocks( clientId );
	const breakPointCount = timelineBlock.innerBlocks.length;

	const onVideoTypeChange = ( variation ) => () => {
		const confirmChange = () => {
			setAttributes( variation.attributes );
			replaceInnerBlocks(
				clientId,
				createBlocksFromInnerBlocksTemplate( variation.innerBlocks )
			);
		};
		if ( breakPointCount === 0 ) {
			confirmChange();
			return;
		}
		asyncConfirm(
			__(
				"Transforming the block to a different video type will delete all content that you've added to the Timeline. Are you sure you want to continue?",
				'sensei-pro'
			)
		).then( ( result ) => ( result ? confirmChange() : null ) );
	};

	const filteredBlockVariations = blockVariations.filter(
		( variation ) => variation.name !== activeVariation?.name
	);

	return {
		icon: activeVariation ? (
			<BlockIcon icon={ activeVariation.icon } />
		) : null,
		controls: filteredBlockVariations.map( ( variation ) => ( {
			title: variation.title,
			icon: <BlockIcon icon={ variation.icon } />,
			onClick: onVideoTypeChange( variation ),
		} ) ),
	};
};

/**
 * Hook to add a break point to the timeline.
 *
 * @param {string} clientId Block client ID.
 *
 * @return {Function} Function to add a break point to the timeline.
 */
const useAddBreakPointProps = ( clientId ) => {
	const [ isPlayerReady, setPlayerReady ] = useState( false );
	const { timelineBlock } = useInteractiveVideoInnerBlocks( clientId );
	const timelineClientId = timelineBlock.clientId;
	const { insertBlock } = useDispatch( blockEditorStore );
	const player = useContextEditorPlayer();

	const { points } = useSelect( ( select ) => ( {
		points: select( blockEditorStore ).getBlocks( timelineClientId ),
	} ) );

	useEffect( () => {
		if ( player ) {
			player.getPlayer().then( () => {
				setPlayerReady( true );
			} );
		}
	}, [ player ] );

	if ( ! isPlayerReady ) {
		return {
			isDisabled: true,
		};
	}

	const allowedDifference = 1; // Difference in seconds allowed between multiple points.

	return {
		onClick: () => {
			Promise.all( [
				player?.getCurrentTime(),
				player?.getDuration(),
			] ).then( ( [ currentTime, duration ] ) => {
				let adjustedTime = currentTime;

				const hasNotAllowedDiff = ( point ) =>
					Math.abs( adjustedTime - point.attributes.time ) <
					allowedDifference;

				while ( points.some( hasNotAllowedDiff ) ) {
					adjustedTime += allowedDifference;
				}

				adjustedTime = Math.min( adjustedTime, duration );

				insertBlock(
					createBlock( 'sensei-pro/break-point', {
						time: adjustedTime,
					} ),
					undefined,
					timelineClientId
				);

				player.setCurrentTime( adjustedTime );
			} );
		},
		isDisabled: false,
	};
};

/**
 * Interactive Video Block settings.
 *
 * @param {Object}   props               Component props.
 * @param {Object}   props.attributes    Block attributes.
 * @param {Function} props.setAttributes Block set attributes function.
 * @param {string}   props.clientId      Block client ID.
 * @param {string}   props.name          Block name.
 */
const InteractiveVideoSettings = ( {
	attributes,
	setAttributes,
	clientId,
	name,
} ) => {
	const { videoType } = attributes;

	// Syncs embed video type.
	useSyncEmbedVideoType( videoType, clientId, setAttributes );

	// Helps controlling ConfirmDialog component.
	const [ confirmDialogProps, asyncConfirm ] = useConfirmDialogProps();

	// Video type dropdown dynamic properties.
	const videoTypeDropdownProps = useVideoTypeDropdownProps(
		attributes,
		setAttributes,
		clientId,
		name,
		asyncConfirm
	);

	// Add break point button properties.
	const addBreakPointProps = useAddBreakPointProps( clientId );
	const addPointButton = (
		<ToolbarButton { ...addBreakPointProps }>
			{ __( 'Add Break Point', 'sensei-pro' ) }
		</ToolbarButton>
	);
	const toolbarButton = addBreakPointProps.isDisabled ? (
		<Tooltip text={ __( 'Please first add a video below', 'sensei-pro' ) }>
			<div>{ addPointButton }</div>
		</Tooltip>
	) : (
		addPointButton
	);

	return (
		<>
			<BlockControls>
				<ToolbarGroup>
					<ToolbarDropdownMenu
						label={ __( 'Select a video type', 'sensei-pro' ) }
						{ ...videoTypeDropdownProps }
					/>
				</ToolbarGroup>
				<ToolbarGroup>{ toolbarButton }</ToolbarGroup>
			</BlockControls>
			<InspectorControls>
				<PanelBody title={ __( 'Timeline', 'sensei-pro' ) }>
					<ToggleControl
						checked={ ! attributes.hiddenTimeline }
						onChange={ ( showTimeline ) => {
							setAttributes( { hiddenTimeline: ! showTimeline } );
						} }
						label={ __(
							'Show the timeline in the frontend',
							'sensei-pro'
						) }
					/>
				</PanelBody>
			</InspectorControls>
			<ConfirmDialog
				title={ __( 'Transform video type', 'sensei-pro' ) }
				confirmButtonText={ __( 'Transform video type', 'sensei-pro' ) }
				{ ...confirmDialogProps }
			/>
		</>
	);
};

export default InteractiveVideoSettings;
