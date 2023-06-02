/**
 * WordPress dependencies
 */
import {
	useEffect,
	useContext,
	createContext,
	useCallback,
} from '@wordpress/element';
import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import {
	createBlock,
	createBlocksFromInnerBlocksTemplate,
	store as blocksStore,
} from '@wordpress/blocks';
import { store as blockEditorStore } from '@wordpress/block-editor';
import { store as noticeStore } from '@wordpress/notices';
import { __, sprintf } from '@wordpress/i18n';
import { useSelect, useDispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import timelineBlockMeta from '../timeline-block';
import isValidVideoBlock from './is-valid-video-block';

const VideoReplaceContext = createContext( undefined );

/**
 * Provider for the Interactive Video block's communication with core/video block.
 *
 * @param {Object}   props       Component props.
 * @param {Function} props.value The callback to pass to core/video block.
 */
export const VideoReplaceProvider = VideoReplaceContext.Provider;

/**
 * Return a component that modifies a block to pass an onReplace prop from the
 * context if one isn't defined.
 *
 * @param {Function} BlockEdit The component to overwrite.
 *
 * @return {Function} The HOC that detects if it has a value for VideoReplaceContext and
 * 						uses it as onReplace prop if onReplace is not defined.
 */
const withOnReplaceVideoBlock = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		const onReplace = useContext( VideoReplaceContext );

		// We removed the check for the core/video block here because this
		// HOC will only be used for the core/video block anyway, check
		// the function extendCoreVideoBlockEdit below.
		if ( ! props.onReplace && onReplace ) {
			return <BlockEdit { ...props } onReplace={ onReplace } />;
		}

		return <BlockEdit { ...props } />;
	};
}, 'withOnReplaceVideoBlock' );

/**
 * Injects the withOnReplaceVideoBlock as HOC for the edit component of the
 * core/video block.
 *
 * @param {Object} settings The settings of the block being handled.
 * @param {string} name     The name of the block to handle.
 * @return {Object} The new settings of the block.
 */
const extendCoreVideoBlockEdit = ( settings, name ) => {
	if ( name !== 'core/video' ) {
		return settings;
	}
	return {
		...settings,
		edit: withOnReplaceVideoBlock( settings.edit ),
	};
};

addFilter(
	'blocks.registerBlockType',
	'sensei-pro/interactive-video/extend-video-block-edit',
	extendCoreVideoBlockEdit,
	// The priority was set to 4 to fix a conflict between VideoPress v5
	// and the Interactive Video Block. The VideoPress v5 plugin uses
	// the same filter with a priority of 5, and it was causing the
	// Interactive Video Block to crash when transforming from the core/video
	// block modified by VideoPress in some environments.
	4
);

/**
 * Hook that returns a function to be used as onReplace for the core/video block.
 *
 * @param {string} clientId Block client ID.
 *
 * @return {Function} The function to be passed as onReplace for the core/video block.
 */
const useOnReplaceVideoBlock = ( clientId ) => {
	const { replaceInnerBlocks } = useDispatch( blockEditorStore );

	return useCallback(
		( newBlock ) => {
			replaceInnerBlocks(
				clientId,
				createBlocksFromInnerBlocksTemplate( [
					[ newBlock.name, newBlock.attributes ],
					[ timelineBlockMeta.name ],
				] )
			);
		},
		[ replaceInnerBlocks, clientId ]
	);
};

/**
 * Hook that detects whether the user is using an invalid video block and restores
 * the video back to the first variation (the video block), but only if it has
 * no breakpoints registered.
 *
 * @param {string} clientId Block client ID.
 */
export const useInvalidVideo = ( clientId ) => {
	const onReplace = useOnReplaceVideoBlock( clientId );
	const {
		replaceBlock,
		__unstableMarkNextChangeAsNotPersistent: markNextChangeAsNotPersistent = () => {},
	} = useDispatch( blockEditorStore );
	const { createErrorNotice } = useDispatch( noticeStore );

	// Get blocks references.
	const { interactiveVideoBlock, firstBlockVariation } = useSelect(
		( select ) => {
			const block = select( blockEditorStore ).getBlock( clientId );
			return {
				interactiveVideoBlock: block,
				firstBlockVariation: select( blocksStore ).getBlockVariations(
					block.name,
					'block'
				)[ 0 ],
			};
		},
		[ clientId ]
	);
	const [ videoBlock, timelineBlock ] = interactiveVideoBlock.innerBlocks;

	useEffect( () => {
		if (
			// If the block is not valid
			! isValidVideoBlock( videoBlock ) &&
			// If timeline has no content.
			timelineBlock.innerBlocks.length === 0
		) {
			const message = sprintf(
				// Translators: placeholder is video provider name
				__(
					'Videos from the provider "%s" are not supported in the Interactive Video Block',
					'sensei-pro'
				),
				videoBlock.attributes.providerNameSlug
			);
			createErrorNotice( message, {
				type: 'snackbar',
				explicitDismiss: true,
			} );
			const block = {
				...interactiveVideoBlock,
				attributes: firstBlockVariation.attributes,
				innerBlocks: createBlocksFromInnerBlocksTemplate(
					firstBlockVariation.innerBlocks
				),
			};
			markNextChangeAsNotPersistent();
			replaceBlock(
				clientId,
				createBlock( block.name, block.attributes, block.innerBlocks )
			);
		}
	}, [
		clientId,
		replaceBlock,
		firstBlockVariation,
		createErrorNotice,
		interactiveVideoBlock,
		videoBlock,
		timelineBlock,
		markNextChangeAsNotPersistent,
	] );

	return { onReplace };
};
