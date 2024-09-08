/**
 * WordPress dependencies
 */
import { PanelRow, TextControl } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';

const CourseCompletionMeta = () => {
	const postMetaData = useSelect( ( select ) => select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {} );
	const { editPost } = useDispatch( 'core/editor' );

	const message = postMetaData?._course_completion_success_message || '';
	const link = postMetaData?._course_completion_survey_link || '';

	return (
		<PluginDocumentSettingPanel title={ __( 'Course Completion Settings', 'wporg-learn' ) }>
			<PanelRow>
				<TextControl
					label={ __( 'Success Message', 'wporg-learn' ) }
					value={ message }
					onChange={ ( newMessage ) => {
						editPost( {
							meta: {
								...postMetaData,
								_course_completion_success_message: newMessage,
							},
						} );
					} }
				/>
			</PanelRow>
			<PanelRow>
				<TextControl
					label={ __( 'Survey Link', 'wporg-learn' ) }
					value={ link }
					onChange={ ( newLink ) => {
						editPost( {
							meta: {
								...postMetaData,
								_course_completion_survey_link: newLink,
							},
						} );
					} }
				/>
			</PanelRow>
		</PluginDocumentSettingPanel>
	);
};

registerPlugin( 'wporg-learn-course-completion-meta', {
	render: CourseCompletionMeta,
} );
