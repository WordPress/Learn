import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import './ordering-answer-controls.scss';

export default function OrderingAnswerControls( props ) {
	const {
		moveAnswer,
		hideControls,
		upDisabled = false,
		downDisabled = false,
	} = props;

	if ( hideControls ) {
		return (
			<div className="sensei-lms-question-block__ordering-answer-option__control" />
		);
	}
	return (
		<div className="sensei-lms-question-block__ordering-answer-option__control">
			<Button
				className="sensei-lms-question-block__ordering-answer-option__control-move-up"
				icon="arrow-up-alt2"
				label={ __( 'Move up', 'sensei-beograd' ) }
				onClick={ () => moveAnswer( 'up' ) }
				disabled={ upDisabled }
			/>
			<Button
				className="sensei-lms-question-block__ordering-answer-option__control-move-up"
				icon="arrow-down-alt2"
				label={ __( 'Move down', 'sensei-beograd' ) }
				onClick={ () => moveAnswer( 'down' ) }
				disabled={ downDisabled }
			/>
		</div>
	);
}
