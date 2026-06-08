module.exports = {
	extends: 'plugin:@wordpress/eslint-plugin/recommended',

	parserOptions: {
		requireConfigFile: false,
		babelOptions: {
			presets: [ require.resolve( '@wordpress/babel-preset-default' ) ],
		},
	},
};
