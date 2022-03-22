import { render } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';

import Question from './Question';

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
