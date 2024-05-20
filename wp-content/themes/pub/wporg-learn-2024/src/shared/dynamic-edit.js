/**
 * WordPress dependencies
 */
import { useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit( { name, attributes, context } ) {
	const blockProps = useBlockProps();
	const { postId } = context;
	return (
		<div { ...blockProps }>
			<ServerSideRender
				block={ name }
				attributes={ attributes }
				skipBlockSupportAttributes
				urlQueryArgs={ { post_id: postId } }
			/>
		</div>
	);
}
