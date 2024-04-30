const RtlCssPlugin = require( 'rtlcss-webpack-plugin' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

const [ scriptConfig ] = defaultConfig;

module.exports = {
	...scriptConfig,
	plugins: [
		...scriptConfig.plugins,
		new RtlCssPlugin( {
			filename: `[name]-rtl.css`,
		} ),
	],
};
