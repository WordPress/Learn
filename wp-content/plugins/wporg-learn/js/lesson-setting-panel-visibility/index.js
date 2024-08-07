/**
 * WordPress dependencies
 */
import { CheckboxControl, PanelRow } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';

const HIDDEN_TERM_SLUG = 'hidden';
const TAXONOMY_NAME = 'show';

const LessonSettingPanelVisibility = () => {
	const { postTerms, hiddenTermId } = useSelect( ( select ) => {
		const terms = select( 'core/editor' ).getEditedPostAttribute( TAXONOMY_NAME ) || [];
		const allTerms = select( 'core' ).getEntityRecords( 'taxonomy', TAXONOMY_NAME ) || [];
		const hiddenTerm = allTerms.find( ( term ) => term.slug === HIDDEN_TERM_SLUG );

		return {
			postTerms: terms,
			hiddenTermId: hiddenTerm ? hiddenTerm.id : null,
		};
	}, [] );
	const [ isHidden, setIsHidden ] = useState( postTerms.includes( hiddenTermId ) );
	const { editPost } = useDispatch( 'core/editor' );

	const toggleHidden = ( newIsHidden ) => {
		setIsHidden( newIsHidden );

		const newTerms =
			newIsHidden && hiddenTermId
				? [ ...postTerms, hiddenTermId ]
				: postTerms.filter( ( termId ) => termId !== hiddenTermId );

		editPost( { [ TAXONOMY_NAME ]: newTerms } );
	};

	return (
		<PluginDocumentSettingPanel title={ __( 'Hidden Lesson', 'wporg-learn' ) }>
			<PanelRow>
				<CheckboxControl
					label={ __( 'Exclude this lesson from archive and search', 'wporg-learn' ) }
					checked={ isHidden }
					onChange={ toggleHidden }
				/>
			</PanelRow>
		</PluginDocumentSettingPanel>
	);
};

registerPlugin( 'wporg-learn-lesson-visibility-settings', {
	render: LessonSettingPanelVisibility,
} );
