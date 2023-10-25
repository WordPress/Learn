/**
 * WordPress dependencies
 */
import { createHigherOrderComponent } from '@wordpress/compose';
import { ToolbarButton, ToolbarGroup } from '@wordpress/components';
import { BlockControls } from '@wordpress/block-editor';
import { addFilter } from '@wordpress/hooks';
import { select } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { useCallback } from '@wordpress/element';

/**
 * External dependencies
 */
import { isEmpty } from 'lodash';
/**
 * Internal dependencies
 */
import { MODAL_HASH_ID } from '../config';

const withGenerateNewButton = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		if ( 'sensei-lms/course-outline' !== props.name ) {
			return <BlockEdit { ...props } />;
		}

		const openModal = useCallback( () => {
			window.location.hash = MODAL_HASH_ID;
		}, [] );

		const { getBlock } = select( 'core/block-editor' );
		const block = getBlock( props.clientId );
		const hasLessons = ! isEmpty( block?.innerBlocks );

		return (
			<>
				<BlockEdit { ...props } />

				{ hasLessons && (
					<BlockControls>
						<ToolbarGroup>
							<ToolbarButton
								label={ __(
									'Generate new course outline using ai',
									'sensei-pro'
								) }
								onClick={ openModal }
							>
								{ __( 'Generate new', 'sensei-pro' ) }
							</ToolbarButton>
						</ToolbarGroup>
					</BlockControls>
				) }
			</>
		);
	};
}, 'withGenerateNewButton' );

addFilter(
	'editor.BlockEdit',
	'sensei-pro/tailored-course-outline/withGenerateNewButton',
	withGenerateNewButton
);
