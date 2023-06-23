/**
 * WordPress dependencies
 */
import { select } from '@wordpress/data';

/**
 * Get lesson content from the text blocks inside Gutenberg editor.
 *
 * @return {string} Lesson content.
 */
const getLessonContent = () => {
	const lessonContent = getBlockContentsRecursivelyFromInnerBlocks(
		select( 'core/block-editor' )
			.getBlocks()
			.filter( ( block ) => 'sensei-lms/quiz' !== block.name )
	);
	return lessonContent.trim();
};

const getBlockContentsRecursivelyFromInnerBlocks = ( blocks ) => {
	let content = '';

	blocks.forEach( ( block ) => {
		let blockContent = scrapTextFromTextBlock( block );

		blockContent = blockContent ? blockContent + ' ' : '';

		if ( block.innerBlocks && block.innerBlocks.length > 0 ) {
			blockContent += getBlockContentsRecursivelyFromInnerBlocks(
				block.innerBlocks
			);
		}
		content += blockContent;
	} );

	return content;
};

/**
 * Get text from a text block.
 *
 * @param {Object} block Block object.
 *
 * @return {string} Text from the block.
 */
const scrapTextFromTextBlock = ( block ) => {
	const { attributes, name } = block;

	switch ( name ) {
		case 'core/paragraph':
		case 'core/verse':
		case 'core/preformatted':
		case 'core/code':
		case 'core/heading':
		case 'core/freeform':
		case 'core/list-item':
			return attributes.content;
		case 'core/pullquote':
			return (
				'"' + attributes.value + '" said ' + attributes.citation + '.'
			);
		default:
			return '';
	}
};

export {
	getLessonContent,
	getBlockContentsRecursivelyFromInnerBlocks,
	scrapTextFromTextBlock,
};
