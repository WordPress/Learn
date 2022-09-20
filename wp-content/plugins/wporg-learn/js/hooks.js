/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';

export const useIsBlockCompatibleWithSidebar = ( attributes, sidebarName ) => {
	let widgets = {};

	useSelect( ( select ) => {
		widgets = select( 'core/edit-widgets' ).getWidgets();
	} );

	const widgetId = attributes.__internalWidgetId;

	// __internalWidgetId is not stable.
	// If it is undefined we can't look up the widget sidebar type to check compatibility
	// so we have to return true and let the backend validation handle the incompatible type.
	if ( widgetId === undefined ) {
		return true;
	}

	const widget = Object.values( widgets ).find(
		( { id } ) => id === widgetId
	);

	return widget?.sidebar === sidebarName;
};
