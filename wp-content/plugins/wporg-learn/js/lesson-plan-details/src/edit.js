/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Placeholder } from '@wordpress/components';

import { useIsBlockCompatibleWithSidebar } from '../../hooks';
import { errors } from '../../constants';

export default function Edit( { attributes } ) {
	const isBlockCompatibleWithSidebar = useIsBlockCompatibleWithSidebar(
		attributes,
		'wporg-learn-lesson-plans'
	);

	const message = isBlockCompatibleWithSidebar
		? __(
				'This will be dynamically populated based on settings in the Lesson Plan Details meta box.',
				'wporg-learn'
		  )
		: errors.SIDEBAR_BLOCK_INCOMPATIBLE;

	return (
		<Placeholder label={ __( 'Lesson Plan Details', 'wporg-learn' ) }>
			<p>{ message }</p>
		</Placeholder>
	);
}
