/**
 * WordPress dependencies
 */
import { registerPlugin } from '@wordpress/plugins';
/**
 * Internal dependencies
 */
import useBrowserHash from './use-browser-hash';
import TailoredCourseOutlineModal from './tailored-course-outline-modal';
import useCourseOutlineBlock from './use-course-outline';
import './style.scss';

import { MODAL_HASH_ID } from '../config';

export const TailoredCourseOutlinePlugin = () => {
	const [ hash, setHash ] = useBrowserHash();
	const { replaceAllLessons } = useCourseOutlineBlock();

	const open = hash === MODAL_HASH_ID;

	return (
		<>
			{ open && (
				<TailoredCourseOutlineModal
					onClose={ ( lessons ) => {
						replaceAllLessons( lessons );
						setHash( '' );
					} }
				/>
			) }
		</>
	);
};

registerPlugin( 'tailored-course-outline-modal', {
	render: TailoredCourseOutlinePlugin,
} );
