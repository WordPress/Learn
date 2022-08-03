/**
 * External dependencies
 */
import { noop } from 'lodash';

/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { createHigherOrderComponent } from '@wordpress/compose';
import { addFilter } from '@wordpress/hooks';
import { getBlockSupport } from '@wordpress/blocks';
import {
	PanelBody,
	ColorPalette,
	Card,
	CardBody,
	CardDivider,
	Dropdown,
} from '@wordpress/components';
import { InspectorControls } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import classNames from 'classnames';
import { Fragment } from '@wordpress/element';

const COLORS_SUPPORT_PATH = 'sensei.colors';

/**
 * Filters registered block settings, extending attributes with the
 * custom colors attributes.
 *
 * @param {Object} settings Original block settings.
 *
 * @return {Object} Filtered block settings.
 */
export function addColorsAttributes( settings ) {
	const colors = getBlockSupport( settings, COLORS_SUPPORT_PATH, false );
	if ( ! Array.isArray( colors ) ) {
		return settings;
	}

	const colorsAttributes = colors.reduce( ( attributes, color ) => {
		if ( color.name ) {
			attributes[ color.name ] = {
				type: 'string',
			};
			if ( color.default ) {
				attributes[ color.name ].default = color.default;
			}
		}
		return attributes;
	}, {} );

	// Set blockId attribute
	settings.attributes = {
		...settings.attributes,
		...colorsAttributes,
	};

	return settings;
}

/**
 * Filters registered block settings to extend the block edit wrapper
 * to apply the desired styles and classnames properly.
 *
 * @param {Object} settings Original block settings.
 *
 * @return {Object} Filtered block settings.
 */
export function addColorsEditProps( settings ) {
	const colors = getBlockSupport( settings, COLORS_SUPPORT_PATH, false );
	if ( ! Array.isArray( colors ) ) {
		return settings;
	}
	const existingGetEditWrapperProps = settings.getEditWrapperProps;
	settings.getEditWrapperProps = ( attributes ) => {
		let props = {};
		if ( existingGetEditWrapperProps ) {
			props = existingGetEditWrapperProps( attributes );
		}
		return {
			...props,
			style: {
				...props.style,
				...colorAttributesToStyle( colors, attributes ),
			},
		};
	};

	return settings;
}

/**
 * Creates a style object from attributes for the given colors settings.
 *
 * @param {Object[]} colors     A list of custom color settings.
 * @param {Object}   attributes The block attributes.
 * @return {Object} The map of color names and their values.
 */
const colorAttributesToStyle = ( colors, attributes ) =>
	colors.reduce( ( style, color ) => {
		if ( color.name && attributes[ color.name ] ) {
			style[ color.name ] = attributes[ color.name ];
		} else if ( color.default ) {
			style[ color.name ] = color.default;
		}
		return style;
	}, {} );

/**
 * Add the color input controls on the sidebar.
 *
 * @param {WPComponent} BlockEdit Original component.
 *
 * @return {WPComponent} Wrapped component.
 */
export const withColorsSupport = createHigherOrderComponent(
	( BlockEdit ) => ( props ) => {
		const colors = getBlockSupport(
			props.name,
			COLORS_SUPPORT_PATH,
			false
		);
		const { attributes, setAttributes } = props;

		const hasCustomColors = Array.isArray( colors ) && colors.length;

		return (
			<>
				<BlockEdit { ...props } />
				{ hasCustomColors && (
					<InspectorControls>
						<PanelBody title={ __( 'Colors', 'sensei-pro' ) }>
							<Card>
								{ colors.map( ( color, index ) => (
									<Fragment key={ color.name }>
										{ index !== 0 && (
											<CardDivider
												style={ { margin: 0 } }
											/>
										) }

										<CardBody style={ { padding: 0 } }>
											<Color
												color={ color }
												attributes={ attributes }
												setAttributes={ setAttributes }
											/>
										</CardBody>
									</Fragment>
								) ) }
							</Card>
						</PanelBody>
					</InspectorControls>
				) }
			</>
		);
	},
	'withColorsSupport'
);

const Color = ( { color, attributes, setAttributes } ) => {
	// Grab theme color palette.
	const { colors: paletteColors } = useSelect( ( select ) =>
		select( 'core/block-editor' ).getSettings()
	);

	if ( ! color.name ) {
		return null;
	}
	const colorValue = attributes[ color.name ];
	const colorIndicatorClassName = classNames(
		'sensei-supports-colors__color-indicator',
		{
			'sensei-supports-colors__color-indicator--empty': ! colorValue,
		}
	);
	return (
		<Dropdown
			className="sensei-supports-colors__dropdown"
			contentClassName="sensei-supports-colors__color-palette-container"
			renderToggle={ ( { onToggle } ) => (
				<div
					className="sensei-supports-colors__color"
					role="button"
					tabIndex={ 0 }
					onClick={ onToggle }
					onKeyDown={ noop }
				>
					<div
						className={ colorIndicatorClassName }
						style={ { backgroundColor: attributes[ color.name ] } }
					/>
					<div className="sensei-supports-colors__color-label">
						{ color.title }
					</div>
				</div>
			) }
			renderContent={ () => (
				<ColorPalette
					className="sensei-supports-colors__color-palette"
					colors={ paletteColors }
					onChange={ ( value ) =>
						setAttributes( {
							[ color.name ]: value,
						} )
					}
					value={ attributes[ color.name ] }
					__experimentalIsRenderedInSidebar
				/>
			) }
		/>
	);
};

/**
 * Updates the block save props to include the blockId attribute.
 *
 * @param {Object} extraProps Additional props applied to save element.
 * @param {Object} blockType  Block type.
 * @param {Object} attributes Current block attributes.
 *
 * @return {Object} Filtered props applied to save element.
 */
export function saveColors( extraProps, blockType, attributes ) {
	const colors = getBlockSupport( blockType, COLORS_SUPPORT_PATH, false );

	if ( ! Array.isArray( colors ) ) {
		return extraProps;
	}

	return {
		...extraProps,
		style: {
			...extraProps.style,
			...colorAttributesToStyle( colors, attributes ),
		},
	};
}

addFilter(
	'blocks.registerBlockType',
	'sensei/extend-supports/colors/addColorsAttributes',
	addColorsAttributes,
	10
);

addFilter(
	'blocks.registerBlockType',
	'sensei/extend-supports/colors/addColorsEditProps',
	addColorsEditProps,
	20
);

addFilter(
	'editor.BlockEdit',
	'sensei/extend-supports/colors/withColorsSupport',
	withColorsSupport
);

addFilter(
	'blocks.getSaveContent.extraProps',
	'sensei/extend-supports/colors/saveColors',
	saveColors
);
