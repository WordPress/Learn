/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import {
	createInterpolateElement,
	useState,
	useMemo,
} from '@wordpress/element';
import { Button, ExternalLink, Spinner } from '@wordpress/components';
import { Icon, check } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import DeactivateLicense from './DeactivateLicense';
import useActivationSubmit from './useActivationSubmit';

const DefaultActivationForm = ( { isMultisite } ) => {
	const {
		submit,
		reset,
		isFetching,
		success,
		errorMessage,
		isActivated,
	} = useActivationSubmit();
	const [ currentLicenseKey, setCurrentLicenseKey ] = useState(
		window.senseiHomeLicenseActivation.licenseKey
	);

	const pluginName =
		window.senseiHomeLicenseActivation.pluginSlug === 'sensei-pro'
			? __( 'Sensei Pro', 'sensei-pro' )
			: __( 'Sensei Blocks', 'sensei-pro' );
	const { domain, forceShowLicense } = window.senseiHomeLicenseActivation;
	const handleSubmit = () => {
		submit( '/sensei-pro-internal/v1/sensei-pro-setup/activate-license', {
			license_key: currentLicenseKey,
			plugin_slug: window.senseiHomeLicenseActivation.pluginSlug,
			nonce: window.senseiHomeLicenseActivation.formNonce,
		} );
	};
	const handleReset = () => {
		setCurrentLicenseKey( '' );
		reset();
	};

	const title = useMemo( () => {
		let result = sprintf(
			/* translators: %s: Name of the plugin that has been activated */
			__( '%s successfully activated!', 'sensei-pro' ),
			pluginName
		);
		if ( ! success ) {
			result = sprintf(
				/* translators: %s: Name of the plugin to be activated */
				__( 'Activate %s', 'sensei-pro' ),
				pluginName
			);
		}
		if ( forceShowLicense && isActivated ) {
			result = sprintf(
				/* translators: %s: Name of the plugin to be activated */
				__( '%s License Information', 'sensei-pro' ),
				pluginName
			);
		}
		return result;
	}, [ pluginName, success, forceShowLicense, isActivated ] );

	return (
		<>
			<div className="sensei-pro-sensei-home-license-activation__title">
				{ ( success || isActivated ) && <Icon icon={ check } /> }
				{ title }
			</div>

			<div className="sensei-pro-sensei-home-license-activation__content">
				{ __(
					'You can find your key by logging in to your SenseiLMS.com account',
					'sensei-pro'
				) }{ ' ' }
				<ExternalLink href="https://senseilms.com/my-account/">
					{ __( 'here', 'sensei-pro' ) }
				</ExternalLink>
				{ '.' }
			</div>
			<div className="sensei-pro-sensei-home-license-activation__content">
				{ isMultisite &&
					createInterpolateElement(
						sprintf(
							// translators: %s: Name of the plugin being activated.
							__(
								"<strong>Multisite Network</strong>: Main site's domain (%s) will be used for activation and the license will be valid for all the sites on the network.",
								'sensei-pro'
							),
							domain
						),
						{
							strong: <strong />,
						}
					) }
			</div>
			{ ( ! success || forceShowLicense ) && (
				<div className="sensei-pro-sensei-home-license-activation__form">
					<input
						className="sensei-pro-sensei-home-license-activation__form-input"
						value={ currentLicenseKey }
						placeholder={ __(
							'Enter your license key',
							'sensei-pro'
						) }
						onChange={ ( e ) =>
							setCurrentLicenseKey( e.target.value )
						}
						readOnly={ isActivated }
					/>
					{ isActivated ? null : (
						<Button
							className="sensei-pro-sensei-home-license-activation__form-button"
							onClick={ handleSubmit }
							disabled={ isFetching }
							variant="primary"
						>
							{ isFetching ? (
								<Spinner />
							) : (
								__( 'Activate', 'sensei-pro' )
							) }
						</Button>
					) }
				</div>
			) }
			{ errorMessage && (
				<div className="sensei-pro-sensei-home-license-activation__error">
					{ errorMessage }
				</div>
			) }
			<DeactivateLicense
				currentLicenseKey={ currentLicenseKey }
				successActivation={ success }
				reset={ handleReset }
			/>
		</>
	);
};

export default DefaultActivationForm;
