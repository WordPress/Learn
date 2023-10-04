/**
 * WordPress dependencies
 */
import { useBlockProps } from '@wordpress/block-editor';
/**
 * Internal dependencies
 */

export const Save = ( props ) => {
	const blockProps = useBlockProps.save();
	return <div { ...blockProps }>{ props.children }</div>;
};

export default Save;
