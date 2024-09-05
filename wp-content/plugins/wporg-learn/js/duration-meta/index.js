/**
 * WordPress dependencies
 */
import { PanelRow, TextControl } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';

const DurationMeta = () => {
	const postMetaData = useSelect( ( select ) => select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {} );
	const { editPost } = useDispatch( 'core/editor' );
	const [ duration, setDuration ] = useState( postMetaData?._duration );

	return (
		<PluginDocumentSettingPanel title={ __( 'Time to complete', 'wporg-learn' ) }>
			<PanelRow>
				<TextControl
					label={ __( 'Duration in hours', 'wporg-learn' ) }
					value={ duration || '' }
					type={ 'number' }
					onChange={ ( newDuration ) => {
						setDuration( newDuration );

						editPost( {
							meta: {
								...postMetaData,
								_duration: parseFloat( newDuration ),
							},
						} );
					} }
				/>
			</PanelRow>
		</PluginDocumentSettingPanel>
	);
};

registerPlugin( 'wporg-learn-duration-meta', {
	render: DurationMeta,
} );
