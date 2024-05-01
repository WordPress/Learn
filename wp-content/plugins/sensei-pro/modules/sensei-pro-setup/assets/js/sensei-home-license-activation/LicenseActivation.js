/**
 * Internal dependencies
 */
import DefaultActivationForm from './DefaultActivationForm';
import WpcomActivationForm from './WpcomActivationForm';

const LicenseActivation = () => {
	const isActivated = window.senseiHomeLicenseActivation?.isLicenseActivated;
	const forceShowLicense =
		window.senseiHomeLicenseActivation?.forceShowLicense;
	const isMultisite =
		window.senseiHomeLicenseActivation?.isMultisite === true;
	const hasWpcomSubscription =
		window.senseiHomeLicenseActivation.hasWpcomSubscription === true;

	if (
		( isActivated && ! forceShowLicense ) ||
		( isActivated && hasWpcomSubscription )
	) {
		return null;
	}

	return (
		<div className="sensei-pro-sensei-home-license-activation">
			{ hasWpcomSubscription ? (
				<WpcomActivationForm />
			) : (
				<DefaultActivationForm isMultisite={ isMultisite } />
			) }
		</div>
	);
};

export default LicenseActivation;
