/**
 * External dependencies
 */
import { forEach, isEmpty } from 'lodash';

/**
 * WordPress dependencies
 */
import { useCallback } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { createHigherOrderComponent } from '@wordpress/compose';
import { addFilter } from '@wordpress/hooks';
import {
	InspectorControls,
	store as blockEditorStore,
} from '@wordpress/block-editor';
import { PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { VisibilityLabels } from '../visibility-labels';
import { UserStatus } from './user-status';
import { Schedule } from './schedule';
import { Groups } from './groups';

/**
 * Filters registered block settings, extending attributes with
 * block visibility attribute.
 *
 * @param {Object} settings Original block settings.
 *
 * @return {Object} Updated block settings.
 */
export function addVisibilityAttribute( settings ) {
	// Set visibility attribute
	settings.attributes = {
		...settings.attributes,
		senseiVisibility: {
			type: 'object',
		},
	};
	// hide conditional content block from the inserter.
	if ( settings.name === 'sensei-lms/conditional-content' ) {
		settings.parent = [ 'non-existing-block' ];
	}

	return settings;
}

/**
 * Override the default edit UI to add the visibility optoins
 * into the block's sidebar settings.
 *
 * @param {WPComponent} BlockEdit Original component.
 *
 * @return {WPComponent} Wrapped component.
 */
const withVisibilitySupport = createHigherOrderComponent(
	( BlockEdit ) => ( props ) => {
		const { setAttributes, attributes } = props;

		const excludedBlocks = [
			'sensei-lms/conditional-content',
			'sensei-lms/quiz',
			'sensei-lms/quiz-question',
			'sensei-lms/quiz-category-question',
			'sensei-lms/question-description',
			'sensei-lms/quiz-question-feedback-correct',
			'sensei-lms/quiz-question-feedback-incorrect',
			'sensei-lms/question-answers',
			'sensei-lms/course-progress',
			'sensei-lms/course-outline-module',
			'sensei-lms/course-outline-lesson',
		];
		const excludedParentBlockIds = useSelect( ( select ) =>
			select( blockEditorStore ).getBlockParentsByBlockName(
				props.clientId,
				excludedBlocks
			)
		);
		const isNotExcludedBlock =
			! excludedBlocks.includes( props.name ) &&
			! excludedParentBlockIds.length;

		const handleChange = useCallback(
			/**
			 * Updates the senseiVisibility attribute according to map of changes supplied.
			 *
			 * @param {Object} changes A map of changes for senseiVisibility attribute.
			 */
			( changes = {} ) => {
				// Get the senseiVisibility attribute
				let { senseiVisibility = {} } = attributes;

				// Loop over changes.
				forEach( changes, ( changeValue, visibilityType ) => {
					// If there is a value then add it to the visibility settings.
					if ( changeValue ) {
						senseiVisibility[ visibilityType ] = changeValue;
					} else {
						// If it is undefined then remove it from the visibility settings.
						delete senseiVisibility[ visibilityType ];
					}
				} );

				if ( isEmpty( senseiVisibility ) ) {
					// If the senseiVisibility settings is just an empty object
					// then we want it to be removed from the blocks attributes
					// by setting it to undefined.
					senseiVisibility = undefined;
				} else {
					// If it is not empty then all good. But if we set the
					// same object that we got from the attributes then the
					// change will not be detected. So we make sure it's a
					// new object.
					senseiVisibility = { ...senseiVisibility };
				}

				// Set the senseiVisibility attribute for the block.
				setAttributes( { senseiVisibility } );
			},
			[ setAttributes, attributes ]
		);

		return (
			<>
				<BlockEdit { ...props } />
				{ isNotExcludedBlock && (
					<>
						<InspectorControls key="sensei-visibility">
							<PanelBody
								title={ __( 'Block Visibility', 'sensei-pro' ) }
								initialOpen={ false }
							>
								<UserStatus
									{ ...props }
									onChange={ handleChange }
								/>
								<Schedule
									{ ...props }
									onChange={ handleChange }
								/>
								<Groups
									{ ...props }
									onChange={ handleChange }
								/>
							</PanelBody>
						</InspectorControls>
						<VisibilityLabels { ...props } />
					</>
				) }
			</>
		);
	}
);

addFilter(
	'blocks.registerBlockType',
	'sensei/extend-supports/visibility/addVisibilityAttribute',
	addVisibilityAttribute
);

addFilter(
	'editor.BlockEdit',
	'sensei/extend-supports/visibility/withVisibilitySupport',
	withVisibilitySupport
);
