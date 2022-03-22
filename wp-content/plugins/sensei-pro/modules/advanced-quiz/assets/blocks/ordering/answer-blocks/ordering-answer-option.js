/**
 * WordPress dependencies
 */
import { useEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import './ordering-answer-option.scss';
import { dragHandle, Icon } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import OrderingAnswerControls from './ordering-answer-controls';
import SingleLineInput from '../../../shared/blocks/single-line-input';

/**
 * Answer option in a ordering type question block.
 *
 * @param {Object}   props
 * @param {Object}   props.attributes       Answer attributes.
 * @param {string}   props.attributes.label Answer title.
 * @param {Function} props.setAttributes    Update answer attributes.
 * @param {Function} props.onEnter          Add a new answer after this.
 * @param {Function} props.onRemove         Remove this answer.
 * @param {boolean}  props.hasFocus         Should this answer receive focus.
 * @param {boolean}  props.hasSelected      Is the question block selected.
 * @param {Function} props.moveAnswer       Move this answer up or down.
 * @param {boolean}  props.isFirst          Whether this option is the first.
 * @param {boolean}  props.isLast           Whether this option is the last.
 */
const OrderingAnswerOption = ( props ) => {
	const {
		attributes: { label },
		setAttributes,
		hasFocus,
		moveAnswer,
		isFirst,
		isLast,
		hasSelected,
		...inputProps
	} = props;

	const ref = useRef( null );

	useEffect( () => {
		if ( hasFocus ) {
			const el = ref.current?.textarea || ref.current;
			el?.focus();
		}
	}, [ hasFocus, ref ] );

	return (
		<div className="sensei-lms-question-block__ordering-answer-option">
			{ hasSelected && (
				<OrderingAnswerControls
					moveAnswer={ moveAnswer }
					hideControls={ ! label }
					upDisabled={ isFirst }
					downDisabled={ isLast }
				/>
			) }
			{ ! hasSelected && (
				<div className="sensei-lms-question-block__ordering-answer-option__drag-placeholder">
					<Icon icon={ dragHandle } size={ 18 } />
				</div>
			) }
			<SingleLineInput
				ref={ ref }
				placeholder={ __( 'Add Answer', 'sensei-pro' ) }
				className="sensei-lms-question-block__ordering-answer-option__input"
				onChange={ ( nextValue ) =>
					setAttributes( { label: nextValue } )
				}
				value={ label }
				{ ...inputProps }
			/>
		</div>
	);
};

export default OrderingAnswerOption;
