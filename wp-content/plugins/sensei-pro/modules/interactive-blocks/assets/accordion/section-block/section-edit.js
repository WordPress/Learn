/**
 * WordPress dependencies
 */
import { useInnerBlocksProps, useBlockProps } from '@wordpress/block-editor';
/**
 * Internal dependencies
 */
import Settings from './section-settings';
import Section from '../elements/section';

export const Edit = ( props ) => {
	const { attributes, setAttributes } = props;
	const { openOnLoad } = attributes;

	const setOpenOnLoad = ( value ) => setAttributes( { openOnLoad: value } );

	const blockProps = useBlockProps();

	const { children, ...innerBlockProps } = useInnerBlocksProps( blockProps, {
		template: [
			[ 'sensei-lms/accordion-summary', {} ],
			[ 'sensei-lms/accordion-content', {} ],
		],
		templateLock: 'all',
	} );

	return (
		<>
			<Settings
				{ ...props }
				openOnLoad={ openOnLoad }
				setOpenOnLoad={ setOpenOnLoad }
			/>
			<Section { ...innerBlockProps } attributes={ attributes }>
				{ children }
			</Section>
		</>
	);
};

export default Edit;
