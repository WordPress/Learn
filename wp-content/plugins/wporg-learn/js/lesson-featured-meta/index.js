/**
 * WordPress dependencies
 */
import { CheckboxControl, PanelRow } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';

const FEATURED = 'featured';

const LessonFeaturedMeta = () => {
	const postMetaData = useSelect( ( select ) => select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {} );
	const { editPost } = useDispatch( 'core/editor' );
	const [ lessonFeatured, setLessonFeatured ] = useState( postMetaData?._lesson_featured === FEATURED );

	return (
		<PluginDocumentSettingPanel title={ __( 'Featured Lesson', 'wporg-learn' ) }>
			<PanelRow>
				<CheckboxControl
					label={ __( 'Feature this lesson', 'wporg-learn' ) }
					checked={ lessonFeatured }
					onChange={ ( newLessonFeatured ) => {
						setLessonFeatured( newLessonFeatured );

						editPost( {
							meta: {
								...postMetaData,
								_lesson_featured: newLessonFeatured ? FEATURED : '',
							},
						} );
					} }
				/>
			</PanelRow>
		</PluginDocumentSettingPanel>
	);
};

registerPlugin( 'wporg-learn-lesson-featured-meta', {
	render: LessonFeaturedMeta,
} );
