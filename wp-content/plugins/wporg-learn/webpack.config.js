const config = require( '@wordpress/scripts/config/webpack.config' );

/**
 * Set up the custom entry points.
 */
config.entry = {
	'block-styles': './js/block-styles/index.js',
	'expiration-date': './js/expiration-date/index.js',
	'workshop-application-form': './js/workshop-application-form/src/index.js',
	'workshop-details': './js/workshop-details/src/index.js',
	event: './js/event.js',
	form: './js/form.js',
	'locale-notice': './js/locale-notice.js',
	'lesson-plan-actions': './js/lesson-plan-actions/src/index.js',
	'lesson-plan-details': './js/lesson-plan-details/src/index.js',
};

module.exports = config;
