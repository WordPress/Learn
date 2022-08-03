/**
 * WordPress dependencies
 */

import { useEffect, useState, useCallback } from '@wordpress/element';
/**
 * Internal dependencies
 */
import useLazyRequest from '../use-lazy-request';
import {
	create,
	getCourses,
	remove,
	update,
} from '../../services/group-courses';

/**
 * External dependencies
 */
import { isEqual, omit } from 'lodash';

const byActive = ( course ) => course.accessPeriod.status === 'active';
const byExpired = ( course ) => course.accessPeriod.status === 'expired';

const markAsRemoved = ( removed ) => {
	return ( state ) => {
		const toUpdate = [ ...state ];
		const removedIndex = toUpdate.findIndex(
			( course ) => course.id === removed.id
		);
		toUpdate[ removedIndex ].removed = true;
		return toUpdate;
	};
};

const unmarkAsRemoved = ( removed ) => {
	return ( state ) => {
		const toUpdate = [ ...state ];
		const removedIndex = toUpdate.findIndex(
			( course ) => course.id === removed.id
		);
		toUpdate[ removedIndex ].removed = false;
		return toUpdate;
	};
};

const isEqualWithPreviousVersion = ( collection, newest ) => {
	const byId = ( targetId ) => ( item ) => item.id === targetId;
	const old = collection.find( byId( newest.id ) );

	return isEqual(
		omit( old.accessPeriod, 'status' ),
		omit( newest.accessPeriod, 'status' )
	);
};

/**
 * Hook Result
 *
 * @typedef {Object} HookResult
 * @property {Array}    activeCourses  List of courses with active access periods
 * @property {Array}    expiredCourses List of courses with expired access periods
 * @property {boolean}  isLoading      Return if any async operation is happening
 * @property {boolean}  hasError       Return if any async returned an error
 * @property {Error}    error          Return if any async returned an error
 * @property {Function} addCourse      Add the course to the current group.
 * @property {Function} deleteCourse   Remove the course to the current group.
 *
 */

/**
 * Hook to manage operations with group-courses
 *
 * @param {*} groupId
 * @return {HookResult} The hook result data + actions related to the group id
 */
const useGroupCourses = ( groupId ) => {
	const [ selectedCourses, setSelectedCourses ] = useState( [] );
	const [ isInitializing, setIsInitializing ] = useState( true );

	const {
		run: runAddCourse,
		isLoading: isAddingCourses,
		error: errorAddingCourses,
	} = useLazyRequest( create, [ groupId ] );

	const {
		run: runRemoveCourse,
		isLoading: isRemovingCourse,
		error: errorRemovingCourses,
	} = useLazyRequest( remove, [ groupId ] );

	const {
		run: runGetCoursesByGroup,
		isLoading: isGettingCourses,
		error: errorGettingCourses,
	} = useLazyRequest( getCourses, [ groupId ] );

	const {
		run: runUpdateCourse,
		isLoading: isUpdatingCourse,
		error: errorUpdatingCourse,
	} = useLazyRequest( update, [ groupId ] );

	useEffect( async () => {
		const courses = await runGetCoursesByGroup( groupId );
		setSelectedCourses( courses );
		setIsInitializing( false );
	}, [ groupId ] );

	const addCourse = useCallback(
		async ( course ) => {
			const created = await runAddCourse( groupId, course );
			const alreadyExistent = selectedCourses.find(
				( c ) => c.id === created.id
			);
			if ( alreadyExistent ) {
				setSelectedCourses( unmarkAsRemoved( created ) );
			} else {
				setSelectedCourses( [ created, ...selectedCourses ] );
			}

			return selectedCourses;
		},
		[ groupId, selectedCourses ]
	);

	const deleteCourse = useCallback(
		async ( toRemove, removeEnrollments ) => {
			const removed = await runRemoveCourse(
				groupId,
				toRemove,
				removeEnrollments
			);
			setSelectedCourses( markAsRemoved( removed ) );
			return selectedCourses;
		},
		[ groupId, selectedCourses ]
	);

	const updateCourse = useCallback(
		async ( toUpdate ) => {
			if ( isEqualWithPreviousVersion( selectedCourses, toUpdate ) ) {
				return null;
			}

			const updated = await runUpdateCourse( groupId, toUpdate );
			if ( ! updated ) return null;

			const updatedList = selectedCourses.map( ( course ) => {
				if ( course.id === toUpdate.id ) {
					return updated;
				}
				return course;
			} );

			setSelectedCourses( updatedList );
			return selectedCourses;
		},
		[ groupId, selectedCourses ]
	);

	return {
		allCourses: selectedCourses,
		activeCourses: selectedCourses.filter( byActive ),
		expiredCourses: selectedCourses.filter( byExpired ),
		isLoading:
			!! isGettingCourses ||
			!! isAddingCourses ||
			!! isRemovingCourse ||
			!! isUpdatingCourse,
		hasError:
			!! errorAddingCourses ||
			!! errorGettingCourses ||
			!! errorRemovingCourses ||
			!! errorUpdatingCourse,
		error:
			errorAddingCourses ||
			errorGettingCourses ||
			errorRemovingCourses ||
			errorUpdatingCourse,
		addCourse,
		deleteCourse,
		updateCourse,
		isInitializing,
	};
};

export default useGroupCourses;
