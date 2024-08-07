/**
 * WordPress dependencies
 */
import { CheckboxControl, PanelRow } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';

const EXCLUDED_TERM_ID = 1; // Replace with the actual term ID for 'excluded'
const TAXONOMY_NAME = 'hidden_from_ui';

const LessonArchiveExcludedTaxonomy = () => {
	const [ isExcluded, setIsExcluded ] = useState( false );

	const postTerms = useSelect(
		( select ) => select( 'core/editor' ).getEditedPostAttribute( TAXONOMY_NAME ) || []
	);

	const { editPost } = useDispatch( 'core/editor' );

	useEffect( () => {
		setIsExcluded( postTerms.includes( EXCLUDED_TERM_ID ) );
	}, [ postTerms ] );

	const toggleExcluded = ( newIsExcluded ) => {
		setIsExcluded( newIsExcluded );

		let newTerms;
		if ( newIsExcluded ) {
			newTerms = [ ...postTerms, EXCLUDED_TERM_ID ];
		} else {
			newTerms = postTerms.filter( ( termId ) => termId !== EXCLUDED_TERM_ID );
		}

		// Update the post's terms
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
	render: LessonArchiveExcludedTaxonomy,
} );
