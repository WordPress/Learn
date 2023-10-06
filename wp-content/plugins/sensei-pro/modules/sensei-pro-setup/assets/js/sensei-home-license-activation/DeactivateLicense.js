/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { useState, useEffect } from '@wordpress/element';
import { Button, Spinner } from '@wordpress/components';

/**
 * The DeactivateLicense component.
 *
 * @param {Object}   props                   The component props.
 * @param {string}   props.currentLicenseKey The current license key to deactivate
 * @param {boolean}  props.successActivation Whether the activation was successful or not
 * @param {Function} props.reset             Function to call when resetting the activation status
 */
const DeactivateLicense = ( {
	currentLicenseKey,
	successActivation,
	reset,
} ) => {
	const [ success, setSuccess ] = useState( null );
	const [ message, setMessage ] = useState( null );
	const [ isFetching, setIsFetching ] = useState( false );
	const {
		deactivateNonce,
		pluginSlug,
		isLicenseActivated,
	} = window.senseiHomeLicenseActivation;
	const handleSubmit = ( e ) => {
		e.preventDefault();
		setIsFetching( true );
		setSuccess( null );
		setMessage( __( 'Deactivating license…', 'sensei-pro' ) );
		apiFetch( {
			path: '/sensei-pro-internal/v1/sensei-pro-setup/deactivate-license',
			method: 'POST',
			data: {
				license_key: currentLicenseKey,
				plugin_slug: pluginSlug,
				nonce: deactivateNonce,
			},
		} )
			.then( ( response ) => {
				setIsFetching( false );
				if ( response.success ) {
					setSuccess( true );
					setMessage(
						__( 'License deactivated successfully.', 'sensei-pro' )
					);
					reset();
				} else {
					setSuccess( false );
					setMessage( response.message );
				}
			} )
			.catch( () => {
				setIsFetching( false );
				setSuccess( false );
				setMessage(
					__( 'Error while deactivating license', 'sensei-pro' )
				);
			} );
	};
	useEffect( () => {
		if ( successActivation ) {
			setSuccess( null );
			setMessage( null );
		}
	}, [ successActivation ] );
	if ( message && false !== success ) {
		return (
			<div className="sensei-pro-sensei-home-license-activation__message">
				{ isFetching ? <Spinner /> : null }
				{ message }
			</div>
		);
	}
	if ( deactivateNonce && ( isLicenseActivated || successActivation ) ) {
		return (
			<>
				<Button
					onClick={ handleSubmit }
					variant="secondary"
					disabled={ isFetching }
				>
					{ __( 'Deactivate License', 'sensei-pro' ) }
				</Button>
				{ message ? (
					<div className="sensei-pro-sensei-home-license-activation__error">
						{ message }
					</div>
				) : null }
			</>
		);
	}
	return null;
};

export default DeactivateLicense;
