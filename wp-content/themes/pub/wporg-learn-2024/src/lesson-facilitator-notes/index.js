/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';
import { RichText, useBlockProps } from '@wordpress/block-editor';
import { Button, ComboboxControl } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
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
		const [ isExpanded, setIsExpanded ] = useState( false );

		useEffect( () => {
			// Fetch the initial lesson plan options if lessonPlanId is set
			if ( lessonPlanId ) {
				apiFetch( {
					path: `/wp/v2/lesson-plan/${ lessonPlanId }`,
				} ).then( ( plan ) => {
					setSearchResults( [
						{
							value: plan.id,
							label: plan.title.rendered,
						},
					] );
				} );
			}
		}, [] );

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
				if ( plans.length === 0 ) {
					return;
				}
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
					<>
						<Button variant="secondary" onClick={ () => setIsExpanded( ! isExpanded ) }>
							{ isExpanded
								? __( 'Collapse Content', 'wporg-learn' )
								: __( 'Expand Content', 'wporg-learn' ) }
						</Button>
						<div
							style={ {
								maxHeight: isExpanded ? 'none' : '100px', // Adjust the height as needed
								overflow: 'hidden',
								transition: 'max-height 0.3s ease',
							} }
						>
							<RichText
								label={ __( 'Edit Lesson Plan Content', 'wporg-learn' ) }
								value={ lessonPlanContent }
								onChange={ ( newContent ) => setAttributes( { lessonPlanContent: newContent } ) }
							/>
						</div>
					</>
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
