const config = require( '@wordpress/scripts/config/webpack.config' );

/**
 * Set up the custom entry points.
 */
config.entry = {
	'workshop-details': './js/workshop-details/src/index.js',
	'block-styles': './js/block-styles/index.js',
}

/**
 * The jsonpFunction is a global function used to load application chunks. If
 * multiple webpack-bundled scripts are on the same page, these functions will
 * conflict. This provides a unique name for this function in our app.
 *
 * @see https://github.com/WordPress/gutenberg/issues/23607
 * @see https://webpack.js.org/configuration/output/#outputjsonpfunction
 * @see https://github.com/WordPress/gutenberg/issues/24321
 */
config.output.jsonpFunction = 'wporgLearnPlugin';

module.exports = config;
