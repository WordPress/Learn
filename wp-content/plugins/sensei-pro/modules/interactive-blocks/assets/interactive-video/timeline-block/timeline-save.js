/**
 * Internal dependencies
 */
import ProgressBar from './progress-bar';

/**
 * Timeline save component.
 *
 * @param {Object} props            Component Props.
 * @param {Object} props.blockProps Block props.
 * @param {Object} props.attributes Block attributes.
 * @param {Object} props.children   The break points.
 */
const TimelineSave = ( { blockProps, attributes, children } ) => {
	if ( attributes.breakPointsCount === 0 ) {
		return null;
	}

	return (
		<div { ...blockProps }>
			<ProgressBar isSave>{ children }</ProgressBar>
		</div>
	);
};

export default TimelineSave;
