/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { forwardRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const classMap = {
	true: 'sensei-lms-flip--flipped-back',
	false: 'sensei-lms-flip--flipped-front',
};

/**
 * Flip Card button
 *
 * @param {Object} props
 * @param {string} props.label Custom flip button label.
 */
const FlipButton = ( { label, ...props } ) => (
	<button { ...props } className="sensei-lms-flip__button" tabIndex={ 0 }>
		{ /* eslint-disable-next-line jsx-a11y/anchor-is-valid -- Interaction provided by button */ }
		<a tabIndex={ -1 }>
			{ label ??
				// translators: verb + noun, refers to an action of flipping a card.
				__( 'Flip Card', 'sensei-pro' ) }
		</a>
	</button>
);

/**
 * Container for a card with front and back sides.
 *
 * @param {Object} props
 * @param {Array}  props.children
 * @param          props.className
 */
export const Flip = forwardRef( ( { children, className, ...props }, ref ) => {
	const [ flipped, setFlipped ] = useState( false );
	const onClick = () => {
		if ( props.setCompleted ) {
			props.setCompleted( true );
		}
		setFlipped( ( side ) => ! side );
	};

	return (
		<div
			ref={ ref }
			{ ...props }
			className={ classnames(
				'sensei-lms-flip',
				classMap[ flipped ],
				className
			) }
		>
			{ children }
			<FlipButton onClick={ onClick } />
		</div>
	);
} );

/**
 * Flip component for save function.
 *
 * @param {Object} props
 * @param {Array}  props.children
 */
Flip.Save = ( { children, ...props } ) => (
	<div { ...props } className={ `sensei-lms-flip ${ classMap.false }` }>
		{ children }
		<FlipButton label="Flip Card" />
	</div>
);

/**
 * Classname for front element.
 */
Flip.front = 'sensei-lms-flip__front';

/**
 * Classname for back element.
 */
Flip.back = 'sensei-lms-flip__back';
