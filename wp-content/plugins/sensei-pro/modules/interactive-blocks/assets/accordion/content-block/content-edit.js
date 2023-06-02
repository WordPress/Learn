/**
 * WordPress dependencies
 */
import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';
/**
 * Internal dependencies
 */
import Content from '../elements/content';
import { __ } from '@wordpress/i18n';
import useInnerBlockProtection from '../elements/hooks/use-inner-block-protection';

export const INITIAL_BLOCK = {
	name: 'core/paragraph',
	attributes: {
		placeholder: __(
			'Add the section content or type / to choose a block.',
			'sensei-pro'
		),
	},
};

const TEMPLATE = [ [ INITIAL_BLOCK.name, INITIAL_BLOCK.attributes ] ];

export const Edit = ( props ) => {
	const { clientId } = props;
	const blockProps = useBlockProps();

	useInnerBlockProtection( clientId, [ 'sensei-lms/quiz' ] );

	return (
		<Content { ...blockProps }>
			<InnerBlocks template={ TEMPLATE } templateLock={ false } />
		</Content>
	);
};

export default Edit;
