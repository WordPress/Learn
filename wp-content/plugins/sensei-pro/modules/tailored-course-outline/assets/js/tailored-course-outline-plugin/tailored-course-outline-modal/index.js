/**
 * WordPress dependencies
 */
import { Button, Modal, Notice } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * External dependencies
 */
import { useMemo, useState } from 'react';
import { isEmpty, isObject, noop } from 'lodash';
/**
 * Internal dependencies
 */
import useLazyRequest from './use-lazy-request';
import { create as createOutline } from '../services/course-outline';
import { useSelect, useDispatch } from '@wordpress/data';
import {
	Illustration,
	BEM,
	TextAreaWithCounter,
	SkillLevelSelection,
	Loading,
} from './elements';

const parseError = ( error ) => {
	if ( isObject( error ) ) {
		return error.message;
	}

	return error;
};

const TailoredCourseOutlineModal = ( { onClose = noop } ) => {
	const { getEditedPostAttribute } = useSelect( 'core/editor' );
	const meta = getEditedPostAttribute( 'meta' );
	const title = getEditedPostAttribute( 'title' );
	const excerpt = getEditedPostAttribute( 'excerpt' );

	const [ formState, setFormState ] = useState( {
		description: excerpt,
		skillLevel: meta?.sensei_course_skill_level,
		audience: meta?.sensei_course_audience,
	} );

	const { editPost } = useDispatch( 'core/editor' );

	const send = () => {
		editPost( {
			excerpt: formState.description,
			meta: {
				sensei_course_audience: formState.audience,
				sensei_course_skill_level: formState.skillLevel,
			},
		} );

		return createOutline( {
			title,
			description: formState.description,
			audience: formState.audience,
			skillLevel: formState.skillLevel,
		} );
	};

	const {
		isLoading,
		run: generateCourseOutline,
		response,
		error,
		hasError,
	} = useLazyRequest( send, [
		formState.audience,
		formState.description,
		formState.skillLevel,
	] );

	const isInvalid = useMemo( () => {
		return [
			formState.audience,
			formState.skillLevel,
			formState.description,
		].some( isEmpty );
	}, [ formState.audience, formState.skillLevel, formState.description ] );

	if ( response ) {
		onClose( response?.lessons );
	}

	return (
		<Modal onRequestClose={ () => onClose( [] ) } className={ BEM() }>
			<div className={ BEM( { e: 'body' } ) }>
				<Loading isLoading={ isLoading } />
				<div
					className={ BEM( {
						e: 'fields',
					} ) }
				>
					<h2>{ __( 'Tailored Course Outline', 'sensei-pro' ) }</h2>
					<p>
						{ __(
							'Our AI can help you come up with a great course outline so you can just get in and add your own content to it. To get started, tell us about your target audience.',
							'sensei-pro'
						) }
					</p>

					<TextAreaWithCounter
						value={ formState.description }
						label={ __( 'Course Description', 'sensei-pro' ) }
						name="description"
						disabled={ isLoading }
						onChange={ ( value ) =>
							setFormState( {
								...formState,
								description: value,
							} )
						}
					/>

					<SkillLevelSelection
						isLoading={ isLoading }
						name="skill-level"
						onChange={ ( value ) =>
							setFormState( {
								...formState,
								skillLevel: value,
							} )
						}
						value={ formState.skillLevel }
					/>

					<TextAreaWithCounter
						value={ formState.audience }
						label={ __( 'Intended audience', 'sensei-pro' ) }
						name="intended-audience"
						disabled={ isLoading }
						placeholder={ __(
							'Who is the target audience? This is used by AI to generate course content and quiz questions.',
							'sensei-pro'
						) }
						onChange={ ( value ) =>
							setFormState( {
								...formState,
								audience: value,
							} )
						}
					/>
				</div>
				<Illustration />
			</div>

			{ hasError && (
				<Notice
					status="warning"
					isDismissible={ false }
					className={ BEM( { e: 'error' } ) }
				>
					<p>{ parseError( error ) }</p>
				</Notice>
			) }
			<div className={ BEM( { e: 'actions' } ) }>
				<p>
					{ __(
						'Generating a new Course Outline will replace the existing one.',
						'sensei-pro'
					) }
				</p>
				<Button
					disabled={ isInvalid || isLoading }
					onClick={ generateCourseOutline }
					isBusy={ isLoading }
					variant="primary"
				>
					{ __( 'Generate course outline', 'sensei-pro' ) }
				</Button>
			</div>
		</Modal>
	);
};

export default TailoredCourseOutlineModal;
