/**
 * WordPress dependencies
 */
import { useSelect, useDispatch } from '@wordpress/data';
import { createBlock } from '@wordpress/blocks';
/**
 * External dependencies
 */
import { isEmpty } from 'lodash';

const getCourseOutlineBlock = ( blocks ) => {
	for ( let i = 0; i < blocks.length; i++ ) {
		const block = blocks[ i ];
		if ( 'sensei-lms/course-outline' === block.name ) {
			return block;
		}

		const found = getCourseOutlineBlock( block?.innerBlocks || [] );

		if ( found ) return found;
	}
};

const useCourseOutline = () => {
	const { getBlocks } = useSelect( 'core/block-editor' );
	const { replaceInnerBlocks } = useDispatch( 'core/block-editor' );

	const replaceAllLessons = ( lessons ) => {
		if ( isEmpty( lessons ) ) {
			return;
		}

		const courseOutline = getCourseOutlineBlock( getBlocks() );

		if ( ! courseOutline ) {
			return;
		}

		const blocks = lessons.map( ( lesson ) =>
			createBlock( 'sensei-lms/course-outline-lesson', {
				title: lesson.name,
			} )
		);

		replaceInnerBlocks( courseOutline.clientId, blocks );
	};

	return {
		replaceAllLessons,
	};
};

export default useCourseOutline;
