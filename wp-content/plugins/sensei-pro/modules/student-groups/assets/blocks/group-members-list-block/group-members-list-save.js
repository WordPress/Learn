/**
 * WordPress dependencies
 */
import { useBlockProps } from '@wordpress/block-editor';

const Save = () => {
	const blockProps = useBlockProps.save();

	return <div { ...blockProps }>{ '{{groupMembersList}}' }</div>;
};

export default Save;
