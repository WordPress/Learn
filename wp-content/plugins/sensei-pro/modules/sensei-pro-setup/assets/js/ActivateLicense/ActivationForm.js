/**
 * External dependencies
 */
import interpolateComponents from '@automattic/interpolate-components';

/**
 * WordPress dependencies.
 */
import { useCallback, useState } from '@wordpress/element';
import {
	TextControl,
	Card,
	CardHeader,
	CardFooter,
	CardBody,
	ExternalLink,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies.
 */
import { DATA_STORE_NAME } from '../data/constants';
import { Button } from '../components';
import { useDispatch } from '@wordpress/data';

export const ActivationForm = ( { inProgress, error } ) => {
	const [ licenseKey, setLicenseKey ] = useState( '' );
	const { activateLicense } = useDispatch( DATA_STORE_NAME );
	const handleSubmit = useCallback(
		( ev ) => {
			ev.preventDefault();
			activateLicense( { licenseKey } );
		},
		[ licenseKey, activateLicense ]
	);
	return (
		<Card
			className="sensei-pro-activate"
			as="form"
			onSubmit={ handleSubmit }
		>
			<CardHeader isShady>
				<div className="sensei-pro-activate__header">
					<h2 className="sensei-pro-activate__title">
						{ __( 'Activate Sensei Pro', 'sensei-pro' ) }
					</h2>
					<p className="sensei-pro-activate__title-note">
						{ interpolateComponents( {
							mixedString: __(
								'You can find the key in by navigating to your purchases in your SenseiLMS.com {{link}}account{{/link}}.',
								'sensei-pro'
							),
							components: {
								link: (
									<ExternalLink href="https://senseilms.com/my-account" />
								),
							},
						} ) }
					</p>
				</div>
			</CardHeader>

			<CardBody className="sensei-pro-activate__body">
				<TextControl
					className="sensei-pro-activate__license-key"
					label={ __( 'License key', 'sensei-pro' ) }
					required
					onChange={ setLicenseKey }
					value={ licenseKey }
					disabled={ inProgress }
				/>
			</CardBody>

			<CardFooter>
				<Button
					isPrimary
					type="submit"
					disabled={ inProgress }
					inProgress={ inProgress }
				>
					{ __( 'Activate', 'sensei-pro' ) }
				</Button>
				{ error && (
					<p className="sensei-pro-activate__fail">{ error }</p>
				) }
			</CardFooter>
		</Card>
	);
};
