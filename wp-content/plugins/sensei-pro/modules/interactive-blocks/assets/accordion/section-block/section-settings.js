/**
 * WordPress dependencies
 */
import {
	PanelBody,
	Panel,
	PanelRow,
	ToggleControl,
} from '@wordpress/components';
import { InspectorControls } from '@wordpress/block-editor';

import { __ } from '@wordpress/i18n';
import { useContext, useEffect } from '@wordpress/element';
/**
 * Internal dependencies
 */
import { AccordionContext } from '../elements/accordion';

const DISABLED_HELP_TEXT = __(
	'The section cannot be set to open by default as long as the Accordion is marked as required content or the auto close feature is enabled.',
	'sensei-pro'
);

const HELP_TEXT = __(
	"Show the expanded 'open' view of this section by default.",
	'sensei-pro'
);

const Settings = ( props ) => {
	const { openOnLoad, setOpenOnLoad } = props;

	const { isRequired, autoClose } = useContext( AccordionContext );
	const isDisabled = isRequired || autoClose;

	useEffect( () => {
		if ( isDisabled ) {
			setOpenOnLoad( false );
		}
	}, [ isDisabled, setOpenOnLoad ] );

	return (
		<InspectorControls>
			<Panel initialOpen={ true }>
				<PanelBody title={ __( 'Settings', 'sensei-pro' ) }>
					<PanelRow>
						<ToggleControl
							label="Open by Default"
							checked={ openOnLoad }
							disabled={ isDisabled }
							onChange={ ( value ) => setOpenOnLoad( value ) }
							help={ isDisabled ? DISABLED_HELP_TEXT : HELP_TEXT }
						/>
					</PanelRow>
				</PanelBody>
			</Panel>
		</InspectorControls>
	);
};

export default Settings;
