/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { render } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';
import { useCopyToClipboard } from '@wordpress/compose';
import { store as noticesStore } from '@wordpress/notices';
import { useDispatch } from '@wordpress/data';
import { EditorSnackbars } from '@wordpress/editor';

/**
 * Internal dependencies
 */
import './style.scss';
import { ReactComponent as CopyIcon } from './copy-icon.svg';

/**
 * Copy Signup Link Button.
 *
 * @param {Object} props
 * @param {string} props.signupLink   The group signup link.
 * @param {string} props.editPageLink The link to edit group signup page.
 */
export const CopySignupLinkButton = ( { signupLink, editPageLink } ) => {
	const { createNotice } = useDispatch( noticesStore );

	const copySuccess = () => {
		createNotice(
			'info',
			__( 'Page link copied to the clipboard.', 'sensei-pro' ),
			{
				isDismissible: true,
				type: 'snackbar',
				actions: [
					{
						url: editPageLink,
						label: __( 'Edit page', 'sensei-pro' ),
						variant: 'primary',
					},
				],
			}
		);
	};

	const ref = useCopyToClipboard( signupLink, copySuccess );

	return (
		<button
			className="sensei-group-copy-signup-link-button"
			type="button"
			ref={ ref }
		>
			{ __( 'Copy invite link', 'sensei-pro' ) }
			<CopyIcon className="sensei-group-copy-signup-link-button__icon" />
		</button>
	);
};

domReady( () => {
	Array.from(
		document.getElementsByClassName(
			'sensei-group-copy-signup-link-container'
		)
	).forEach( ( actionMenu ) => {
		render(
			<CopySignupLinkButton { ...actionMenu?.dataset } />,
			actionMenu
		);
	} );

	// Enable Snackbars.
	const wrapper = document.createElement( 'div' );
	document
		.querySelector( '#wpfooter' )
		.insertAdjacentElement( 'afterbegin', wrapper );
	render( <EditorSnackbars />, wrapper );
} );
