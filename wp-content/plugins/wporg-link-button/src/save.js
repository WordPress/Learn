/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into `post_content`.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#save
 *
 * @return {WPElement} Element to render.
 */
export default function save( { attributes } ) {
	return (
		<div>
			<a class="button button-large" href={ attributes.url }>
				{ __('Join a Group Discussion', 'wporg-learn') }
			</a>
			<p>
				You must agree to our <a href="">Code of Conduct</a> in order to participate.
			</p>
		</div>
	);
}
