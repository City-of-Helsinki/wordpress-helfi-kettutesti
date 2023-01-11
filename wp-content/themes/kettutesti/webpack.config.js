var webpack = require( 'webpack' );
const path = require( 'path' );

module.exports = {
	mode: 'development',
	entry: {
		main: './src/ts/main.ts',
	},
	output: {
		path: path.resolve( __dirname, 'js' ),
		filename: 'main.js',
		libraryTarget: 'umd2',
	},
	externals: {
		'jquery': 'jQuery',
	},
	devtool: 'source-map',
	resolve: {
		modules: [
			'node_modules',
			path.resolve( __dirname, 'src' )
		],

		// Add `.ts`  as a resolvable extension.
		extensions: ['.ts', '.js'],
	},
	plugins: [
		new webpack.ProvidePlugin(
			{
				$: 'jquery',
				jQuery: 'jquery',

			} ),
		new webpack.DefinePlugin(
			{
				'process.env.NODE_ENV': JSON.stringify( 'development' )
			} )
	],
	module: {
		rules: [{
			test: /(\.ts$)|(\.js$)/,        // which files to compile
			loader: 'awesome-typescript-loader',// which loader to use

		}],
	},

};