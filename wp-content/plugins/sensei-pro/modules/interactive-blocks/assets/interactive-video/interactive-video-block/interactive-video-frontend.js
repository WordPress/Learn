/**
 * External dependencies
 */
import Player from 'sensei/assets/shared/helpers/player';

/**
 * WordPress dependencies
 */
import { useEffect, useRef, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import meta from './block.json';
import { registerBlockFrontend } from '../../shared/block-frontend';
import { FrontendPlayerProvider } from '../frontend-player-context';
import ignorePersistedAttributes from '../../shared/ignore-persisted-attributes';

/**
 * Hook to get a player from an Interactive Video Block.
 *
 * @param {Object} ref A ref for the Interactive Video Frontend container.
 * @return {Object} The Player Instance.
 */
const usePlayerFromRef = ( ref ) => {
	const [ player, setPlayer ] = useState();

	useEffect( () => {
		const element = ref.current.querySelector( 'video, iframe' );

		setPlayer( new Player( element ) );
	}, [ ref ] );

	return player;
};

/**
 * Interactive Video component to be used while to render in the frontend.
 *
 * @param {Object} props             Component props.
 * @param {Array}  props.children    Component children.
 * @param {Object} props.blockProps  Block Props.
 * @param {Array}  props.innerBlocks The inner blocks.
 */
const InteractiveVideoFrontend = ( { children, blockProps, innerBlocks } ) => {
	const ref = useRef();
	const player = usePlayerFromRef( ref );
	const points = innerBlocks[ 1 ]?.innerBlocks;

	return (
		<div { ...blockProps } ref={ ref }>
			<FrontendPlayerProvider player={ player } points={ points }>
				{ children }
			</FrontendPlayerProvider>
		</div>
	);
};

ignorePersistedAttributes( meta.name );

registerBlockFrontend( {
	name: meta.name,
	run: InteractiveVideoFrontend,
} );
