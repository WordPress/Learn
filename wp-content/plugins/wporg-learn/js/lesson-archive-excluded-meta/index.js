/**
 * WordPress dependencies
 */
import { CheckboxControl, PanelRow } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';

const EXCLUDED = 'excluded';

const LessonArchiveExcludedMeta = () => {
	const postMetaData = useSelect( ( select ) => select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {} );
	const { editPost } = useDispatch( 'core/editor' );
	const [ lessonExcluded, setLessonFeatured ] = useState( postMetaData?._lesson_archive_excluded === EXCLUDED );

	return (
		<PluginDocumentSettingPanel title={ __( 'Hidden Lesson (deprecated)', 'wporg-learn' ) }>
			<PanelRow>
				<CheckboxControl
					label={ __( 'Exclude this lesson from the archive', 'wporg-learn' ) }
					checked={ lessonExcluded }
					onChange={ ( newLessonExcluded ) => {
						setLessonFeatured( newLessonExcluded );

						editPost( {
							meta: {
								...postMetaData,
								_lesson_archive_excluded: newLessonExcluded ? EXCLUDED : '',
							},
						} );
					} }
				/>
			</PanelRow>
		</PluginDocumentSettingPanel>
	);
};

registerPlugin( 'wporg-learn-lesson-archive-excluded-meta', {
	render: LessonArchiveExcludedMeta,
} );
