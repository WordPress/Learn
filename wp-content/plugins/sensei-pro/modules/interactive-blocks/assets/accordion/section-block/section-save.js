/**
 * WordPress dependencies
 */
import { useBlockProps } from '@wordpress/block-editor';

const SectionSave = ( props ) => {
	return <details { ...useBlockProps.save() }>{ props.children }</details>;
};

export default SectionSave;
