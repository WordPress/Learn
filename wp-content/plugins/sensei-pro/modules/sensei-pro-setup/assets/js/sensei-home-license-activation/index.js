/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks';

/**
 * Internal dependencies
 */
import LicenseActivation from './LicenseActivation';

addFilter( 'sensei.home.top', 'sensei-pro', () => {
	return <LicenseActivation />;
} );
