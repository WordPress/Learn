/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components';
import { useMemo } from '@wordpress/element';

/**
 * Internal dependencies
 */
import CaptionsControl from './captions-control';
import DurationControl from './duration-control';
import { getDurationDisplay } from './utils';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
 *
 * @param {Object} [props]           Properties passed from the editor.
 * @param {string} [props.className] Class name generated for the block.
 *
 * @return {WPElement} Element to render.
 */

const strings = {
	language: __( 'Language', 'wporg-learn' ),
	captions: __( 'Captions', 'wporg-learn' ),
	searchCaptions: __( 'Search for languages (en)', 'wporg-learn' ),
	duration: __( 'Length', 'wporg-learn' ),
};

const BlockView = ( { items } ) => {
	const blockViewItem = ( { label, value } ) => (
		<li key={ label }>
			<b>{ label }</b>
			<span>{ value }</span>
		</li>
	);

	return <ul>{ items.map( blockViewItem ) }</ul>;
};

export default function Edit( { className, setAttributes, attributes } ) {
	const {
		languageLabels,
		videoCaptionLanguages,
		videoLanguage,
		duration,
	} = attributes;

	/**
	 * Transform locale object into list of { label: 'English, value: 'en' }
	 */
	const labelValueList = useMemo( () => {
		return Object.keys( languageLabels ).map(
			( i ) => {
				return {
					label: languageLabels[ i ],
					value: i,
				};
			},
			[ languageLabels ]
		);
	} );

	/**
	 * Transform list of locales into list of display names
	 */
	const captions = videoCaptionLanguages.map( ( i ) => languageLabels[ i ] );

	return (
		<div className={ className }>
			<BlockView
				items={ [
					{
						label: strings.duration,
						value: getDurationDisplay( duration ),
					},
					{
						label: strings.language,
						value: languageLabels[ videoLanguage ],
					},
					{
						label: strings.captions,
						value: captions.join( ', ' ),
					},
				] }
			/>
			<InspectorControls>
				<PanelBody title={ strings.language } initialOpen={ true }>
					<SelectControl
						value={ videoLanguage }
						options={ labelValueList }
						onChange={ ( newValue ) =>
							setAttributes( {
								videoLanguage: newValue,
							} )
						}
					/>
				</PanelBody>
				<PanelBody title={ strings.captions } initialOpen={ true }>
					<CaptionsControl
						label={ strings.searchCaptions }
						options={ labelValueList }
						tokens={ captions }
						onChange={ ( newList ) => {
							/**
							 * Get the locales from a list of display names
							 */
							const locales = labelValueList
								.filter( ( i ) => newList.includes( i.label ) )
								.map( ( i ) => i.value );

							setAttributes( {
								videoCaptionLanguages: locales,
							} );
						} }
					/>
				</PanelBody>
				<PanelBody title={ strings.duration }>
					<DurationControl
						duration={ duration }
						onChange={ ( timeInSeconds ) => {
							setAttributes( {
								duration: timeInSeconds,
							} );
						} }
					/>
				</PanelBody>
			</InspectorControls>
		</div>
	);
}
