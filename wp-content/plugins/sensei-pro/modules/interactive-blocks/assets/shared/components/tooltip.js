/**
 * External dependencies
 */
import {
	Tooltip as TooltipMessage,
	TooltipReference,
	useTooltipState,
} from 'reakit/Tooltip';

/**
 * Frontend tooltip component. Shows 'message' in a tooltip when hovered or focused.
 *
 * @see https://reakit.io/docs/tooltip/
 *
 * @param {Object}  props
 * @param {*}       props.message   Tooltip content.
 * @param {string}  props.placement Positioning.
 * @param {boolean} props.disabled  Disable showing the tooltip on hover.
 */
export const Tooltip = ( { message, placement, disabled, ...props } ) => {
	const tooltip = useTooltipState( { placement, animated: 300 } );

	if ( disabled ) {
		const Tag = props.as ?? 'div';
		return <Tag { ...props } />;
	}

	return (
		<>
			<TooltipReference { ...tooltip } { ...props } />
			<TooltipMessage className="sensei-lms-tooltip" { ...tooltip }>
				{ message }
			</TooltipMessage>
		</>
	);
};
