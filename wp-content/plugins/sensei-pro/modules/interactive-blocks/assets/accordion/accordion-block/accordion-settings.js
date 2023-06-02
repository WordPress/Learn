/**
 * WordPress dependencies
 */
import { InspectorControls } from '@wordpress/block-editor';
import {
	Panel,
	PanelBody,
	PanelRow,
	ToggleControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const Settings = ( props ) => {
	const { attributes, setAttributes } = props;
	const { autoClose } = attributes;

	return (
		<InspectorControls>
			<Panel initialOpen={ true }>
				<PanelBody title={ __( 'Settings', 'sensei-pro' ) }>
					<PanelRow>
						<ToggleControl
							label={ __( 'Auto Close', 'sensei-pro' ) }
							checked={ autoClose }
							onChange={ ( value ) =>
								setAttributes( { autoClose: value } )
							}
							help={ __(
								'Open one section at a time on your published page',
								'sensei-pro'
							) }
						/>
					</PanelRow>
				</PanelBody>
			</Panel>
		</InspectorControls>
	);
};

export default Settings;
