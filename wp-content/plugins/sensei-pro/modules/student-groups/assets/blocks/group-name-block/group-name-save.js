/**
 * WordPress dependencies
 */
import { useBlockProps } from '@wordpress/block-editor';

const Save = () => {
	const blockProps = useBlockProps.save();

	return <h2 { ...blockProps }>{ '{{groupName}}' }</h2>;
};

export default Save;
