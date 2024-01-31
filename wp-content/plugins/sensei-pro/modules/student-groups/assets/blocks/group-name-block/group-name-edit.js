/**
 * WordPress dependencies
 */
import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

const Edit = () => {
	const blockProps = useBlockProps();

	return <h2 { ...blockProps }>{ __( 'Group Name', 'sensei-pro' ) }</h2>;
};

export default Edit;
