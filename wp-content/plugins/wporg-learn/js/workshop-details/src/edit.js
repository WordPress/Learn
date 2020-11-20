/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Placeholder } from '@wordpress/components';

export default function Edit() {
	return (
		<Placeholder label={ __( 'Workshop Details', 'wporg-learn' ) }>
			<p>
				{ __(
					'This will be dynamically populated based on settings in the Workshop Details meta box.',
					'wporg-learn'
				) }
			</p>
		</Placeholder>
	);
}
