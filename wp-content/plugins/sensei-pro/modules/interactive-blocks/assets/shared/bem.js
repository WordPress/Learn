/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { forwardRef } from '@wordpress/element';

/**
 * Merge component props.
 *
 * @param {Object} baseProps
 * @param {Object} props
 * @return {Object} Merged props.
 */
function mergeProps( baseProps = {}, props = {} ) {
	const mergedProps = {
		...baseProps,
		...props,
	};

	if ( props.className && baseProps.className ) {
		mergedProps.className = classnames(
			baseProps.className,
			props.className
		);
	}

	return mergedProps;
}

/**
 * Helper to create elemental components.
 *
 * @param {Object} baseProps
 */
export const createBemComponent = ( baseProps ) => {
	const Component = forwardRef( ( props, ref ) => {
		const { classMod, ...elementProps } = mergeProps( baseProps, props );
		const TagName = elementProps.as ?? 'div';
		if ( classMod ) {
			elementProps.className = classnames(
				elementProps.className,
				Component.bem( `--${ classMod }` )
			);
		}
		return <TagName ref={ ref } { ...elementProps } />;
	} );

	/**
	 * Generate a derived class name.
	 *
	 * @param {string} className Class name addon.
	 * @return {string} Generated class name.
	 */
	Component.bem = ( className ) =>
		[ baseProps.className, className ].join( '' );

	return Component;
};
