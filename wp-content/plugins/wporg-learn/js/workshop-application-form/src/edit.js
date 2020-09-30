/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import './editor.scss';

export default function Edit( { className } ) {
	return (
		<div className={ className }>
			<p>{ __( 'Workshop Application Form', 'wporg-learn' ) }</p>
			<p>
				{ __(
					'This will render a form on the front end.',
					'wporg-learn'
				) }
			</p>
		</div>
	);
}
