/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';

const useActivationSubmit = () => {
	const [ isFetching, setIsFetching ] = useState( false );
	const [ success, setSuccess ] = useState( null );
	const [ errorMessage, setErrorMessage ] = useState( null );

	const [ isActivated, setIsActivated ] = useState(
		window.senseiHomeLicenseActivation.isLicenseActivated
	);

	const submit = ( path, data ) => {
		setIsFetching( true );
		apiFetch( {
			path,
			method: 'POST',
			data,
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
		setSuccess( null );
		setErrorMessage( null );
		setIsActivated( false );
	};

	return {
		submit,
		reset,
		isFetching,
		success,
		errorMessage,
		isActivated,
	};
};

export default useActivationSubmit;
