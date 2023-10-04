/**
 * WordPress dependencies
 */
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { addFilter } from '@wordpress/hooks';
import apiFetch from '@wordpress/api-fetch';

/**
 * Internal dependencies
 */
import OptionsSelector from '../../../../student-groups/assets/js/option-selector';
import useResource from '../../../../student-groups/assets/js/hooks/use-resource';
import './style.scss';

/**
 * External dependencies
 */
import editorLifecycle from 'sensei/assets/shared/helpers/editor-lifecycle';

const useTeachers = (
	initialCoteachers,
	selectedCoteachers,
	courseAuthorId,
	searchTerm
) => {
	// Initialise filters state.
	const [ filters, setFilters ] = useState( {
		term: '',
		exclude: [],
		// Force initial co-teachers list to be requested initially.
		include: initialCoteachers,
	} );

	// Load list of available teachers.
	const teachers = useResource( {
		resource: 'users',
		fields: [ 'id', 'name' ],
		roles: [ 'teacher' ], // Keep in sync with `Co_Teachers::SUPPORTED_ROLES`.
		...filters,
	} );

	// Update resource filters when the search term changes.
	useEffect( () => {
		// Consciously ignore the excluded property – we are managing them ourselves.
		setFilters( ( previous ) => ( { ...previous, searchTerm } ) );
	}, [ searchTerm ] );

	// Update resource filters when author or selected co-teachers change.
	useEffect( () => {
		if ( selectedCoteachers !== undefined ) {
			const exclude = [ courseAuthorId ].concat(
				selectedCoteachers.map( ( coteacher ) => coteacher.value )
			);
			setFilters( ( previous ) => ( {
				...previous,
				exclude,
				// Disable initially forced list of user IDs.
				include: [],
			} ) );
		}
	}, [ selectedCoteachers, courseAuthorId, setFilters ] );

	return [ teachers.resources, teachers.isLoading, setFilters ];
};

const toOption = ( user ) => ( {
	value: user.id,
	label: user.name,
	user,
} );

const useInitialCoteacherIds = ( courseId ) => {
	const [ initialCoteacherIds, setInitialCoteacherIds ] = useState(
		undefined
	);
	// Load initial co-teachers list.
	useEffect( () => {
		( async () => {
			const result = await apiFetch( {
				path: `/sensei-pro-internal/v1/coteachers/${ courseId }`,
				method: 'GET',
			} );
			setInitialCoteacherIds( result );
		} )();
		// eslint-disable-next-line react-hooks/exhaustive-deps -- Only run once.
	}, [] );
	return initialCoteacherIds;
};

const useEditorLifecycleToPersistOnSave = ( courseId, selectedCoteachers ) => {
	// Hook into editor lifecycle to save the co-teachers list.
	useEffect( () =>
		editorLifecycle( {
			onSaveStart: () => {
				if ( selectedCoteachers !== undefined ) {
					apiFetch( {
						path: `/sensei-pro-internal/v1/coteachers/${ courseId }`,
						method: 'POST',
						data: {
							users: selectedCoteachers.map(
								( { value } ) => value
							),
						},
					} );
				}
			},
		} )
	);
};

export const CoTeachersMetaBox = ( { courseId, courseAuthorId } ) => {
	const [ searchTerm, setSearchTerm ] = useState( '' );
	const [ selectedCoteachers, setSelectedCoteachers ] = useState( undefined );
	const initialCoteacherIds = useInitialCoteacherIds( courseId );
	const [ teachers, isLoading ] = useTeachers(
		initialCoteacherIds,
		selectedCoteachers,
		courseAuthorId,
		searchTerm
	);
	const [ options, setOptions ] = useState( [] );
	useEditorLifecycleToPersistOnSave( courseId, selectedCoteachers );

	// Remove courseAuthorId from selected co-teachers when it changes.
	useEffect( () => {
		setSelectedCoteachers( ( selected ) => {
			if ( selected !== undefined ) {
				return selected.filter(
					( coteacher ) => coteacher.value !== courseAuthorId
				);
			}
			return selected;
		} );
	}, [ courseAuthorId, setSelectedCoteachers ] );

	// Initialise selected co-teachers with initial co-teacher's data.
	useEffect( () => {
		if (
			selectedCoteachers === undefined &&
			initialCoteacherIds !== undefined &&
			! isLoading
		) {
			const newSelectedCoteachers = [];
			for ( const initialCoteacherId of initialCoteacherIds ) {
				const teacher = teachers.find(
					( t ) => t.id === initialCoteacherId
				);
				if ( teacher ) {
					newSelectedCoteachers.push( toOption( teacher ) );
				}
			}
			setSelectedCoteachers( newSelectedCoteachers );
		}
	}, [ teachers, initialCoteacherIds, selectedCoteachers ] );

	// Update options when teacher list is updated.
	useEffect( () => {
		setOptions( teachers.map( toOption ) );
	}, [ setOptions, teachers ] );

	// Early return if selected co-teachers are not calculated yet.
	if ( selectedCoteachers === undefined ) {
		return <></>;
	}

	const Selector = () => {
		// Early return if there are no teachers already selected or to select from.
		if (
			selectedCoteachers.length === 0 &&
			teachers.length === 0 &&
			! isLoading
		) {
			return (
				<>
					<p>
						{ __(
							'To select a co-teacher, you first need to add users with the teacher role.',
							'sensei-pro'
						) }{ ' ' }
					</p>
					<p>
						<a href="users.php">
							{ __( 'Add teachers', 'sensei-pro' ) }
						</a>
					</p>
				</>
			);
		}
		// Otherwise, return the actual selector.
		return (
			<OptionsSelector
				selected={ selectedCoteachers }
				options={ options }
				onSearch={ ( term ) => setSearchTerm( term ) }
				onChange={ ( selected ) => {
					setSelectedCoteachers( selected );
				} }
				isLoading={ isLoading }
				placeholder={ __( 'Search For Teachers', 'sensei-pro' ) }
				className="student-selector"
				excluded={ [ courseAuthorId ] }
			/>
		);
	};

	return (
		<div className="sensei-course-coteachers">
			<h3 className="sensei-course-coteachers__header">
				{ __( 'Co-Teachers', 'sensei-pro' ) }
			</h3>
			<Selector />
		</div>
	);
};

addFilter( 'senseiCourseSettingsTeachersAfter', 'sensei-pro', ( existing ) => {
	return ( props ) => {
		return (
			<>
				{ existing && existing( props ) }
				<CoTeachersMetaBox { ...props } />
			</>
		);
	};
} );
