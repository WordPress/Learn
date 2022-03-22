/**
 * WordPress dependencies
 */
import { useEffect, useState } from '@wordpress/element';

/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * Internal dependencies
 */
import OrderingAnswerOption from './ordering-answer-option';

/**
 * Default answer options for new blocks.
 */
const DEFAULT_ANSWERS = [ { label: '' }, { label: '' } ];

/**
 * Answer component for question blocks with ordering type.
 *
 * @param {Object}   props
 * @param {Object}   props.attributes
 * @param {Function} props.setAttributes
 * @param {Array}    props.attributes.answers Answers.
 */
const OrderingAnswer = ( props ) => {
	const { setAttributes, hasSelected } = props;

	let {
		attributes: { answers = [] },
	} = props;

	if ( 0 === answers.length ) {
		answers = DEFAULT_ANSWERS;
	}

	const hasDraft = ! answers[ answers.length - 1 ]?.label;

	const displayedAnswers = [ ...answers ];
	if ( hasSelected && ! hasDraft ) {
		displayedAnswers.push( { label: '' } );
	}

	/**
	 * The `correct` property of the answer has a different meaning in
	 * ordering question types and multiple choice question types. If the
	 * question first was of type multiple choice and later converted to
	 * ordering question, then the answers in ordering question end up having
	 * `correct` attribute which we don't want.
	 *
	 * Here we make sure the `correct` properties in answers are never saved
	 * in the database for the ordering question types.
	 */
	useEffect( () => {
		const hasCorrectAttribute = answers.filter( ( answer ) =>
			answer.hasOwnProperty( 'correct' )
		);
		if ( hasCorrectAttribute?.length ) {
			setAttributes( {
				answers: answers.map( ( answer ) => {
					delete answer.correct;
					return answer;
				} ),
			} );
		}
	}, [ answers ] );

	/**
	 * Move an answer in the list.
	 *
	 * @param {number} index     Position of the answer to move.
	 * @param {string} direction Direction to move.
	 */
	const moveAnswer = ( index, direction ) => {
		const newIndex = direction === 'up' ? index - 1 : index + 1;
		if ( newIndex < 0 || newIndex >= answers.length ) {
			return;
		}
		const newAnswers = [ ...answers ];
		const previousAnswer = newAnswers[ newIndex ];
		newAnswers[ newIndex ] = newAnswers[ index ];
		newAnswers[ index ] = previousAnswer;
		setAttributes( { answers: newAnswers } );
	};

	/**
	 * Add a new answer option.
	 *
	 * @param {number} index Answer position
	 */
	const insertAnswer = ( index ) => {
		const nextAnswers = [ ...answers ];
		const newAnswer = { label: '' };
		nextAnswers.splice( index + 1, 0, newAnswer );
		setAttributes( { answers: nextAnswers } );
		setFocus( index + 1 );
	};

	/**
	 * Remove an answer option.
	 *
	 * @param {number} index Answer position
	 */
	const removeAnswer = ( index ) => {
		// Do not allow the user to remove all the answers.
		if ( answers.length === 1 ) {
			return;
		}

		setFocus( index - 1 );
		const nextAnswers = [ ...answers ];
		nextAnswers.splice( index, 1 );
		setAttributes( { answers: nextAnswers } );
	};

	/**
	 * Update answer attributes.
	 *
	 * @param {number} index Answer position
	 * @param {Object} next  Updated answer
	 */
	const updateAnswer = ( index, next ) => {
		{
			const nextAnswers = [ ...answers ];
			nextAnswers[ index ] = { ...nextAnswers[ index ], ...next };
			setAttributes( { answers: nextAnswers } );
		}
	};

	const [ nextFocus, setFocus ] = useState( null );

	let lastNonEmptyAnswer = displayedAnswers.length - 1;

	if ( ! displayedAnswers[ displayedAnswers.length - 1 ]?.label ) {
		lastNonEmptyAnswer = displayedAnswers.length - 2;
	}

	return (
		<OrderingAnswer.Options answers={ displayedAnswers }>
			{ ( answer, index ) => (
				<OrderingAnswerOption
					hasFocus={ index === nextFocus }
					attributes={ answer }
					setAttributes={ ( next ) => updateAnswer( index, next ) }
					onEnter={ () => insertAnswer( index ) }
					onRemove={ () => removeAnswer( index ) }
					moveAnswer={ ( direction ) =>
						moveAnswer( index, direction )
					}
					isFirst={ index === 0 }
					isLast={ index === lastNonEmptyAnswer }
					{ ...{ hasSelected } }
				/>
			) }
		</OrderingAnswer.Options>
	);
};

/**
 * Render a list of answer options.
 *
 * @param {Object}   props
 * @param {Array}    props.answers  Answer list.
 * @param {Function} props.children Answer render function
 */
OrderingAnswer.Options = ( { answers, children } ) => (
	<ol className="sensei-lms-question-block__answer sensei-lms-question-block__answer--ordering">
		{ answers.map( ( answer, index ) => (
			<li
				key={ index }
				className={ classnames(
					'sensei-lms-question-block__answer--ordering__option',
					{ 'is-draft': ! answer.label }
				) }
			>
				{ children( answer, index ) }
			</li>
		) ) }
	</ol>
);

/**
 * Read-only ordering component.
 *
 * @param {Object} props
 * @param {Object} props.attributes
 * @param {Array}  props.attributes.answers Answers.
 */
OrderingAnswer.view = ( { attributes: { answers = [] } } ) => {
	return (
		<OrderingAnswer.Options answers={ answers }>
			{ ( answer ) => <>{ answer.label }</> }
		</OrderingAnswer.Options>
	);
};

export default OrderingAnswer;
