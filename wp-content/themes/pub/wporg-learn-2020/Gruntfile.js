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
					'css/print.css': 'css/print.scss',
				}
			}
		},

		sass_globbing: {
			itcss: {
				files: getSassFiles(),
			},
			options: { signature: false }
		},

		rtlcss: {
			options: {
				// rtlcss options.
				opts: {
					clean: false,
					processUrls: { atrule: true, decl: false },
					stringMap: [
						{
							name: 'import-rtl-stylesheet',
							priority: 10,
							exclusive: true,
							search: [ '.css' ],
							replace: [ '-rtl.css' ],
							options: {
								scope: 'url',
								ignoreCase: false
							}
						} // phpcs:ignore Generic.WhiteSpace.ScopeIndent.IncorrectExact
					]
				},
				saveUnmodified: false,
				plugins: [
					{
						name: 'swap-dashicons-left-right-arrows',
						priority: 10,
						directives: {
							control: {},
							value: []
						},
						processors: [
							{
								expr: /content/im,
								action: function( prop, value ) {
									if ( value === '"\\f141"' ) { // dashicons-arrow-left.
										value = '"\\f139"';
									} else if ( value === '"\\f340"' ) { // dashicons-arrow-left-alt.
										value = '"\\f344"';
									} else if ( value === '"\\f341"' ) { // dashicons-arrow-left-alt2.
										value = '"\\f345"';
									} else if ( value === '"\\f139"' ) { // dashicons-arrow-right.
										value = '"\\f141"';
									} else if ( value === '"\\f344"' ) { // dashicons-arrow-right-alt.
										value = '"\\f340"';
									} else if ( value === '"\\f345"' ) { // dashicons-arrow-right-alt2.
										value = '"\\f341"';
									}
									return { prop: prop, value: value };
								}
							} // phpcs:ignore Generic.WhiteSpace.ScopeIndent.IncorrectExact
						]
					} // phpcs:ignore Generic.WhiteSpace.ScopeIndent.IncorrectExact
				]
			},
			dynamic: {
				expand: true,
				cwd: 'css/',
				dest: 'css/',
				ext: '-rtl.css',
				src: ['**/style.css']
			}
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
	grunt.loadNpmTasks( 'grunt-rtlcss' );
	grunt.loadNpmTasks( 'grunt-postcss' );
	grunt.loadNpmTasks( 'grunt-sass-globbing' );
	grunt.loadNpmTasks( 'grunt-contrib-watch' );

	grunt.registerTask( 'css', [ 'sass_globbing', 'sass', 'postcss', 'rtlcss:dynamic' ] );

	grunt.registerTask( 'default', [ 'css' ] );
	grunt.registerTask( 'build', [ 'css' ] ); // Automatically runs "production" steps
};
