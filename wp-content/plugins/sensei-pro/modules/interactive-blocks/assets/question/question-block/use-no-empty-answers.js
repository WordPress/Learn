/**
 * External dependencies
 */
import editorLifecycle from 'sensei/assets/shared/helpers/editor-lifecycle';

/**
 * WordPress dependencies
 */
import { useDispatch } from '@wordpress/data';
import { useEffect } from '@wordpress/element';

/**
 * A React hook to identify empty answers in multiple-choice and ordering quesitons,
 * and remove them.
 *
 * @param {string}   type          Question type.
 * @param {Object}   answer        Answer attribute, containing all the answer options.
 * @param {Function} setAttributes Set block attributes function.
 */
const useNoEmptyAnswers = ( type, answer, setAttributes ) => {
	const { savePost } = useDispatch( 'core/editor' );

	useEffect( () => {
		const unsubscribe = editorLifecycle( {
			onSave: () => {
				if ( [ 'multiple-choice', 'ordering' ].includes( type ) ) {
					const hasEmptyAnswer = answer?.answers?.some(
						( item ) => item.label.trim() === ''
					);

					if ( hasEmptyAnswer ) {
						setAttributes( {
							answer: {
								...answer,
								answers: answer.answers.filter(
									( item ) => item.label.trim() !== ''
								),
							},
						} );

						savePost();
					}
				}
			},
		} );

		return unsubscribe;
	} );
};

export default useNoEmptyAnswers;
