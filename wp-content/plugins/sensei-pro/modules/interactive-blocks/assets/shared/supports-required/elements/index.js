/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * Internal dependencies
 */
import { ReactComponent as IconCheck } from 'sensei/assets/icons/checked.svg';
import { Tooltip } from '../../components/tooltip';

/**
 * Renders the required checkmark.
 *
 * @param {Object}  props
 * @param {string}  props.className   Additional classname.
 * @param {boolean} props.completed   Tells if the checkbox is in completed state.
 * @param {boolean} props.showTooltip Tells if the required tooltip text should be shown. Default: true.
 * @param {string}  props.message     The tooltip text.
 */
export const CompletedStatus = ( {
	className,
	completed,
	showTooltip = true,
	message = __(
		'Required — This item needs to be completed in order to complete the lesson.',
		'sensei-pro'
	),
} ) => {
	return (
		<Tooltip
			as="div"
			position="bottom-start"
			disabled={ completed || ! showTooltip }
			message={ message }
			aria-label={ __( 'Status from the required block', 'sensei-pro' ) }
			className={ classnames(
				'sensei-supports-required__completed-status',
				{
					'sensei-supports-required__completed-status--completed': completed,
				},
				className
			) }
		>
			{ completed && <IconCheck /> }
		</Tooltip>
	);
};
