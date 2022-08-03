/**
 * External dependencies
 */
import Question from 'shared-module/ordering-question/frontend/Question';

/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';

domReady( () => {
	const questions = window.sensei_ordering_questions;
	const questionIds = Object.keys( questions );

	questionIds.forEach( ( questionId ) => {
		const element = document.getElementById(
			`sensei-ordering-question-${ questionId }`
		);

		if ( questions[ questionId ].question.answers.length > 0 ) {
			render(
				<Question { ...questions[ questionId ].question } />,
				element
			);
		}
	} );
} );
