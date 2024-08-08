const config = require( '@wordpress/scripts/config/webpack.config' );

/**
 * Set up the custom entry points.
 */
config.entry = {
	'course-status': './js/course-status/src/index.js',
	'duration-meta': './js/duration-meta/index.js',
	'expiration-date': './js/expiration-date/index.js',
	'lesson-archive-excluded-meta': './js/lesson-archive-excluded-meta/index.js',
	'lesson-count': './js/lesson-count/src/index.js',
	'lesson-featured-meta': './js/lesson-featured-meta/index.js',
	'workshop-application-form': './js/workshop-application-form/src/index.js',
	'workshop-details': './js/workshop-details/src/index.js',
	event: './js/event.js',
	form: './js/form.js',
	'learning-duration': './js/learning-duration/src/index.js',
	'locale-notice': './js/locale-notice.js',
	'lesson-plan-actions': './js/lesson-plan-actions/src/index.js',
	'lesson-plan-details': './js/lesson-plan-details/src/index.js',
	'course-data': './js/course-data/src/index.js',
	'language-meta': './js/language-meta/index.js',
};

module.exports = config;
