/**
 * WordPress dependencies
 */
import { CheckboxControl, PanelRow } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';

const EXCLUDED_TERM_SLUG = 'excluded';
const TAXONOMY_NAME = 'hidden_from_ui';

const LessonSettingPanelVisibility = () => {
	const { postTerms, excludedTermId } = useSelect( ( select ) => {
		const terms = select( 'core/editor' ).getEditedPostAttribute( TAXONOMY_NAME ) || [];
		const allTerms = select( 'core' ).getEntityRecords( 'taxonomy', TAXONOMY_NAME ) || [];
		const excludedTerm = allTerms.find( ( term ) => term.slug === EXCLUDED_TERM_SLUG );

		return {
			postTerms: terms,
			excludedTermId: excludedTerm ? excludedTerm.id : null,
		};
	}, [] );
	const [ isExcluded, setIsExcluded ] = useState( postTerms.includes( excludedTermId ) );
	const { editPost } = useDispatch( 'core/editor' );

	const toggleExcluded = ( newIsExcluded ) => {
		setIsExcluded( newIsExcluded );

		const newTerms =
			newIsExcluded && excludedTermId
				? [ ...postTerms, excludedTermId ]
				: postTerms.filter( ( termId ) => termId !== excludedTermId );

		editPost( { [ TAXONOMY_NAME ]: newTerms } );
	};

	return (
		<PluginDocumentSettingPanel title={ __( 'Hidden Lesson', 'wporg-learn' ) }>
			<PanelRow>
				<CheckboxControl
					label={ __( 'Exclude this lesson from the archive', 'wporg-learn' ) }
					checked={ isExcluded }
					onChange={ toggleExcluded }
				/>
			</PanelRow>
		</PluginDocumentSettingPanel>
	);
};

registerPlugin( 'wporg-learn-lesson-archive-excluded-taxonomy', {
	render: LessonSettingPanelVisibility,
} );
