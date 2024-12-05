/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';
import { RichText, useBlockProps } from '@wordpress/block-editor';
import { Button, ComboboxControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { debounce } from 'lodash';

/**
 * Internal dependencies
 */
import metadata from './block.json';
import './style.scss';

registerBlockType( metadata.name, {
	edit: function Edit( { attributes, setAttributes } ) {
		const { lessonPlanId, lessonPlanContent } = attributes;
		const [ searchResults, setSearchResults ] = useState( [] );

		const fetchLessonPlanContent = ( id ) => {
			apiFetch( { path: `/wp/v2/lesson-plan/${ id }` } ).then( ( plan ) => {
				const cleanedContent = plan.content.rendered.replace( /\s+/g, ' ' ).trim();
				setAttributes( { lessonPlanContent: cleanedContent } );
			} );
		};

		const fetchLessonPlans = debounce( ( searchTerm ) => {
			apiFetch( {
				path: `/wp/v2/lesson-plan?search=${ encodeURIComponent( searchTerm ) }&per_page=10`,
			} ).then( ( plans ) => {
				const options = plans.map( ( plan ) => ( {
					value: plan.id,
					label: plan.title.rendered,
				} ) );
				setSearchResults( options );
			} );
		}, 300 );

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
				<ComboboxControl
					label={ __( 'Select Lesson Plan', 'wporg-learn' ) }
					value={ lessonPlanId || '' }
					options={ searchResults }
					onFilterValueChange={ ( inputValue ) => {
						if ( inputValue ) {
							fetchLessonPlans( inputValue );
						} else {
							setSearchResults( [] );
						}
					} }
					onChange={ ( newValue ) => {
						setAttributes( { lessonPlanId: newValue } );
						if ( newValue ) {
							fetchLessonPlanContent( newValue );
						}
					} }
					placeholder={ __( 'Search for a lesson plan…', 'wporg-learn' ) }
				/>
				{ lessonPlanId && (
					<RichText
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
