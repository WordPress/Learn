/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';
import { RichText, useBlockProps } from '@wordpress/block-editor';
import { Button, ComboboxControl, Icon, Spinner } from '@wordpress/components';
import { check } from '@wordpress/icons';
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
		const { lessonPlanId, lessonPlanContent, lessonPlanTitle } = attributes;
		const [ searchResults, setSearchResults ] = useState( [] );
		const [ isExpanded, setIsExpanded ] = useState( false );
		const [ isSaving, setIsSaving ] = useState( false );
		const [ saveSuccess, setSaveSuccess ] = useState( null );
		const [ errorMessage, setErrorMessage ] = useState( null );

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
				setAttributes( {
					lessonPlanContent: cleanedContent,
					lessonPlanTitle: plan.title.rendered,
				} );
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
			setIsSaving( true );
			setSaveSuccess( null );
			apiFetch( {
				path: `/wp/v2/lesson-plan/${ lessonPlanId }`,
				method: 'POST',
				data: {
					content: lessonPlanContent,
					title: lessonPlanTitle,
				},
			} )
				.then( () => {
					setIsSaving( false );
					setSaveSuccess( true );
					setTimeout( () => setSaveSuccess( null ), 2000 );
				} )
				.catch( ( error ) => {
					setIsSaving( false );
					setSaveSuccess( false );
					setErrorMessage( error.message || __( 'An error occurred while saving', 'wporg-learn' ) );
				} );
		};

		const getSaveButton = () => {
			if ( isSaving ) {
				return <Spinner />;
			}
			if ( saveSuccess === true ) {
				return <Icon icon={ check } />;
			}
			if ( saveSuccess === false ) {
				return errorMessage;
			}
			return __( 'Save Changes', 'wporg-learn' );
		};

		const getSaveButtonClassName = () => {
			if ( saveSuccess === true ) {
				return 'is-success';
			}
			if ( saveSuccess === false ) {
				return 'is-failure';
			}
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
						<RichText
							tagName="h1"
							value={ lessonPlanTitle }
							onChange={ ( newTitle ) => setAttributes( { lessonPlanTitle: newTitle } ) }
						/>
						<Button variant="secondary" onClick={ () => setIsExpanded( ! isExpanded ) }>
							{ isExpanded
								? __( 'Collapse Content', 'wporg-learn' )
								: __( 'Expand Content', 'wporg-learn' ) }
						</Button>
						<RichText
							className={ isExpanded ? 'is-expanded' : 'is-collapsed' }
							value={ lessonPlanContent }
							onChange={ ( newContent ) => setAttributes( { lessonPlanContent: newContent } ) }
						/>
					</>
				) }
				{ lessonPlanId && (
					<Button
						variant="primary"
						onClick={ saveLessonPlanContent }
						disabled={ isSaving }
						className={ getSaveButtonClassName() }
					>
						{ getSaveButton() }
					</Button>
				) }
			</div>
		);
	},
	save: () => null,
} );
