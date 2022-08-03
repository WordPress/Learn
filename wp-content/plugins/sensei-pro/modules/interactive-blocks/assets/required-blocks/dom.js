/**
 * External dependencies
 */
import { Provider, useSelector } from 'react-redux';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { render, Fragment } from '@wordpress/element';
import { Tooltip } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { CompletedStatus } from '../shared/supports-required/elements';
import { blocksStore } from '../shared/block-frontend/data';
import { selectors } from '../shared/block-frontend/data/attributes';
import { blockTypeLabels } from './constants';

/**
 * The list of complete lesson button elements
 * and their initial innerText values.
 *
 * @member {Object[]}
 */
const completeLessonButtons = [];

/**
 * Populates the completeLesssonButtons list.
 */
export function prepareCompleteLessonButtons() {
	document
		.querySelectorAll( '[data-id="complete-lesson-button"]' )
		.forEach( ( button ) => {
			completeLessonButtons.push( {
				button,
				text: button.innerText,
			} );
			addCompleteLessonTooltip( button.parentElement );
		} );
}

/**
 * Updates the complete lesson buttons with correct "lesson
 * completenes" progress.
 *
 * @param {number} requiredBlockCount  The number of required blocks.
 * @param {number} completedBlockCount The number of completed blocks.
 */
export function updateCompleteLessonButtons(
	requiredBlockCount,
	completedBlockCount
) {
	completeLessonButtons.forEach( ( { button, text } ) => {
		button.innerText = `${ text } (${ completedBlockCount }/${ requiredBlockCount })`;
		button.disabled = requiredBlockCount > completedBlockCount;
	} );
}

function CompleteLessonTooltip() {
	const allBlocksAreComplete = useSelector( ( state ) => {
		const requriedBlockIds = selectors.getRequiredBlockIds( state );

		// If there are no required blocks then we're good.
		if ( ! requriedBlockIds.length ) {
			return true;
		}

		const completedBlocksCount = selectors
			.areBlocksCompleted( state, requriedBlockIds )
			.filter( ( completed ) => completed ).length;

		return requriedBlockIds.length <= completedBlocksCount;
	} );

	const uncompleteBlocksCount = useSelector( ( state ) => {
		const requiredBlockIds = selectors.getRequiredBlockIds( state );
		const requiredBlocksCount = requiredBlockIds.reduce(
			( groupedBlocksCount, blockId ) => {
				const blockAttributes = selectors.getBlockAttributes(
					state,
					blockId
				);

				const blockTypeName = blockAttributes?.blockType?.name || '';
				if ( ! blockTypeName ) {
					return groupedBlocksCount;
				}

				if ( ! groupedBlocksCount[ blockTypeName ] ) {
					groupedBlocksCount[ blockTypeName ] = {
						required: 0,
						completed: 0,
					};
				}

				// Increment the required counter for the block type.
				groupedBlocksCount[ blockTypeName ].required += 1;

				// Increment the completed counter for the block type if this block is completed.
				if ( blockAttributes.completed ) {
					groupedBlocksCount[ blockTypeName ].completed += 1;
				}

				return groupedBlocksCount;
			},
			{}
		);

		// Remove completed groups
		Object.keys( requiredBlocksCount ).forEach( ( groupName ) => {
			const group = requiredBlocksCount[ groupName ];
			if ( group.required <= group.completed ) {
				delete requiredBlocksCount[ groupName ];
			}
		} );

		return requiredBlocksCount;
	} );

	const TooltipComponent = allBlocksAreComplete ? Fragment : Tooltip;
	return (
		<TooltipComponent
			position="bottom left"
			text={
				<div className="complete-lesson-tooltip">
					<div className="complete-lesson-tooltip__title">
						{ __(
							'You still have some items to complete.',
							'sensei-pro'
						) }
					</div>
					<div className="complete-lesson-tooltip__summary">
						<div className="complete-lesson-tooltip__labels">
							{ Object.keys( uncompleteBlocksCount ).map(
								( blockTypeName ) => (
									<div
										className="complete-lesson-tooltip__label"
										key={ blockTypeName }
									>{ `${ blockTypeLabels[ blockTypeName ] }: ` }</div>
								)
							) }
						</div>
						<div className="complete-lesson-tooltip__counters">
							{ Object.keys( uncompleteBlocksCount ).map(
								( blockTypeName ) => (
									<div
										className="complete-lesson-tooltip__counter"
										key={ blockTypeName }
									>
										{ `(${ uncompleteBlocksCount[ blockTypeName ].completed }/${ uncompleteBlocksCount[ blockTypeName ].required })` }
									</div>
								)
							) }
						</div>
					</div>
				</div>
			}
		>
			<div
				className={ classnames(
					'sensei-supports-required__complete-lesson-overlay',
					{
						'sensei-supports-required__complete-lesson-overlay--completed': allBlocksAreComplete,
					}
				) }
			></div>
		</TooltipComponent>
	);
}

/**
 * Adds a disabled tooltip to Complete Lesson button.
 *
 * @param {HTMLElement} button The Complete Lesson button element.
 */
export function addCompleteLessonTooltip( button ) {
	const tooltipWrapper = document.createElement( 'div' );
	button.classList.add( 'sensei-supports-required__complete-lesson-form' );
	button.appendChild( tooltipWrapper );
	render(
		<Provider store={ blocksStore }>
			<CompleteLessonTooltip />
		</Provider>,
		tooltipWrapper
	);
}

/**
 * Renders Completed status for the video blocks.
 *
 * @param {Object} props
 * @param {string} props.id The id of the video block.
 */
function VideoCompletedStatus( { id } ) {
	const { required, completed } = useSelector( ( state ) =>
		selectors.getBlockAttributes( state, id )
	);
	if ( ! required ) {
		return null;
	}
	return (
		<CompletedStatus
			message={ __(
				'Required - Watch the full video to complete.',
				'sensei-pro'
			) }
			className="sensei-supports-required__video-block-completed-status"
			completed={ completed }
		/>
	);
}

/**
 * Adds a completed status UI for the video blocks.
 *
 * @param {string}      id           The video block id.
 * @param {HTMLElement} blockElement The video block DOM element.
 */
export function addVideoCompletedStatus( id, blockElement ) {
	const completedStatusWrapper = document.createElement( 'div' );
	blockElement.classList.add( 'sensei-supports-required__video-block' );
	blockElement.appendChild( completedStatusWrapper );
	render(
		<Provider store={ blocksStore }>
			<VideoCompletedStatus id={ id } />
		</Provider>,
		completedStatusWrapper
	);
}
