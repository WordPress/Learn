/**
 * WordPress dependencies
 */
import { useSelect, useDispatch } from '@wordpress/data';
import { createBlock } from '@wordpress/blocks';
import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import {
	BlockControls,
	store as blockEditorStore,
} from '@wordpress/block-editor';
import { ToolbarGroup, ToolbarButton } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { migrateAttributes } from './transforms';
import { ReactComponent as InteractiveVideoTransformIcon } from '../../icons/interactive-video-transform.svg';

/**
 * Hook to transform the block to interactive video block.
 *
 * @param {Object} props            Block edit props.
 * @param {string} props.clientId   Block client ID.
 * @param {string} props.name       Block name.
 * @param {Object} props.attributes Block attributes.
 *
 * @return {Function} Transform function.
 */
const useTransform = ( { clientId, name, attributes } ) => {
	const { replaceBlock } = useDispatch( blockEditorStore );

	const transform = () => {
		replaceBlock(
			clientId,
			createBlock(
				'sensei-pro/interactive-video',
				migrateAttributes( attributes ),
				[
					createBlock( name, attributes ),
					createBlock( 'sensei-pro/timeline' ),
				]
			)
		);
	};

	return transform;
};

/**
 * Filters video and embed block from Gutenberg, adding interactive video block customization.
 */
const addTransformButtonToVideoBlocks = () => {
	/**
	 * HOC that adds a button to the toolbar to transform to Interactive Video block.
	 */
	const withInteractiveVideoTransformation = createHigherOrderComponent(
		( BlockEdit ) => ( props ) => {
			const { clientId, name, attributes } = props;

			const { parents } = useSelect( ( select ) => ( {
				parents: select( blockEditorStore ).getBlockParentsByBlockName(
					clientId,
					'sensei-pro/interactive-video'
				),
			} ) );

			const transform = useTransform( props );

			const original = <BlockEdit { ...props } />;

			if (
				// Check embed or video.
				! [ 'core/embed', 'core/video' ].includes( name ) ||
				// Check embed (VideoPress, YouTube, Vimeo).
				( name === 'core/embed' &&
					! [ 'videopress', 'youtube', 'vimeo' ].includes(
						attributes?.providerNameSlug
					) ) ||
				// Check if it was already transformed.
				parents.length > 0
			) {
				return original;
			}

			return (
				<>
					{ original }
					<BlockControls group="block">
						<ToolbarGroup>
							<ToolbarButton
								icon={ InteractiveVideoTransformIcon }
								label={ __(
									'Add Break Points to the video',
									'sensei-pro'
								) }
								onClick={ transform }
							/>
						</ToolbarGroup>
					</BlockControls>
				</>
			);
		},
		'withInspectorControl'
	);

	addFilter(
		'editor.BlockEdit',
		'sensei-pro/add-interactive-video-transformation',
		withInteractiveVideoTransformation
	);
};

export default addTransformButtonToVideoBlocks;
