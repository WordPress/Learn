/* global wporgLearnLocales */
// see render_locales_list() in wp-content/plugins/wporg-learn/inc/post-meta.php

/**
 * WordPress dependencies
 */
import { PanelRow, SelectControl } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';

const locales = wporgLearnLocales;

const LanguageMeta = () => {
	const postMetaData = useSelect(
		( select ) =>
			select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {}
	);
	const { editPost } = useDispatch( 'core/editor' );
	const [ language, setLanguage ] = useState( postMetaData?.language );

	return (
		<PluginDocumentSettingPanel title={ __( 'Language', 'wporg-learn' ) }>
			<PanelRow>
				<SelectControl
					label={ __( 'Language', 'wporg-learn' ) }
					value={ language }
					options={ locales }
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
