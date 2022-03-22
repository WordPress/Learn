/**
 * WordPress dependencies.
 */
import { registerPlugin } from '@wordpress/plugins';
import { select, dispatch } from '@wordpress/data';

/**
 * Internal dependencies.
 */
import CoursePricingSidebar from './course-pricing-sidebar';

registerPlugin( 'sensei-wc-paid-courses-pricing-sidebar-plugin', {
	render: () => <CoursePricingSidebar />,
	icon: null,
} );

/**
 * Set the default pricing panel state to open.
 */
( () => {
	const panelName =
		'sensei-wc-paid-courses-pricing-sidebar-plugin/sensei-wcpc-pricing';
	const firstLoadStorageKey = `${ panelName }_first-load`;

	if (
		! window.localStorage.getItem( firstLoadStorageKey ) &&
		! select( 'core/edit-post' ).isEditorPanelOpened( panelName )
	) {
		dispatch( 'core/edit-post' ).toggleEditorPanelOpened( panelName );
		window.localStorage.setItem( firstLoadStorageKey, true );
	}
} )();
