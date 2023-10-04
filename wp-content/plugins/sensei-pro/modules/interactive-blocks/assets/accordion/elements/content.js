/**
 * WordPress dependencies
 */
import { forwardRef } from '@wordpress/element';

const Content = ( props, ref ) => {
	const { children, ...otherProps } = props;

	return (
		<div ref={ ref } { ...otherProps }>
			{ children }
		</div>
	);
};

export default forwardRef( Content );
