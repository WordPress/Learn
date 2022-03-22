/**
 * WordPress dependencies.
 */
import { addFilter } from '@wordpress/hooks';

/**
 * Internal dependencies.
 */
import { Setup } from './Setup';

const LicensingOnExtensionsPage = () => (
	<>
		<Setup />
		<div className="sensei-pro-setup__extensions-separator" />
	</>
);

addFilter(
	'senseiExtensionsFeaturedProduct',
	'sensei-pro/setup',
	() => LicensingOnExtensionsPage
);
