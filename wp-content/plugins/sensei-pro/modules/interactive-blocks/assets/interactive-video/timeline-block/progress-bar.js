/**
 * WordPress dependencies
 */
import { forwardRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Progress bar component.
 *
 * @param {Object}      props               Component props.
 * @param {number}      props.videoProgress Video progress percentage.
 * @param {Object}      props.children      The break points.
 * @param {number|null} props.blockedAfter  Percentage of the video that is blocked.
 * @param {boolean}     props.isSave        Whether it's being rendered by the save. It's used
 *                                          because teacher role can't save `aria-valuenow` in the
 *                                          post content.
 */
const ProgressBar = forwardRef(
	(
		{ videoProgress = 0, children, blockedAfter = null, isSave = false },
		ref
	) => (
		<div ref={ ref } className="wp-block-sensei-pro-timeline__progress-bar">
			<div
				className="wp-block-sensei-pro-timeline__progress-bar-filled"
				style={ { width: `${ videoProgress }%` } }
				role="progressbar"
				aria-label={ __( 'Progress of the video', 'sensei-pro' ) }
				{ ...( ! isSave && {
					'aria-valuenow': videoProgress,
				} ) }
			>
				<div className="screen-reader-text">
					{ __( 'Video progress:', 'sensei-pro' ) } { videoProgress }%
				</div>
			</div>
			{ blockedAfter !== null ? (
				<div
					className="wp-block-sensei-pro-timeline__progress-bar-blocked"
					style={ {
						width: `${ 100 - blockedAfter }%`,
					} }
				>
					<div className="screen-reader-text">
						{ __( 'Video is blocked after:', 'sensei-pro' ) }{ ' ' }
						{ blockedAfter }%
					</div>
				</div>
			) : null }
			{ children }
		</div>
	)
);

export default ProgressBar;
