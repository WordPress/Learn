import { __ } from '@wordpress/i18n';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { ExternalLink } from '@wordpress/components';

/**
 * WooCommerce Prompt Sidebar component.
 */

const Sidebar = () => (
	<PluginDocumentSettingPanel
		name="sensei-pro-woocommerce-prompt"
		title={ __( 'Pricing ', 'sensei-pro' ) }
	>
		<p>
			{ __(
				'WooCommerce is required in order to set pricing for a course.',
				'sensei-pro'
			) }
		</p>
		<p>
			<ExternalLink
				href={
					'plugin-install.php?s=woocommerce&tab=search&type=term&plugin_details=woocommerce'
				}
			>
				{ __( 'Get WooCommerce', 'sensei-pro' ) }
			</ExternalLink>
		</p>
	</PluginDocumentSettingPanel>
);

export default Sidebar;
