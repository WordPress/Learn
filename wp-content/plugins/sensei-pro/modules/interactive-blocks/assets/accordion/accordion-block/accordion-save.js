/**
 * WordPress dependencies
 */
import { useBlockProps } from '@wordpress/block-editor';

const Save = ( { children } ) => (
	<div { ...useBlockProps.save() }>{ children }</div>
);

export default Save;
