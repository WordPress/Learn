/**
 * Break Point save component.
 *
 * @param {Object} props            Component props.
 * @param {Object} props.blockProps Block props.
 * @param {Object} props.children   Component children.
 */
const BreakPointSave = ( { blockProps, children } ) => {
	return <div { ...blockProps }>{ children }</div>;
};

export default BreakPointSave;
