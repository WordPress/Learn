import { registerBlockStyle } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

import './style.scss';

registerBlockStyle( 'core/button', {
	name: 'primary',
	label: __( 'Primary', 'wporg-learn' ),
} );

registerBlockStyle( 'core/button', {
	name: 'primary-full-width',
	label: __( 'Primary (Full-width)', 'wporg-learn' ),
} );

registerBlockStyle( 'core/button', {
	name: 'secondary',
	label: __( 'Secondary', 'wporg-learn' ),
} );

registerBlockStyle( 'core/button', {
	name: 'secondary-full-width',
	label: __( 'Secondary (Full-width)', 'wporg-learn' ),
} );
