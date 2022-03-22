import {
	Button,
	Card,
	CardBody,
	CardHeader,
	Icon,
} from '@wordpress/components';
import { check } from '@wordpress/icons';
import { __ } from '@wordpress/i18n';

export const InstallSenseiSuccess = ( { activateUrl, activated } ) => {
	if ( activated ) {
		return null;
	}

	return (
		<Card className="sensei-pro-activate">
			<CardHeader isShady>
				<h2 className="sensei-pro-activate__title">
					{ __( 'Sensei is installed!', 'sensei-pro' ) }
				</h2>
				<Icon className="sensei-pro-activated__icon" icon={ check } />
			</CardHeader>
			{ ! activated && activateUrl && (
				<CardBody>
					<Button href={ activateUrl } isPrimary>
						{ __( 'Activate Sensei', 'sensei-pro' ) }
					</Button>
				</CardBody>
			) }
		</Card>
	);
};
