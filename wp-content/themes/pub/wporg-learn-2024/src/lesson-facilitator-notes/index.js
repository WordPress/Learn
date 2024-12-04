/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';
import { RichText, useBlockProps } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import { Button, SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

/**
 * Internal dependencies
 */
import metadata from './block.json';
import './style.scss';

registerBlockType( metadata.name, {
	edit: function Edit( { attributes, setAttributes } ) {
		const { lessonPlanId, lessonPlanContent } = attributes;

		const lessonPlans = useSelect(
			( select ) => select( 'core' ).getEntityRecords( 'postType', 'lesson-plan', { per_page: -1 } ),
			[]
		);

		const options = lessonPlans
			? lessonPlans.map( ( plan ) => ( {
					value: plan.id,
					label: plan.title.rendered,
			  } ) )
			: [];

		const fetchLessonPlanContent = ( id ) => {
			apiFetch( { path: `/wp/v2/lesson-plan/${ id }` } ).then( ( plan ) => {
				const cleanedContent = plan.content.rendered.replace( /\s+/g, ' ' ).trim();
				setAttributes( { lessonPlanContent: cleanedContent } );
			} );
		};

		const saveLessonPlanContent = () => {
			apiFetch( {
				path: `/wp/v2/lesson-plan/${ lessonPlanId }`,
				method: 'POST',
				data: { content: lessonPlanContent },
			} ).then( () => {
				// Optionally, you can show a success message or update the UI
			} );
		};

		return (
			<div { ...useBlockProps() }>
				<SelectControl
					label={ __( 'Select Lesson Plan', 'wporg-learn' ) }
					value={ lessonPlanId }
					options={ [ { label: __( 'Select a plan', 'wporg-learn' ), value: null }, ...options ] }
					onChange={ ( newValue ) => {
						setAttributes( { lessonPlanId: parseInt( newValue, 10 ) } );
						fetchLessonPlanContent( newValue );
					} }
				/>
				{ lessonPlanId && (
					<RichText
						tagName="div"
						label={ __( 'Edit Lesson Plan Content', 'wporg-learn' ) }
						value={ lessonPlanContent }
						onChange={ ( newContent ) => setAttributes( { lessonPlanContent: newContent } ) }
					/>
				) }
				{ lessonPlanId && (
					<Button variant="primary" onClick={ saveLessonPlanContent }>
						{ __( 'Save Changes', 'wporg-learn' ) }
					</Button>
				) }
			</div>
		);
	},
	save: () => null,
} );
