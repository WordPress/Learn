/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import domReady from '@wordpress/dom-ready';
import { render } from '@wordpress/element';
import { Card, Notice, Spinner } from '@wordpress/components';

/**
 * External dependencies
 */
import { AnimatePresence, motion } from 'framer-motion';

/**
 * Internal dependencies
 */
import useGroupCourses from '../hooks/use-group-courses';
import CourseSelector from './course-selector';
import ActiveCourses from './active-courses';
import ExpiredCourses from './expired-courses';

import './style.scss';
/**
 * @typedef {Object} Props
 * @property {groupId} groupId The Group's id to assign students.
 */

/**
 * Component to Manage course's access periods
 *
 * @param {Props} props The component Props.
 * @return {JSX} The component.
 */

const AnimatedSpinner = ( { isLoading } ) => (
	<AnimatePresence>
		{ isLoading && (
			<motion.span
				key="loading"
				initial={ {
					opacity: 0,
				} }
				animate={ {
					opacity: 1,
				} }
				exit={ {
					opacity: 0,
					transition: { delay: 1 },
				} }
			>
				<Spinner
					data-testid="loading-active"
					className="loading-state .components-animate__loading"
				/>
			</motion.span>
		) }
	</AnimatePresence>
);

const RenderWhenReady = ( { isReady, children } ) => {
	if ( ! isReady ) return null;

	return <>{ children }</>;
};

const AnimatedNotice = ( { hasError, error } ) => (
	<AnimatePresence>
		{ hasError && (
			<motion.div
				initial={ {
					opacity: 0,
				} }
				animate={ {
					opacity: 1,
				} }
				exit={ {
					opacity: 0,
				} }
			>
				<Notice
					status="error"
					className="add-student-to-group__notice"
					isDismissible={ false }
				>
					{ error.message ?? __( 'Something Wrong', 'sensei-pro' ) }
				</Notice>
			</motion.div>
		) }
	</AnimatePresence>
);

const AddCoursesToGroup = ( { groupId } ) => {
	const {
		activeCourses,
		expiredCourses,
		allCourses,
		isLoading,
		addCourse,
		deleteCourse,
		updateCourse,
		isInitializing,
		hasError,
		error,
	} = useGroupCourses( groupId );

	const excludeFromSelection = allCourses.map( ( course ) => course.id );

	return (
		<Card className="add-courses-to-group" size="large">
			<div className="add-courses-to-group__content">
				<h2>
					{ __(
						'Auto-enroll students and Access Periods',
						'sensei-pro'
					) }
					<AnimatedSpinner isLoading={ isLoading } />
				</h2>
				<p>
					{ __(
						'Automatically enroll students in courses that belong to this group. You can also set an Access Period, which is the start and end date that students have access to lessons in the course.',
						'sensei-pro'
					) }
				</p>
				<AnimatedNotice hasError={ hasError } error={ error } />
				<RenderWhenReady isReady={ ! isInitializing }>
					<CourseSelector
						onSelect={ addCourse }
						selectedCourses={ excludeFromSelection }
					></CourseSelector>
					<ActiveCourses
						activeCourses={ activeCourses }
						isLoading={ isLoading }
						onUnSelect={ deleteCourse }
						onSelect={ addCourse }
						onUpdate={ updateCourse }
						disabled={ isLoading }
					></ActiveCourses>
					{ expiredCourses.length > 0 && (
						<ExpiredCourses
							expiredCourses={ expiredCourses }
							isLoading={ isLoading }
							onUpdate={ updateCourse }
						></ExpiredCourses>
					) }
				</RenderWhenReady>
			</div>
		</Card>
	);
};

domReady( () => {
	const element = document.getElementById( 'access-period-page' );
	render( <AddCoursesToGroup { ...element?.dataset } />, element );
} );

export default AddCoursesToGroup;
