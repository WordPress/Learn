/**
 * WordPress dependencies
 */
import { registerPlugin } from '@wordpress/plugins';
import {
	PluginPrePublishPanel,
	PluginPostStatusInfo,
} from '@wordpress/edit-post';
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';

const VisibilityPluginPrePublishPanel = () => {
	const VisibilityLabel = (
		<p>
			{ ' ' }
			{ __( 'This page has ', 'sensei-pro' ) }{ ' ' }
			<span className={ 'sensei-block-visibility__text-bold' }>
				{ __( 'hidden content.', 'sensei-pro' ) }
			</span>{ ' ' }
		</p>
	);

	const blocks = useSelect( ( select ) => {
		return select( 'core/block-editor' ).getBlocks();
	} );
	const hasHiddenContent = blocks.some( ( block ) => {
		return (
			block.attributes.senseiVisibility &&
			block.attributes.senseiVisibility?.type !== 'EVERYONE'
		);
	} );

	return (
		hasHiddenContent && (
			<>
				<PluginPrePublishPanel>
					{ VisibilityLabel }
				</PluginPrePublishPanel>
				<PluginPostStatusInfo>{ VisibilityLabel }</PluginPostStatusInfo>
			</>
		)
	);
};

registerPlugin( 'visibility-pre-publish-panel', {
	render: VisibilityPluginPrePublishPanel,
} );
