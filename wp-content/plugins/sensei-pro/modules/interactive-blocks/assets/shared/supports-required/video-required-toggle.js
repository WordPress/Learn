/**
 * External dependencies
 */
import classnames from 'classnames';
import { isBoolean } from 'lodash';

/**
 * WordPress dependencies
 */
import { createHigherOrderComponent } from '@wordpress/compose';
import { addFilter } from '@wordpress/hooks';
import { BlockControls } from '@wordpress/block-editor';
import { Tooltip } from '@wordpress/components';
import { useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { CompletedStatus } from './elements';
import { ReactComponent as IconRequired } from '../../icons/required.svg';

/**
 * Tells if the current video block is one of the supported block types.
 *
 * @param {string} blockName  The block type name.
 * @param {Object} attributes The block attributes.
 * @return {boolean} True if the block is supported and false otherwise.
 */
function isSupportedVideoBlock( blockName, attributes ) {
	// If it's not a video or video embed block then it does not get a required toggle.
	if ( ! [ 'core/video', 'core/embed' ].includes( blockName ) ) {
		return false;
	}

	// If it's a video embed block and not one of the supported
	// ones then it does not get a required toggle.
	if (
		'core/embed' === blockName &&
		! [ 'videopress', 'youtube', 'vimeo' ].includes(
			attributes.providerNameSlug
		)
	) {
		return false;
	}

	return true;
}

/**
 * Filters registered block settings, extending attributes with required
 * block attribute.
 *
 * @todo Limit the settings rewrite to only video blocks. (youtube, vimeo, videopress ).
 *       Currently applies to all core/embed blocks.
 *
 * @param {Object} settings Original block settings.
 *
 * @return {Object} Filtered block settings.
 */
export function addVideoRequiredSupport( settings ) {
	if ( ! [ 'core/video', 'core/embed' ].includes( settings.name ) ) {
		return settings;
	}

	// Set required attribute
	settings.attributes = {
		...settings.attributes,
		required: {
			type: 'boolean',
		},
	};

	return settings;
}

/**
 * Override the default edit UI for video blocks to add the required
 * toggle button into the block tools.
 *
 * @param {WPComponent} BlockEdit Original component.
 *
 * @return {WPComponent} Wrapped component.
 */
export const withVideoRequiredSupport = createHigherOrderComponent(
	( BlockEdit ) => {
		return ( props ) => {
			const isSupported = isSupportedVideoBlock(
				props.name,
				props.attributes
			);

			// If it's not a lesson post then it does not get a required toggle.
			const isLessonPost = useSelect(
				( select ) =>
					'lesson' === select( 'core/editor' ).getCurrentPostType()
			);

			// Find out if the course video progression has the "Required:" setting
			// turnded on.
			const senseiCourseVideoRequired = useSelect( ( select ) => {
				const currentPost = select( 'core/editor' ).getCurrentPost();
				if ( 'lesson' !== currentPost.type ) {
					return false;
				}
				const currentPostCourse = select( 'core' ).getEntityRecord(
					'postType',
					'course',
					currentPost.meta._lesson_course
				);
				return currentPostCourse?.meta.sensei_course_video_required;
			} );

			const { attributes, setAttributes } = props;

			// If attributes.required is not set then fallback to course settings.
			const required = isBoolean( attributes.required )
				? attributes.required
				: senseiCourseVideoRequired;

			const handleToggleRequired = useCallback( () => {
				// When the block's required setting matches the course's video required
				// setting, we remove the block required setting so it fallbacks course wide setting.
				if (
					isBoolean( attributes.required ) &&
					senseiCourseVideoRequired === ! attributes.required
				) {
					setAttributes( { required: null } );
				} else {
					setAttributes( { required: ! required } );
				}
			}, [
				required,
				attributes.required,
				senseiCourseVideoRequired,
				setAttributes,
			] );

			if ( isSupported && isLessonPost ) {
				return (
					<div className="wp-block sensei-supports-required__video-block">
						<BlockControls>
							<Tooltip text={ __( 'Required', 'sensei-pro' ) }>
								<button
									isPressed={ attributes.required }
									onClick={ handleToggleRequired }
									showTooltip
									className="sensei-supports-required__required-button"
								>
									<div
										className={ classnames( {
											'sensei-supports-required__required-icon': true,
											'sensei-supports-required__required-icon--is-pressed': required,
										} ) }
									>
										<IconRequired />
									</div>
								</button>
							</Tooltip>
						</BlockControls>
						{ required && (
							<CompletedStatus
								showTooltip={ false }
								className="sensei-supports-required__video-block-completed-status"
							/>
						) }
						<BlockEdit { ...props } />
					</div>
				);
			}

			return <BlockEdit { ...props } />;
		};
	},
	'withVideoRequriedSupport'
);

/**
 * Override props assigned to save component to inject required attributes, if
 * applicable.
 *
 * @param {Object} extraProps Additional props applied to save element.
 * @param {Object} blockType  Block type.
 * @param {Object} attributes Current block attributes.
 *
 * @return {Object} Filtered props applied to save element.
 */
export function addVideoSaveProps( extraProps, blockType, attributes ) {
	if ( ! isSupportedVideoBlock( blockType.name, attributes ) ) {
		return extraProps;
	}

	if ( true === attributes.required ) {
		extraProps[ 'data-sensei-is-required' ] = true;
	}

	if ( false === attributes.required ) {
		extraProps[ 'data-sensei-is-not-required' ] = true;
	}

	return extraProps;
}

if ( window.sensei?.supportsRequired ) {
	addFilter(
		'blocks.registerBlockType',
		'sensei/extend-supports/required/addVideoRequiredSupport',
		addVideoRequiredSupport
	);

	addFilter(
		'editor.BlockEdit',
		'sensei/extend-supports/required/withVideoRequiredSupport',
		withVideoRequiredSupport
	);

	addFilter(
		'blocks.getSaveContent.extraProps',
		'sensei/extend-supports/required/addVideoSaveProps',
		addVideoSaveProps
	);
}
