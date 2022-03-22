/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';

const Subtitle = ( { isQuestionSelected } ) => {
	if ( ! isQuestionSelected ) {
		return null;
	}
	return (
		<p className="sensei-lms-question-block__subtitle">
			{ __(
				'Answers will be randomly shuffled on the quiz.',
				'sensei-pro'
			) }
		</p>
	);
};
export default Subtitle;
