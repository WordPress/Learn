/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { createHigherOrderComponent } from '@wordpress/compose';
import { addFilter } from '@wordpress/hooks';
import { getBlockSupport } from '@wordpress/blocks';
import { BlockControls } from '@wordpress/block-editor';
import { store as editorStore } from '@wordpress/editor';
import {
	ToolbarItem,
	ToolbarButton,
	ToolbarGroup,
} from '@wordpress/components';
import { useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import './video-required-toggle';
import { ReactComponent as IconRequired } from '../../icons/required.svg';

/**
 * Filters registered block settings, extending attributes with required
 * block attribute.
 *
 * @param {Object} settings Original block settings.
 *
 * @return {Object} Filtered block settings.
 */
export function addRequiredSupport( settings ) {
	const supportsRequired = getBlockSupport(
		settings,
		'sensei.required',
		false
	);
	if ( ! supportsRequired ) {
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
 * Override the default edit UI to add the required toggle button
 * into the block tools.
 *
 * @param {WPComponent} BlockEdit Original component.
 *
 * @return {WPComponent} Wrapped component.
 */
export const withRequiredSupport = createHigherOrderComponent(
	( BlockEdit ) => {
		return ( props ) => {
			const supportsRequired = getBlockSupport(
				props.name,
				'sensei.required',
				false
			);
			const { attributes, setAttributes } = props;
			const handleToggleRequired = useCallback( () => {
				setAttributes( { required: ! attributes.required } );
			}, [ attributes.required, setAttributes ] );

			const currentPostType = useSelect( ( select ) =>
				select( editorStore ).getCurrentPostType()
			);
			const isLessonPost = 'lesson' === currentPostType;

			return (
				<>
					{ supportsRequired && isLessonPost && (
						<BlockControls>
							<ToolbarGroup>
								<ToolbarItem>
									{ () => (
										<ToolbarButton
											label={ __(
												'Required',
												'sensei-pro'
											) }
											isPressed={ attributes.required }
											onClick={ handleToggleRequired }
											showTooltip
										>
											<div
												className={ classnames( {
													'sensei-supports-required__required-icon': true,
													'sensei-supports-required__required-icon--is-pressed':
														attributes.required,
												} ) }
											>
												<IconRequired />
											</div>
										</ToolbarButton>
									) }
								</ToolbarItem>
							</ToolbarGroup>
						</BlockControls>
					) }
					<BlockEdit { ...props } />
				</>
			);
		};
	},
	'withRequriedSupport'
);

if ( window.sensei?.supportsRequired ) {
	addFilter(
		'blocks.registerBlockType',
		'sensei/extend-supports/requried/addRequiredSupport',
		addRequiredSupport
	);

	addFilter(
		'editor.BlockEdit',
		'sensei/extend-supports/required/withRequiredSupport',
		withRequiredSupport
	);
}
