/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks';

/**
 * Internal dependencies
 */
import LicenseActivationForm from './LicenseActivationForm';

addFilter( 'sensei.home.top', 'sensei-pro', () => {
	const isActivated = window.senseiHomeLicenseActivation?.isLicenseActivated;
	const isMultisite =
		window.senseiHomeLicenseActivation?.isMultisite === true;

	if ( isActivated ) {
		return null;
	}

	return <LicenseActivationForm isMultisite={ isMultisite } />;
} );
