/**
 * WordPress dependencies
 */
import { Button, Spinner } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import useActivationSubmit from './useActivationSubmit';

const WpcomActivationForm = () => {
	const { submit, isFetching, success, errorMessage } = useActivationSubmit();

	const pluginName =
		window.senseiHomeLicenseActivation.pluginSlug === 'sensei-pro'
			? __( 'Sensei Pro', 'sensei-pro' )
			: __( 'Sensei Blocks', 'sensei-pro' );

	const handleSubmit = () => {
		submit(
			'/sensei-pro-internal/v1/sensei-pro-setup/flush-wpcom-license',
			{
				plugin_slug: window.senseiHomeLicenseActivation.pluginSlug,
				nonce: window.senseiHomeLicenseActivation.formNonce,
			}
		);
	};

	if ( success ) {
		return (
			<div className="sensei-pro-sensei-home-license-activation__title">
				{ sprintf(
					/* translators: %s: Name of the plugin that has been activated */
					__( '%s successfully activated!', 'sensei-pro' ),
					pluginName
				) }
			</div>
		);
	}

	return (
		<>
			<div className="sensei-pro-sensei-home-license-activation__title">
				{ sprintf(
					/* translators: %s: Name of the plugin */
					__( 'Activate %s', 'sensei-pro' ),
					pluginName
				) }
			</div>

			<div className="sensei-pro-sensei-home-license-activation__content">
				{ sprintf(
					/* translators: %s: Name of the plugin */
					__(
						'Your %s license is not activated. Click on the following button to activate it now.',
						'sensei-pro'
					),
					pluginName
				) }
			</div>

			{ errorMessage && (
				<div className="sensei-pro-sensei-home-license-activation__error">
					{ errorMessage }
				</div>
			) }

			<div className="sensei-pro-sensei-home-license-activation__form">
				<Button
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
		</>
	);
};

export default WpcomActivationForm;
