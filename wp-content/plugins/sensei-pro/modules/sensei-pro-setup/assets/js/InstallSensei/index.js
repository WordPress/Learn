/**
 * WordPress dependencies.
 */
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies.
 */
import { DATA_STORE_NAME } from '../data/constants';
import { InstallSenseiSuccess } from './InstallSenseiSuccess';
import { InstallSenseiForm } from './InstallSenseiForm';

export const InstallSensei = ( props ) => {
	const senseiInstallation = useSelect( ( select ) => {
		return select( DATA_STORE_NAME ).getSenseiInstall();
	}, [] );

	if ( senseiInstallation.installed ) {
		return <InstallSenseiSuccess { ...props } { ...senseiInstallation } />;
	}
	return <InstallSenseiForm { ...props } { ...senseiInstallation } />;
};
