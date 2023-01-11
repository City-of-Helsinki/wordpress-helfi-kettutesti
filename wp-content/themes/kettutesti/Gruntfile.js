module.exports = function (grunt) {
	const commonTasks = [
		'concat',
		'webpack',
		'dart-sass:dev',
		'notify:watch',
		'postcss',
		'watch:postcss',
	];
	const jsTasks = ['webpack', 'notify:js'];
	const cssTasks = [
		'concat',
		'dart-sass:dev',
		'notify:css',
		'postcss',
		'watch:postcss',
	];
	const buildTasks = [
		'concat',
		'webpack',
		'dart-sass:build',
		'postcss',
		'cssmin',
		'watch:postcss',
	];

	const webpackConfig = require('./webpack.config');

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		concat: {
			options: {
				separator: ';',
			},
			dist: {
				src: ['layouts/**/*.scss', '!layouts/layouts.scss'],
				dest: 'layouts/layouts.scss',
			},
		},

		'dart-sass': {
			dev: {
				options: {
					sourceMap: true,
					sourceMapFileInline: false,
				},
				files: {
					'css/style.css': ['src/scss/style.scss'],
				},
			},

			build: {
				options: {
					compress: true,
					sourceMap: false,
				},
				files: {
					'css/style.css': 'src/scss/style.scss',
				},
			},
		},
		webpack: {
			dev: webpackConfig,
			prod: webpackConfig,
		},

		watch: {
			postcss: {
				files: './src/css/**/*.css',
				tasks: ['compile-tailwindcss'],
				options: {
					interrupt: true,
				},
				css: {
					files: [
						'src/scss/*.scss',
						'src/scss/**/*.scss',
						'layouts/*.scss',
						'layouts/**/*.scss',
						'templates/*.scss',
						'templates/**/*.scss',
					],
					tasks: cssTasks,
				},
				js: {
					files: [
						'src/ts/*.ts',
						'src/ts/**/*.ts',
						'layouts/*.ts',
						'layouts/**/*.ts',
						'templates/*.ts',
						'templates/**/*.ts',
					],
					tasks: jsTasks,
				},
				options: {
					nospawn: true,
				},
				config: {
					files: ['Gruntfile.js'],
					tasks: commonTasks,
				},
				livereload: {
					// Here we watch the files the sass task will compile to
					// These files are sent to the live reload server after sass compiles to them
					options: { livereload: true },
					files: ['css/**/*', 'js/**/*'],
				},
			},
		},
		postcss: {
			options: {
				map: true, // inline sourcemaps
				processors: [
					require('tailwindcss')(),
					require('autoprefixer'), // add vendor prefixes
				],
			},
			dist: {
				expand: true,
				cwd: './src/css/',
				src: ['*.css'],
				dest: './css/',
				ext: '.css',
			},
		},
		cssmin: {
			target: {
				files: {
					'css/style.min.css': 'css/style.css',
				},
			},
		},
		notify: {
			watch: {
				options: {
					title: 'kettutesti',
					message: 'build ok',
				},
			},
			css: {
				options: {
					title: 'kettutesti',
					message: 'CSS build ok',
				},
			},
			postcss: {
				options: {
					title: 'kettutesti',
					message: 'tailwind build ok',
				},
			},
			js: {
				options: {
					title: 'kettutesti',
					message: 'JS build ok',
				},
			},
		},
	});

	grunt.loadNpmTasks('grunt-dart-sass');
	grunt.loadNpmTasks('grunt-postcss');
	grunt.loadNpmTasks('grunt-webpack');
	grunt.loadNpmTasks('grunt-contrib-cssmin');

	//grunt.loadNpmTasks( 'grunt-ts' );
	grunt.loadNpmTasks('grunt-notify');
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-watch');

	//grunt.loadNpmTasks( 'grunt-contrib-uglify' );
	//grunt.loadNpmTasks( 'grunt-contrib-cssmin' );

	//grunt.registerTask("default", ["babel"]);
	grunt.registerTask('default', commonTasks.concat(['watch']));
	//grunt.registerTask('compile-tailwindcss', ['postcss']);
	//grunt.registerTask( 'js', jsTasks );
	grunt.registerTask('build', buildTasks);
};
