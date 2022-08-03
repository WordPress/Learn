/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { forwardRef } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { createBemComponent } from '../shared/bem';

export const Tooltip = createBemComponent( {
	className: 'sensei-lms-image-hotspots__hotspot-tooltip',
} );

/**
 * Tooltip with positioning support.
 *
 * @param {Object} props
 * @param {*}      props.children
 * @param {Object} props.style
 * @param {Object} props.attributes
 */
export const HotSpotTooltip = forwardRef(
	( { children, className, style, attributes: { x, y }, ...props }, ref ) => {
		style = {
			...style,
			'--y': `${ y?.toFixed( 2 ) }%`,
			'--x': `${ x?.toFixed( 2 ) }%`,
		};
		let edge = '';
		switch ( true ) {
			case x < 15:
				edge = 'left';
				break;
			case x > 75:
				edge = 'right';
				break;
		}

		className = classnames( className, edge );

		return (
			<Tooltip { ...props } { ...{ className, style, ref } }>
				<span className={ Tooltip.bem( '-arrow' ) } />
				{ children }
			</Tooltip>
		);
	}
);
