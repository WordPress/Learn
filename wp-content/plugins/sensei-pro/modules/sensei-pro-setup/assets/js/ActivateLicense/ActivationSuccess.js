import { Card, CardBody, CardHeader, Icon } from '@wordpress/components';
import { check } from '@wordpress/icons';
import { __ } from '@wordpress/i18n';

export const ActivationSuccess = ( { licenseKey } ) => {
	return (
		<Card className="sensei-pro-activate">
			<CardHeader isShady>
				<h2 className="sensei-pro-activate__title">
					{ __( 'Your Sensei Pro is activated!', 'sensei-pro' ) }
				</h2>
				<Icon className="sensei-pro-activated__icon" icon={ check } />
			</CardHeader>
			{ licenseKey && (
				<CardBody>
					<p>
						<strong>{ __( 'License Key:', 'sensei-pro' ) }</strong>{ ' ' }
						{ licenseKey }
					</p>
				</CardBody>
			) }
		</Card>
	);
};
