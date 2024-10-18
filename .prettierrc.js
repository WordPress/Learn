// Import the default config for core compatibility, but enable us to add some overrides as needed.
const defaultConfig = require( '@wordpress/scripts/config/.prettierrc.js' );

module.exports = {
	...defaultConfig,
	printWidth: 115,
};
