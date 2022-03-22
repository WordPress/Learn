/**
 * External dependencies
 */
import interpolateComponents from '@automattic/interpolate-components';
import classnames from 'classnames';

/**
 * WordPress dependencies.
 */
import { useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	Card,
	CardBody,
	CardHeader,
	Icon,
	ExternalLink,
} from '@wordpress/components';
import { update, warning } from '@wordpress/icons';
import { useDispatch } from '@wordpress/data';

/**
 * Internal dependencies.
 */
import { DATA_STORE_NAME, UNKNOWN_ERROR_MESSAGE } from '../data/constants';
import { Button } from '../components';

export const InstallSenseiForm = ( { inProgress, error, className } ) => {
	const { installSenseiCore } = useDispatch( DATA_STORE_NAME );
	const handleSubmit = useCallback(
		( ev ) => {
			ev.preventDefault();
			installSenseiCore();
		},
		[ installSenseiCore ]
	);
	return (
		<Card
			className={ `${ className } sensei-pro-install-sensei` }
			as="form"
			onSubmit={ handleSubmit }
		>
			<CardHeader isShady>
				<div className="sensei-pro-install-sensei__header">
					<h2 className="sensei-pro-install-sensei__title">
						<Icon
							icon={ inProgress ? update : warning }
							className={ classnames( {
								'sensei-pro-install-sensei__title-icon': true,
								'sensei-pro-install-sensei__title-icon--warning': ! inProgress,
								'sensei-pro-install-sensei__title-icon--installing': inProgress,
							} ) }
						/>
						{ __( 'Install Sensei', 'sensei-pro' ) }
					</h2>
					<p className="sensei-pro-install-sensei__title-note">
						{ __(
							"Looks like you don't have Sensei installed yet. Sensei Pro needs Sensei installed in order to be usable.",
							'sensei-pro'
						) }
					</p>
				</div>
			</CardHeader>
			<CardBody className="sensei-pro-install-sensei__body">
				<Button
					isPrimary
					type="submit"
					disabled={ inProgress }
					inProgress={ inProgress }
				>
					{ __( 'Install Sensei', 'sensei-pro' ) }
				</Button>
				{ inProgress && (
					<p className="sensei-pro-activate__note">
						{ __(
							'Installing… this may take a while.',
							'sensei-pro'
						) }
					</p>
				) }
				{ error && (
					<p className="sensei-pro-activate__fail">
						{ error === UNKNOWN_ERROR_MESSAGE
							? interpolateComponents( {
									mixedString: __(
										'Sensei LMS installation failed. You can try to {{link}}install it manually{{/link}}.',
										'sensei-pro'
									),
									components: {
										link: (
											<ExternalLink
												className="sensei-pro-install-sensei__fail-link"
												href="https://senseilms.com/documentation/getting-started-with-sensei/"
											/>
										),
									},
							  } )
							: error }
					</p>
				) }
			</CardBody>
		</Card>
	);
};
