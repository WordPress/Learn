import { Icon } from '@wordpress/icons';
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import NumberControl from '../../sensei/number-control';
import QuizTimerIcon from './quiz-timer-icon';
import { Timer } from './components/Timer';

const DEFAULT_VALUE = 10;

/**
 * Quiz timer editor component.
 *
 * @param {Object} props
 * @param {Object} props.attributes
 * @param {Function} props.setAttributes
 * @param {number|null} props.attributes.time Timer in seconds, or null if not set.
 */
export function QuizTimerEdit( { attributes: { time }, setAttributes } ) {
	const minutes = time ? Math.floor( time / 60 ) : 0;

	const setTime = ( value ) =>
		setAttributes( { time: value ? value * 60 : value } );

	const hasValue = null !== time;

	let controls;

	if ( hasValue ) {
		controls = (
			<>
				<div className="sensei-lms-quiz-timer__controls sensei-lms-quiz-timer__controls--circular">
					<div className="sensei-lms-quiz-timer__label-wrapper">
						<div className="sensei-lms-quiz-timer__label">
							{ __( 'Quiz time limit', 'sensei-pro' ) }
						</div>
						<Button
							className="sensei-lms-quiz-timer__remove"
							isLink
							label={ __(
								'Remove quiz time timit',
								'sensei-pro'
							) }
							onClick={ () => setTime( null ) }
						>
							{ __( 'Remove', 'sensei-pro' ) }
						</Button>
					</div>
					<NumberControl
						className="sensei-lms-quiz-timer__input"
						min="0"
						value={ minutes }
						onChange={ setTime }
						suffix={ __( 'minutes', 'sensei-pro' ) }
					/>
				</div>

				<div className="sensei-lms-quiz-timer__preview">
					<Timer
						time={ time }
						timeLeft={ time }
						isPreviewMode={ false }
					/>
				</div>
			</>
		);
	} else {
		controls = (
			<Button
				className="sensei-lms-quiz-timer__controls sensei-lms-quiz-timer__controls__add-timer"
				onClick={ () => setTime( DEFAULT_VALUE ) }
			>
				<span className="sensei-lms-quiz-timer__icon">
					<Icon icon={ <QuizTimerIcon /> } />
				</span>
				<div className="sensei-lms-quiz-timer__label">
					{ __( 'Add quiz time limit', 'sensei-pro' ) }
				</div>
			</Button>
		);
	}

	return <div className="sensei-lms-quiz-timer">{ controls }</div>;
}
