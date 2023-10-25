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
	const forceShowLicense =
		window.senseiHomeLicenseActivation?.forceShowLicense;
	const isMultisite =
		window.senseiHomeLicenseActivation?.isMultisite === true;

	if ( isActivated && ! forceShowLicense ) {
		return null;
	}

	return <LicenseActivationForm isMultisite={ isMultisite } />;
} );
