/**
 * WordPress dependencies.
 */
import { render } from '@wordpress/element';

/**
 * Internal dependencies.
 */
import { Setup } from './Setup';

const rootElement = document.getElementById( 'sensei-pro-setup__container' );

render( <Setup />, rootElement );
