/**
 * External dependencies
 */
import { useSelector } from 'react-redux';
import { isEqual } from 'lodash';

/**
 * WordPress dependencies
 */
import {
	createContext,
	useContext,
	useEffect,
	useState,
	useCallback,
	useMemo,
} from '@wordpress/element';

/**
 * Internal dependencies
 */
import { selectors as attributeSelector } from '../shared/block-frontend/data/attributes';
import { selectors as parentSelector } from '../shared/block-frontend/data/parents';

const FrontendPlayerContext = createContext( undefined );

/**
 * Required break points hook.
 *
 * @param {Object[]} [points=[]] Point IDs.
 * @param {number}   currentTime Current video time in seconds.
 * @param {Object}   player      The player instance.
 *
 * @return {Object|false} Object containing the first required and incompleted point state, or
 *                        `false` if no required incomplete point is found.
 */
const useRequiredBreakPoints = ( points, currentTime, player ) => {
	const pointStates = useSelector( ( state ) => {
		// Create point states - an object with id, requiredBlockIds and time, where the key is the
		// blockId.
		return points.reduce( ( acc, { blockId, attributes: { time } } ) => {
			const childrenIds = parentSelector.getDescendantBlockList(
				state,
				blockId
			);

			const requiredBlockIds = attributeSelector.filterRequiredBlockIds(
				state,
				childrenIds
			);

			const completed = attributeSelector.areRequiredBlocksCompleted(
				state,
				requiredBlockIds
			);

			return {
				...acc,
				[ blockId ]: {
					id: blockId,
					requiredBlockIds,
					completed,
					time,
				},
			};
		}, {} );
	}, isEqual );

	// Set first required incompleted point. If nothing is found, it sets `false`.
	const firstIncompleted = useMemo(
		() =>
			Object.values( pointStates ).reduce( ( a, b ) => {
				if ( ! b.completed && ! a ) {
					return b;
				}

				if ( ! b.completed && b.time < a?.time ) {
					return b;
				}

				return a;
			}, false ),
		[ pointStates ]
	);

	// Check if current time passed a required and incomplete point. It it has passed, send the
	// video to the required point.
	useEffect( () => {
		if ( firstIncompleted && currentTime > firstIncompleted.time ) {
			player
				.pause()
				.then( () => player.setCurrentTime( firstIncompleted.time ) );
		}
	}, [ currentTime, firstIncompleted, player ] );

	return firstIncompleted;
};

/**
 * Interactive video player hook.
 *
 * @param {Object}   player      The player instance.
 * @param {Object[]} [points=[]] Point IDs.
 *
 * @return {Object} Object for the context.
 */
export const useInteractiveVideoPlayer = ( player, points = [] ) => {
	const [ currentTime, setCurrentTime ] = useState( 0 );
	const [ openPoints, setOpenPoints ] = useState( [] );

	// Update current time.
	useEffect( () => {
		if ( ! player ) {
			return;
		}

		const event = player.on( 'timeupdate', setCurrentTime );

		return () => {
			event.then( ( unsubscribe ) => {
				unsubscribe();
			} );
		};
	}, [ player ] );

	// Required break points logic.
	const firstIncomplete = useRequiredBreakPoints(
		points,
		currentTime,
		player
	);

	/**
	 * Set a break point to open. It will open when it's in their respective time.
	 *
	 * @param {string}  id     Break point ID.
	 * @param {boolean} toggle Whether it should open or close.
	 * @param {number}  time   Point time in seconds.
	 */
	const setOpen = useCallback(
		( id, toggle, time ) => {
			if ( toggle ) {
				// Avoid opening if there's an incomplete point before this time.
				if ( firstIncomplete && firstIncomplete.time < time ) {
					return;
				}

				const sortByTime = ( a, b ) => a.time - b.time;

				// Add open point.
				setOpenPoints( ( prev ) =>
					[ ...prev, { id, time } ].sort( sortByTime )
				);
			} else {
				// Remove point.
				setOpenPoints( ( prev ) =>
					prev.filter( ( i ) => i.id !== id )
				);

				// Play after closed if there's no incomplete or it's after this time.
				if ( ! firstIncomplete || firstIncomplete.time > time ) {
					player.play();
				}
			}
		},
		[ player, firstIncomplete ]
	);

	/**
	 * Checks if a break point should open the content.
	 *
	 * @param {string} id   Break point ID.
	 * @param {number} time Time in seconds.
	 *
	 * @return {boolean} Whether it should open.
	 */
	const shouldOpen = useCallback(
		( id, time ) =>
			openPoints[ 0 ]?.id === id &&
			( ! firstIncomplete || firstIncomplete.time >= time ),
		[ openPoints, firstIncomplete ]
	);

	// Memoize context object. This is used to avoid a different object every re-render.
	return useMemo(
		() => ( {
			player,
			currentTime,
			setOpen,
			shouldOpen,
			firstIncomplete,
		} ),
		[ player, currentTime, setOpen, shouldOpen, firstIncomplete ]
	);
};

/**
 * Frontend player provider.
 *
 * @param {Object}         props             Component props.
 * @param {Object}         props.player      Player instance.
 * @param {Object[]}       [props.points=[]] Points.
 * @param {Object | Array} props.children    Component children.
 */
export const FrontendPlayerProvider = ( { player, points = [], children } ) => {
	const interactiveVideoPlayer = useInteractiveVideoPlayer( player, points );

	return (
		<FrontendPlayerContext.Provider value={ interactiveVideoPlayer }>
			{ children }
		</FrontendPlayerContext.Provider>
	);
};

/**
 * Hook to get the frontend player from the context.
 *
 * @return {Object} Object from  the context. Includes the "player" instance, the "currentTime" that
 *                  is the video current time, the "setOpen" function that sets a point as open
 *                  should open, the "shouldOpen" function that checks if a point content should
 *                  open, and the "firstIncomplete" attribute is the first breakpoint that is required
 *                  and not complete.
 */
export const useContextFrontendPlayer = () =>
	useContext( FrontendPlayerContext );
