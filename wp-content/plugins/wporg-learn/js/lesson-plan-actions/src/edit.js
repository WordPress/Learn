/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Placeholder } from '@wordpress/components';

import { useIsBlockInSidebar, useGetCurrentPostType } from '../../hooks';
import { getBlockPlaceholderMessage } from '../../utils';

export default function Edit( { clientId } ) {
	const message = getBlockPlaceholderMessage(
		'lesson-plan',
		useGetCurrentPostType(),
		useIsBlockInSidebar( clientId, 'wporg-learn-lesson-plans' ),
		__(
			'This will be dynamically populated based on media attached to the Lesson Plan.',
			'wporg-learn'
		)
	);

	return (
		<Placeholder label={ __( 'Lesson Plan Actions', 'wporg-learn' ) }>
			<p>{ message }</p>
		</Placeholder>
	);
}
