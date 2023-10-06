/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import {
	createInterpolateElement,
	useState,
	useMemo,
} from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { Button, ExternalLink, Spinner } from '@wordpress/components';
import { Icon, check } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import DeactivateLicense from './DeactivateLicense';

const LicenseActivationForm = ( { isMultisite } ) => {
	const [ isFetching, setIsFetching ] = useState( false );
	const [ success, setSuccess ] = useState( null );
	const [ errorMessage, setErrorMessage ] = useState( null );
	const [ currentLicenseKey, setCurrentLicenseKey ] = useState(
		window.senseiHomeLicenseActivation.licenseKey
	);

	const [ isActivated, setIsActivated ] = useState(
		window.senseiHomeLicenseActivation.isLicenseActivated
	);
	const pluginName =
		window.senseiHomeLicenseActivation.pluginSlug === 'sensei-pro'
			? __( 'Sensei Pro', 'sensei-pro' )
			: __( 'Sensei Blocks', 'sensei-pro' );
	const { domain, forceShowLicense } = window.senseiHomeLicenseActivation;
	const handleSubmit = () => {
		setIsFetching( true );
		apiFetch( {
			path: '/sensei-pro-internal/v1/sensei-pro-setup/activate-license',
			method: 'POST',
			data: {
				license_key: currentLicenseKey,
				plugin_slug: window.senseiHomeLicenseActivation.pluginSlug,
			},
		} )
			.then( ( response ) => {
				setIsFetching( false );
				if ( response.success ) {
					setSuccess( true );
					setIsActivated( true );
					setErrorMessage( null );
				} else {
					setSuccess( false );
					setErrorMessage( response.message );
				}
			} )
			.catch( () => {
				setIsFetching( false );
				setSuccess( false );
				setErrorMessage(
					__( 'Error while activating license', 'sensei-pro' )
				);
			} );
	};
	const reset = () => {
		setCurrentLicenseKey( '' );
		setSuccess( null );
		setErrorMessage( null );
		setIsActivated( false );
	};

	const title = useMemo( () => {
		let result = sprintf(
			/* translators: %s: Name of the plugin that has been activated */
			__( '%s successfully activated', 'sensei-pro' ),
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
		<div className="sensei-pro-sensei-home-license-activation">
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
				reset={ reset }
			/>
		</div>
	);
};

export default LicenseActivationForm;
