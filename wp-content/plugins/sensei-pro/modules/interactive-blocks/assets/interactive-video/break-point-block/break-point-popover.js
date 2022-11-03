/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { close as closeIcon, Icon } from '@wordpress/icons';
import { Button } from '@wordpress/components';

/**
 * Break Point editor as a popover component.
 *
 * @param {Object}   props          The component properties.
 * @param {Function} props.onClose  Method to call when closing the popover.
 * @param {Object}   props.children The component children.
 */
const BreakPointPopover = ( { onClose, children, ...props } ) => (
	<div className="sensei-break-point-popover" { ...props }>
		<div className="sensei-break-point-popover__header">
			<button
				className="sensei-break-point-popover__close-button"
				onClick={ onClose }
				aria-label={ __( 'Close', 'sensei-pro' ) }
			>
				<Icon icon={ closeIcon } />
			</button>
		</div>
		<div className="sensei-break-point-popover__content">{ children }</div>
		<div className="sensei-break-point-popover__footer">
			<Button variant="primary" onClick={ onClose }>
				{ __( 'Continue', 'sensei-pro' ) }
			</Button>
		</div>
	</div>
);

export default BreakPointPopover;
