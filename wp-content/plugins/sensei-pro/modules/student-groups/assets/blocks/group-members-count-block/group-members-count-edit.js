/**
 * WordPress dependencies
 */
import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

const Edit = () => {
	const blockProps = useBlockProps();

	return <div { ...blockProps }>{ __( '99 members', 'sensei-pro' ) }</div>;
};

export default Edit;
