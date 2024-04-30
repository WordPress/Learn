/**
 * WordPress dependencies
 */
import { ComboboxControl, Modal } from '@wordpress/components';
import { createElement, render, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { addQueryArgs } from '@wordpress/url';

/**
 * Internal dependencies
 */
import './style.scss';

const config = window.wporgLocaleSwitcherConfig || {};

const handleChange = ( value ) => {
	const currentLocation = window.location.href;

	window.location = addQueryArgs( currentLocation, { locale: value } );
};

const LocaleSwitcher = ( props ) => {
	const { externalButton } = props;
	const { initialValue, options } = config;
	const [ isOpen, setOpen ] = useState( false );
	const openModal = () => setOpen( true );
	const closeModal = () => setOpen( false );
	const [ value, setValue ] = useState( false );

	externalButton.addEventListener( 'click', ( event ) => {
		event.preventDefault();
		openModal();
	} );

	return (
		<>
			{ isOpen && (
				<Modal
					closeButtonLabel={ __( 'Cancel', 'wporg' ) }
					onRequestClose={ closeModal }
					title={ __( 'Change language', 'wporg' ) }
				>
					<ComboboxControl
						onChange={ ( val ) => {
							setValue( val );
							handleChange( val );
						} }
						onFilterValueChange={ () => {} } // Instead of requiring noop from lodash.
						options={ options }
						value={ value || initialValue }
					/>
				</Modal>
			) }
		</>
	);
};

const initLocaleSwitcher = () => {
	const container = document.getElementById(
		'wporg-locale-switcher-container'
	);
	const externalButton = document.getElementById(
		'wp-admin-bar-locale-switcher'
	);

	const props = {
		externalButton,
	};

	render( createElement( LocaleSwitcher, props ), container );
};

document.addEventListener( 'DOMContentLoaded', initLocaleSwitcher );
