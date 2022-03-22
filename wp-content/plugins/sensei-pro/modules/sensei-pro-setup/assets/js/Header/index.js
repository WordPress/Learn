/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies.
 */
import { DATA_STORE_NAME } from '../data/constants';
import { Icons } from '../icons';

export const Header = () => {
	const licenseActivation = useSelect( ( select ) => {
		return select( DATA_STORE_NAME ).getLicenseActivate();
	}, [] );
	const needsActivationText = __(
		"Now let's activate Sensei Pro",
		'sensei-pro'
	);
	const activatedText = __( 'Sensei Pro Activated!', 'sensei-pro' );
	return (
		<div className="sensei-pro-setup-header">
			<div className="sensei-pro-setup-header__content">
				<div className="sensei-pro-setup-header__icon">
					<Icons.SenseiTree />
				</div>
				<h1 className="sensei-pro-setup-header__title">
					{ licenseActivation.activated
						? activatedText
						: needsActivationText }
				</h1>
			</div>
		</div>
	);
};
