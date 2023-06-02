/**
 * Internal dependencies
 */
import '../data';
import { Header } from '../Header';
import { ActivateLicense } from '../ActivateLicense';
import { InstallSensei } from '../InstallSensei';

export const Setup = () => {
	const hasSensei = window.senseiProSetup?.senseiActivated || false;

	const hasSenseiHome = window.senseiProSetup?.hasSenseiHome || false;

	if ( hasSensei && ! hasSenseiHome ) {
		// If the user is on this page, probably it has a Sensei version older
		// than 4.8.0, so the user has Sensei, but doesn't have Sensei Pro
		// activated, and so we need to show the ActivateLicense form.
		return (
			<>
				<Header />
				<ActivateLicense />
			</>
		);
	}

	return (
		<>
			<Header />
			<InstallSensei />
		</>
	);
};
