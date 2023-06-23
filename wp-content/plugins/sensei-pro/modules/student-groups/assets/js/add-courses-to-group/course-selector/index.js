/**
 * WordPress dependencies
 */
import { useMemo, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */

import useResource from '../../hooks/use-resource';
import SearchAutoComplete from '../../search-autocomplete';
/**
 * External dependencies
 */
import { decode } from 'he';

const toOption = ( course ) => ( {
	value: course.id,
	label: decode( course.title.rendered ),
	course,
} );

const CourseSelector = ( { onSelect, selectedCourses = [] } ) => {
	const [ term, setTerm ] = useState( '' );

	const { resources: courses, isLoading } = useResource( {
		resource: 'courses',
		fields: [ 'id', 'title' ],
		term,
		exclude: selectedCourses,
	} );

	const searchOptions = useMemo( () => courses.map( toOption ), [ courses ] );

	return (
		<SearchAutoComplete
			isLoading={ isLoading }
			onSearch={ setTerm }
			placeholder={ __( 'Search Courses', 'sensei-pro' ) }
			options={ searchOptions }
			onSelect={ ( option ) => onSelect( option.course ) }
		/>
	);
};

export default CourseSelector;
