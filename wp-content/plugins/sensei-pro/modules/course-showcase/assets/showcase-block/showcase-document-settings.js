/**
 * WordPress dependencies
 */
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { ToggleControl, SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import useMeta from './use-meta';
import { CATEGORY_OPTIONS, LANGUAGE_OPTIONS, SITE_LANGUAGE } from './constants';
import { useEffect } from '@wordpress/element';
import extendPanelFirstLoad from '../../../shared-module/assets/extend-panel-first-load';

/**
 * Settings to be displayed in the post tab in the sidebar.
 */
const DocumentSettings = () => {
	const [ isPaid, setIsPaid ] = useMeta( '_is_paid' );
	const [ category, setCategory ] = useMeta( '_category' );
	const [ language, setLanguage ] = useMeta( '_language' );

	useEffect( () => {
		extendPanelFirstLoad(
			'sensei-pro-showcase-plugin/sensei-pro-showcase-settings'
		);
	}, [] );

	return (
		<PluginDocumentSettingPanel
			name="sensei-pro-showcase-settings"
			title={ __( 'Settings', 'sensei-pro' ) }
			className="sensei-pro-showcase-settings"
		>
			<ToggleControl
				checked={ isPaid }
				onChange={ setIsPaid }
				label={ __( 'List course as Paid', 'sensei-pro' ) }
				help={ __(
					"Set if you want the course to be listed on Sensei's Showcase as Free or Paid.",
					'sensei-pro'
				) }
			/>
			<SelectControl
				label={ __( 'Category', 'sensei-pro' ) }
				value={ category }
				options={ CATEGORY_OPTIONS }
				onChange={ setCategory }
			/>
			<SelectControl
				label={ __( 'Language', 'sensei-pro' ) }
				value={ language || SITE_LANGUAGE }
				options={ LANGUAGE_OPTIONS }
				onChange={ setLanguage }
			/>
		</PluginDocumentSettingPanel>
	);
};

export default DocumentSettings;
