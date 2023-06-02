/**
 * WordPress dependencies
 */
import { useDispatch } from '@wordpress/data';
import { useEffect } from '@wordpress/element';
import { store as noticeStore } from '@wordpress/notices';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import useMeta from './use-meta';

/**
 * External dependencies
 */
import usePrevious from 'shared-module/use-previous';

/**
 * A constant to save the notice ID used for Course Showcase errors.
 *
 * @type {string}
 */
const COURSE_SHOWCASE_NOTICE_ID = 'course-showcase-save-error';

/**
 * A hook that handles the response of the SenseiLMS.com API while saving
 * the Course Showcase listing.
 */
const useHandleResponse = () => {
	const [ error ] = useMeta( '_senseilmscom_error' );
	const [ submitted ] = useMeta( '_senseilmscom_submitted' );
	const previousSubmitted = usePrevious( submitted );

	const { createErrorNotice, removeNotice } = useDispatch( noticeStore );
	useEffect( () => {
		if ( error === '' ) {
			removeNotice( COURSE_SHOWCASE_NOTICE_ID );
			if (
				false === previousSubmitted &&
				previousSubmitted !== submitted &&
				window.sensei?.showcaseEditor?.redirectUrl
			) {
				// Listing was "published" successfully, let's redirect the user.
				window.location.href = window.sensei.showcaseEditor.redirectUrl;
			}
			return;
		}
		const errorMessage = sprintf(
			/* translators: Error message. */
			__( 'Course Showcase could not be updated. %s', 'sensei-pro' ),
			error
		);
		createErrorNotice( errorMessage, {
			id: COURSE_SHOWCASE_NOTICE_ID,
			isDismissible: false,
		} );
	}, [
		removeNotice,
		createErrorNotice,
		error,
		previousSubmitted,
		submitted,
	] );
};

export default useHandleResponse;
