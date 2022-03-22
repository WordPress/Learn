/**
 * External dependencies.
 */
import classnames from 'classnames';

/**
 * WordPress dependencies.
 */
import { Button as WPButton, Animate } from '@wordpress/components';

export const Button = ( { inProgress = false, className, ...props } ) => (
	<Animate type={ inProgress ? 'loading' : '' }>
		{ ( { className: animatedClassName } ) => (
			<WPButton
				className={ classnames( className, animatedClassName ) }
				{ ...props }
			/>
		) }
	</Animate>
);
