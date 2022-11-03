/**
 * External dependencies
 */
import Modal from 'sensei/assets/shared/components/modal';
import { useSelector } from 'react-redux';
import { isEqual } from 'lodash';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { useEffect, useCallback, useRef, Children } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import BreakPointButton from './break-point-button';
import useBreakPointPositionStyle from './use-break-point-position-style';
import { registerBlockFrontend } from '../../shared/block-frontend';
import meta from './block.json';
import { useContextFrontendPlayer } from '../frontend-player-context';
import usePrevious from '../use-previous';
import ignorePersistedAttributes from '../../shared/ignore-persisted-attributes';
import { selectors as parentSelector } from '../../shared/block-frontend/data/parents';
import { selectors as attributeSelector } from '../../shared/block-frontend/data/attributes';
import { BLOCK_ID_ATTRIBUTE } from '../../shared/supports-block-id';

/**
 * Exits the fullscreen mode, if the page is in the fullscreen mode, naturally
 *
 * @return {Promise<void>|void} The promise for when the operation is finished.
 */
const exitFullScreen = () =>
	document.fullscreenElement &&
	( document.exitFullscreen
		? document.exitFullscreen()
		: document.webkitExitFullscreen() );

/**
 * It sets the minimum time to 0.001, so we make sure the points from 0:00.000 will open only after
 * the play.
 *
 * @param {number} time Time in seconds to be adjusted.
 *
 * @return {number} Adjusted time.
 */
const getAdjustedTime = ( time ) => Math.max( time, 0.001 );

/**
 * Function to be passed as render prop to render footer of the modal of the
 * break point.
 *
 * @param {Function} onClose Callback to call when closing the modal.
 */
const renderBreakPointFooter = ( onClose ) => (
	<div className="wp-block-sensei-pro-break-point__modal-footer">
		<div className="wp-block-button">
			<button onClick={ onClose } className="wp-block-button__link">
				{ __( 'Continue', 'sensei-pro' ) }
			</button>
		</div>
	</div>
);

/**
 * Searches for iframes and videos inside the ref element and set the
 * "loading" attribute to "eager", forcing the element to be loaded even when
 * the breakpoint is not loaded yet.
 *
 * @return {Object} The ref attribute to pass to an HTML Element rendered by React.
 */
const useEagerLoading = () => {
	const ref = useRef();
	useEffect( () => {
		if ( ! ref.current ) {
			return;
		}
		ref.current
			.querySelectorAll( 'iframe, video' )
			.forEach( ( element ) => {
				element.setAttribute( 'loading', 'eager' );
			} );
	} );
	return ref;
};

/**
 * Component to render content of the break point. If isModalOpen is true, it
 * will render the Modal component. If it is false, it will render a hidden
 * container.
 *
 * @param {Object}       props             Component props.
 * @param {boolean}      props.isModalOpen Flag to indicate if the modal is open or not.
 * @param {number}       props.time        Point time in seconds.
 * @param {Object}       props.player      The player instance.
 * @param {Function}     props.onClose     Callback to call when closing the modal.
 * @param {string}       props.blockId     The Block ID of the breakpoint.
 * @param {Array|Object} props.children    Component children.
 */
const BreakPointContent = ( {
	isModalOpen,
	time,
	player,
	onClose,
	children,
	blockId,
} ) => {
	useEffect( () => {
		if ( isModalOpen ) {
			player
				.pause()
				.then( () => player.setCurrentTime( getAdjustedTime( time ) ) )
				.then( () => exitFullScreen() );
		}
	}, [ isModalOpen, time, player ] );

	const onCloseCallback = isModalOpen ? onClose : () => {};
	const extraContentProps = { [ BLOCK_ID_ATTRIBUTE ]: blockId };

	// On browsers that support the "loading" attribute (like Chrome, for instance),
	// the value "lazy" means that it will only effectively load the element
	// when the element is visible on the screen, which means the "registerVideo"
	// function is not called until the modal is visible.
	// So, because of this, we need to change the value of the attribute to
	// "eager" - which is the default value anyway - to force the browser into
	// loading the iframe and calling "registerVideo" properly.
	const ref = useEagerLoading();

	// We need to force rendering the children because otherwise the
	// required blocks logic will not detect them, which can be very
	// confusing.
	return (
		<Modal
			className={ classnames( 'wp-block-sensei-pro-break-point__modal', {
				'wp-block-sensei-pro-break-point__modal__hidden-content': ! isModalOpen,
			} ) }
			onClose={ onCloseCallback }
			renderFooter={ renderBreakPointFooter }
		>
			<div className="entry-content" ref={ ref } { ...extraContentProps }>
				{ children }
			</div>
		</Modal>
	);
};

/**
 * Break Point component to be used while to render in the frontend.
 *
 * @param {Object} props                 Component props.
 * @param {Object} props.attributes      Block attributes.
 * @param {number} props.attributes.time Point time in seconds.
 * @param {Object} props.blockProps      Block props.
 * @param {Object} props.children        Component children.
 * @param {string} props.blockId         Block ID.
 */
const BreakPointFrontend = ( {
	attributes: { time },
	blockProps,
	children,
	blockId,
} ) => {
	const {
		player,
		currentTime,
		setOpen,
		shouldOpen,
		firstIncomplete,
	} = useContextFrontendPlayer();
	const previousTime = usePrevious( currentTime );

	const requiredBlockIds = useSelector( ( state ) => {
		const childrenIds = parentSelector.getDescendantBlockList(
			state,
			blockId
		);

		return attributeSelector.filterRequiredBlockIds( state, childrenIds );
	}, isEqual );

	const isCompleted = useSelector(
		( state ) =>
			attributeSelector.areRequiredBlocksCompleted(
				state,
				requiredBlockIds
			),
		isEqual
	);

	/**
	 * Open break point content.
	 */
	const open = useCallback( () => {
		setOpen( blockId, true, time );
	}, [ setOpen, blockId, time ] );

	/**
	 * Close break point content.
	 */
	const close = () => {
		setOpen( blockId, false, time );
	};

	// Check time update.
	useEffect( () => {
		const jumpDiff = currentTime - previousTime;

		if ( jumpDiff === 0 ) {
			return;
		}

		const adjustedTime = getAdjustedTime( time );
		const isBetweenPlayedInterval =
			adjustedTime > previousTime && adjustedTime < currentTime;

		if (
			( isBetweenPlayedInterval && jumpDiff < 1 ) ||
			adjustedTime === currentTime
		) {
			open();
		}
	}, [ currentTime, previousTime, time, open ] );

	const positionStyle = useBreakPointPositionStyle( time, player );

	if ( isEmpty( children ) ) {
		return null;
	}

	return (
		<div { ...blockProps }>
			<BreakPointButton
				hasContent={ true }
				onClick={ open }
				style={ positionStyle }
				isRequired={ requiredBlockIds.length > 0 && ! isCompleted }
				isBlocked={ time > firstIncomplete?.time }
			/>
			<BreakPointContent
				isModalOpen={ shouldOpen( blockId, time ) }
				time={ time }
				player={ player }
				onClose={ close }
				blockId={ blockId }
			>
				{ children }
			</BreakPointContent>
		</div>
	);
};

const isEmpty = ( children ) => {
	const hasContent = ( child ) => {
		const content = child?.props?.block?.element?.innerHTML;
		if ( content === undefined ) {
			return true; // Consider non-empty if we couldn't determine the content.
		}
		return content !== ''; // Check if content is not empty.
	};
	return ! Children.toArray( children ).some( hasContent );
};

ignorePersistedAttributes( meta.name );

registerBlockFrontend( {
	name: meta.name,
	run: BreakPointFrontend,
} );
