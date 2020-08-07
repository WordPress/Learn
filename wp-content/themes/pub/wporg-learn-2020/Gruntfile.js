/* global module:false, require:function, process:object */
module.exports = function( grunt ) {
	var isChild = 'wporg' !== grunt.file.readJSON( 'package.json' ).name;

	const getSassFiles = () => {
		const files = {};
		const paths = [ 'settings', 'tools', 'generic', 'base', 'objects', 'components', 'utilities', 'vendor' ];

		paths.forEach( function( component ) {
			var paths = [
				'../wporg/css/' + component + '/**/*.scss',
				'!../wporg/css/' + component + '/_' + component + '.scss'
			];

			if ( isChild ) {
				paths.push( 'css/' + component + '/**/*.scss' );
				paths.push( '!css/' + component + '/_' + component + '.scss' );
			}

			files[ 'css/' + component + '/_' + component + '.scss' ] = paths;
		} );

		return files;
	};

	grunt.initConfig({
		postcss: {
			options: {
				map: 'build' !== process.argv[2],
				processors: [
					require( 'autoprefixer' )( {
						cascade: false
					} ),
					require( 'pixrem' ),
					require('cssnano')( {
						mergeRules: false
					} )
				]
			},
			dist: {
				src: 'css/style.css'
			}
		},

		sass: {
			options: {
				implementation: require( 'node-sass' ),
				sourceMap: true,
				// Don't add source map URL in built version.
				omitSourceMapUrl: 'build' === process.argv[2],
				outputStyle: 'expanded'
			},
			dist: {
				files: {
					'css/style.css': 'css/style.scss',
				}
			}
		},

		sass_globbing: {
			itcss: {
				files: getSassFiles(),
			},
			options: { signature: false }
		},

		watch: {
			css: {
				files: ['**/*.scss', '../wporg/css/**/*scss'],
				tasks: ['css']
			}
		}
	});

	if ( 'build' === process.argv[2] ) {
		grunt.config.merge( { postcss: { options : { processors: [ require( 'cssnano' ) ] } } } );
	}

	grunt.loadNpmTasks( 'grunt-sass' );
	grunt.loadNpmTasks( 'grunt-postcss' );
	grunt.loadNpmTasks( 'grunt-sass-globbing' );
	grunt.loadNpmTasks( 'grunt-contrib-watch' );

	grunt.registerTask( 'css', [ 'sass_globbing', 'sass', 'postcss' ] );

	grunt.registerTask( 'default', [ 'css' ] );
	grunt.registerTask( 'build', [ 'css' ] ); // Automatically runs "production" steps
};
