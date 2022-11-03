/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks';
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import { selectors } from '../shared/block-frontend/data/attributes';
import { blocksStore } from '../shared/block-frontend/data';
import { getPersistedState } from '../shared/block-frontend/data/persistState';
import {
	updateCompleteLessonButtons,
	prepareCompleteLessonButtons,
	addVideoCompletedStatus,
} from './dom';
import { BLOCK_ID_ATTRIBUTE } from '../shared/supports-block-id';

/**
 * Handles the blocks state change.
 */
function handleStateChange() {
	const state = blocksStore.getState();
	const requriedBlockIds = selectors.getRequiredBlockIds( state );

	// Do not bother the complete lesson buttons if there
	// are no required blocks.
	if ( ! requriedBlockIds.length ) {
		return;
	}

	const completedBlocksCount = selectors
		.areBlocksCompleted( state, requriedBlockIds )
		.filter( ( completed ) => completed ).length;

	updateCompleteLessonButtons(
		requriedBlockIds.length,
		completedBlocksCount
	);
}

/**
 * Remove the "required" attribute from the persisted state.
 * In cases when teacher removes the required attribute from the block
 * we don't want the block be still required because it's persisted state
 * has required attribute set to true.
 *
 * @param {Object.<string, any>} persistedAttributes The attributes that were stored in
 *                                                   the local storage last time user visited the current lesson.
 */
function filterOutRequiredAttribute( persistedAttributes = {} ) {
	const {
		// eslint-disable-next-line no-unused-vars
		required,
		...rest
	} = persistedAttributes;

	return rest;
}

/**
 * Persisted state from the last session.
 *
 * @member {Object}
 */
const persistedState = getPersistedState();

function initRequiredBlocks() {
	/**
	 * Turn off Lesson Complete button manipulation.
	 */
	wp.hooks.addFilter(
		'sensei.videoProgression.preventLessonCompletion',
		'sensei-pro',
		() => false
	);
	wp.hooks.addFilter(
		'sensei.videoProgression.allowLessonCompletion',
		'sensei-pro',
		() => false
	);

	/**
	 * Collect all required videos.
	 */
	wp.hooks.addAction(
		'sensei.videoProgression.registerVideo',
		'sensei-pro',
		( { url, blockElement } ) => {
			blocksStore.setAttributes( url, {
				blockId: url,
				required: true,
				completed: false,
				blockType: {
					name: 'sensei/video',
				},
				...selectors.getBlockAttributes( persistedState, url ),
			} );
			const parentBlock = blockElement.closest(
				`[${ BLOCK_ID_ATTRIBUTE }]`
			);
			if ( parentBlock ) {
				const parentId = parentBlock.getAttribute( BLOCK_ID_ATTRIBUTE );
				if ( parentId ) {
					blocksStore.setParent( url, parentId );
				}
			}
			addVideoCompletedStatus( url, blockElement );
		}
	);

	/**
	 * Mark videos completed when they are completed.
	 */
	wp.hooks.addAction(
		'sensei.videoProgression.videoEnded',
		'sensei-pro',
		( { url } ) => {
			blocksStore.setCompleted( url );
		}
	);

	/**
	 * Override sensei.videoProgression.allCompleted so it counts
	 * required interactive blocks too.
	 */
	wp.hooks.addFilter(
		'sensei.videoProgression.allCompleted',
		'sensei-pro',
		() => {
			const requiredBlockIds = blocksStore.getRequiredBlockIds();
			const completedBlocksCount = blocksStore
				.areBlocksCompleted( requiredBlockIds )
				.filter( ( completed ) => completed ).length;
			return requiredBlockIds.length === completedBlocksCount;
		}
	);

	/**
	 * Make sure the `required` attribute is never overwritten
	 * by the persisted state.
	 */
	addFilter(
		'sensei.blockFrontend.persistedAttributes',
		'sensei-pro',
		filterOutRequiredAttribute
	);

	prepareCompleteLessonButtons();
	blocksStore.subscribe( handleStateChange );
}

domReady( () => {
	// Enable RequiredBlocks if it is supported.
	if ( window.sensei?.supportsRequired ) {
		initRequiredBlocks();
	}
} );
