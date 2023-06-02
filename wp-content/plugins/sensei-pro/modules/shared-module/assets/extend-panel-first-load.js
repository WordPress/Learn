/**
 * WordPress dependencies
 */
import { dispatch, select } from '@wordpress/data';

const extendPanelFirstLoad = ( panelName ) => {
	const firstLoadStorageKey = `${ panelName }_first-load`;

	if (
		! window.localStorage.getItem( firstLoadStorageKey ) &&
		! select( 'core/edit-post' ).isEditorPanelOpened( panelName )
	) {
		dispatch( 'core/edit-post' ).toggleEditorPanelOpened( panelName );
		window.localStorage.setItem( firstLoadStorageKey, true );
	}
};

export default extendPanelFirstLoad;
