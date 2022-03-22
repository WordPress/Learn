/**
 * WordPress dependencies.
 */
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies.
 */
import { DATA_STORE_NAME } from '../data/constants';
import { ActivationForm } from './ActivationForm';
import { ActivationSuccess } from './ActivationSuccess';

export const ActivateLicense = () => {
	const licenseActivation = useSelect( ( select ) => {
		return select( DATA_STORE_NAME ).getLicenseActivate();
	}, [] );
	if ( licenseActivation.activated ) {
		return <ActivationSuccess { ...licenseActivation } />;
	}
	return <ActivationForm { ...licenseActivation } />;
};
