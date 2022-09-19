/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Placeholder } from '@wordpress/components';

export default function Edit() {
	return (
		<Placeholder label={ __( 'Lesson Plan Actions', 'wporg-learn' ) }>
			<p>
				{ __(
					'This will be dynamically populated based on media attached to the Lesson Plan.',
					'wporg-learn'
				) }
			</p>
			<p>
				{ __(
					'Please note that it will not be displayed in the sidebar for other content types.',
					'wporg-learn'
				) }
			</p>
		</Placeholder>
	);
}
