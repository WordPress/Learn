/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { Icons } from '../icons';

export const Header = () => {
	const locales = window.senseiProSetup?.locales || {};

	const title = locales.header?.title || __( 'Setup Wizard', 'sensei-pro' );

	return (
		<div className="sensei-pro-setup-header">
			<div className="sensei-pro-setup-header__content">
				<div className="sensei-pro-setup-header__icon">
					<Icons.SenseiTree />
				</div>
				<h1 className="sensei-pro-setup-header__title">{ title }</h1>
			</div>
		</div>
	);
};
