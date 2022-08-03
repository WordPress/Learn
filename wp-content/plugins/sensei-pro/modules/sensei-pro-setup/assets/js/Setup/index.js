/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { Animate } from '@wordpress/components';

/**
 * Internal dependencies
 */
import '../data';
import { DATA_STORE_NAME } from '../data/constants';
import { Header } from '../Header';
import { ActivateLicense } from '../ActivateLicense';
import { InstallSensei } from '../InstallSensei';

export const Setup = () => {
	const licenseActivated = useSelect(
		( select ) => select( DATA_STORE_NAME ).isLicenseActivated(),
		[]
	);

	const shouldInstallSensei = window.senseiProSetup?.requires_sensei || false;

	return (
		<>
			<Header />
			<ActivateLicense />
			{ licenseActivated && shouldInstallSensei && (
				<Animate type="appear" options={ { origin: 'bottom left' } }>
					{ ( { className } ) => (
						<InstallSensei className={ className } />
					) }
				</Animate>
			) }
		</>
	);
};
