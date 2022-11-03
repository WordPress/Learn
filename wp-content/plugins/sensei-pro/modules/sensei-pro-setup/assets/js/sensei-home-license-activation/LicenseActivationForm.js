/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { Button, Spinner } from '@wordpress/components';
import { Icon, check, external } from '@wordpress/icons';

const LicenseActivationForm = () => {
	const [ isFetching, setIsFetching ] = useState( false );
	const [ success, setSuccess ] = useState( null );
	const [ errorMessage, setErrorMessage ] = useState( null );
	const [ currentLicenseKey, setCurrentLicenseKey ] = useState(
		window.senseiHomeLicenseActivation.licenseKey
	);
	const pluginName =
		window.senseiHomeLicenseActivation.pluginSlug === 'sensei-pro'
			? __( 'Sensei Pro', 'sensei-pro' )
			: __( 'Sensei Blocks', 'sensei-pro' );

	const handleSubmit = () => {
		setIsFetching( true );
		apiFetch( {
			path: '/sensei-pro-internal/v1/sensei-pro-setup/activate-license',
			method: 'POST',
			data: {
				license_key: currentLicenseKey,
				plugin_slug: window.senseiHomeLicenseActivation.pluginSlug,
			},
		} ).then( ( response ) => {
			setIsFetching( false );
			if ( response.success ) {
				setSuccess( true );
				setErrorMessage( null );
			} else {
				setSuccess( false );
				setErrorMessage( response.message );
			}
		} );
	};

	const title = success
		? sprintf(
				/* translators: %s: Name of the plugin that has been activated */
				__( '%s successfully activated', 'sensei-pro' ),
				pluginName
		  )
		: /* translators: %s: Name of the plugin to be activated */
		  sprintf( __( 'Activate %s', 'sensei-pro' ), pluginName );

	return (
		<div className="sensei-pro-sensei-home-license-activation">
			<div className="sensei-pro-sensei-home-license-activation__title">
				{ success && <Icon icon={ check } /> }
				{ title }
			</div>
			<div className="sensei-pro-sensei-home-license-activation__content">
				{ __(
					'You can find your key by logging in to your SenseiLMS.com account',
					'sensei-pro'
				) }{ ' ' }
				<a
					href="https://senseilms.com/my-account/"
					target="_blank"
					rel="noreferrer"
				>
					{ __( 'here', 'sensei-pro' ) } <Icon icon={ external } />
				</a>
			</div>
			{ ! success && (
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
					/>
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
				</div>
			) }
			{ errorMessage && (
				<div className="sensei-pro-sensei-home-license-activation__error">
					{ errorMessage }
				</div>
			) }
		</div>
	);
};

export default LicenseActivationForm;
