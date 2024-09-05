/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Placeholder } from '@wordpress/components';

import { useGetCurrentPostType, useIsBlockInSidebar } from '../../hooks';
import { getBlockPlaceholderMessage } from '../../utils';

export default function Edit( { clientId } ) {
	const message = getBlockPlaceholderMessage(
		'wporg_workshop',
		useGetCurrentPostType(),
		useIsBlockInSidebar( clientId, 'wporg-learn-workshops' ),
		__(
			'This will be dynamically populated based on settings in the Workshop Details meta box.',
			'wporg-learn'
		)
	);

	return (
		<Placeholder label={ __( 'Workshop Details', 'wporg-learn' ) }>
			<p>{ message }</p>
		</Placeholder>
	);
}
