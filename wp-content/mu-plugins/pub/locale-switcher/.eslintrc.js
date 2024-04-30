module.exports = {
	extends: '../../../../.eslintrc.js',
	rules: {
		/*
		 * Set up our text domain.
		 */
		'@wordpress/i18n-text-domain': [ 'error', { allowedTextDomain: [ 'wporg' ] } ],
	},
};
