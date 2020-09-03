/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * WordPress dependencies
 */
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components';

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
export default function Edit( { className, setAttributes, attributes } ) {
	const { languageLabels, captionLanguages, videoLanguage } = attributes;

	const toggleCaptionLanguage = ( [ newValue ] ) => {
		let newCaptionList;

		if ( captionLanguages.includes( newValue ) ) {
			// Remove the item
			newCaptionList = captionLanguages.filter( ( i ) => i !== newValue );
		} else {
			// Add the item
			newCaptionList = [ ...captionLanguages, newValue ];
		}

		setAttributes( {
			captionLanguages: newCaptionList,
		} );
	};

	const strings = {
		language: __( 'Language', 'wporg-learn' ),
		captions: __( 'Captions', 'wporg-learn' ),
    };
    
    const [ languageToDisplay ] = languageLabels.filter( i => i.value === videoLanguage );
    const captionsToDisplay = languageLabels.filter( i =>  captionLanguages.includes( i.value )).map( i => i.label );

	return (
		<div className={ className }>
			<ul>
				<li>
					<b>{ strings.language }</b>
					<span>{ languageToDisplay.label }</span>
				</li>
				<li>
					<b>{ strings.captions }</b>
					<span>{ captionsToDisplay.join( ', ' ) }</span>
				</li>
			</ul>

			<InspectorControls>
				<PanelBody
					title={ __( 'Details', 'wporg-learn' ) }
					initialOpen={ true }
				>
					<SelectControl
						label={ strings.language }
						value={ videoLanguage }
						options={ languageLabels }
						onChange={ ( newValue ) =>
							setAttributes( {
								videoLanguage: newValue,
							} )
						}
					/>
					<SelectControl
						label={ strings.captions }
						value={ captionLanguages }
						options={ languageLabels }
						onChange={ toggleCaptionLanguage }
						multiple={ true }
					/>
				</PanelBody>
			</InspectorControls>
		</div>
	);
}
