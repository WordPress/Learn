/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Placeholder } from '@wordpress/components';

export default function Edit() {
	return (
		<Placeholder
			label={ __( 'Workshop Application Form', 'wporg-learn' ) }
			instructions={ __(
				'This will render a form on the front end.',
				'wporg-learn'
			) }
		/>
	);
}
