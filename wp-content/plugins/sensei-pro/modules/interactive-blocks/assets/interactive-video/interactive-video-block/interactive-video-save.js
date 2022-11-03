/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * Interactive Video Block save component.
 *
 * @param {Object}  props                           Component props.
 * @param {Array}   props.children                  Component children, including the video and the timeline block.
 * @param {Object}  props.blockProps                Block props.
 * @param {Object}  props.attributes                Block attributes.
 * @param {boolean} props.attributes.hiddenTimeline Whether timeline should be hidden.
 */
const InteractiveVideoSave = ( {
	children,
	blockProps,
	attributes: { hiddenTimeline },
} ) => {
	return (
		<div
			{ ...blockProps }
			className={ classnames( blockProps.className, {
				'wp-block-sensei-pro-interactive-video--with-hidden-timeline': hiddenTimeline,
			} ) }
		>
			{ children }
		</div>
	);
};

export default InteractiveVideoSave;
