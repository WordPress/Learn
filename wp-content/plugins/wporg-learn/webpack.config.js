const config = require( '@wordpress/scripts/config/webpack.config' );

/**
 * Set up the custom entry points.
 */
config.entry = {
	'block-styles': './js/block-styles/index.js',
	'expiration-date': './js/expiration-date/index.js',
	'workshop-application-form': './js/workshop-application-form/src/index.js',
	'workshop-details': './js/workshop-details/src/index.js',
	'event': './js/event.js',
	'form': './js/form.js',
	'locale-notice': './js/locale-notice.js',
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
