/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Placeholder } from '@wordpress/components';

import { useIsBlockInSidebar } from '../../hooks';
import { errors } from '../../constants';

export default function Edit( { clientId } ) {
	const isBlockInWorkshops = useIsBlockInSidebar(
		clientId,
		'wporg-learn-workshops'
	);

	const message = isBlockInWorkshops
		? __(
				'This will be dynamically populated based on settings in the Workshop Details meta box.',
				'wporg-learn'
		  )
		: errors.SIDEBAR_BLOCK_INCOMPATIBLE;

	return (
		<Placeholder label={ __( 'Workshop Details', 'wporg-learn' ) }>
			<p>{ message }</p>
		</Placeholder>
	);
}
