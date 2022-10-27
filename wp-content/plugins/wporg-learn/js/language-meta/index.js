/**
 * WordPress dependencies
 */
import { PanelRow, SelectControl } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { useState, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';

const LanguageMeta = () => {
	const postMetaData = useSelect(
		( select ) =>
			select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {}
	);
	const { editPost } = useDispatch( 'core/editor' );
	const [ language, setLanguage ] = useState( postMetaData?.language );
	const anchorRef = useRef();

	return (
		<PluginDocumentSettingPanel
			title={ __( 'Language', 'wporg-learn' ) }
			initialOpen="true"
		>
			<PanelRow className="edit-post-post-schedule" ref={ anchorRef }>
				<SelectControl
					label="Language"
					value={ language }
					options={ [
						{ label: 'English (US)', value: 'en_US' },
						{ label: 'English (UK)', value: 'en_GB' },
						{ label: 'Esperanto', value: 'eo' },
					] }
					onChange={ ( newLanguage ) => {
						setLanguage( newLanguage );

						editPost( {
							meta: {
								...postMetaData,
								language: newLanguage,
							},
						} );
					} }
				/>
			</PanelRow>
		</PluginDocumentSettingPanel>
	);
};

registerPlugin( 'wporg-learn-language-meta', {
	render: LanguageMeta,
} );
