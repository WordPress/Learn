/**
 * WordPress dependencies
 */
import { useBlockProps } from '@wordpress/block-editor';
/**
 * Internal dependencies
 */
import Content from '../elements/content';

export const Save = ( props ) => {
	return <Content { ...useBlockProps.save() }>{ props.children }</Content>;
};

export default Save;
