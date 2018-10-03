var jsFiles = [ 
	'./assets/js/*.js', 
	'./lib/opentip/lib/opentip.js',	// force loading first
	'./lib/**/*.js' 
];
var sassFiles = [ 
	'./assets/scss/**/*.scss'
];

var outputFiles = [ './dist/js/*.js', './dist/css/*.css' ];

const gulp = require( 'gulp' );
const image = require( 'gulp-imagemin' );
const sass = require( 'gulp-sass' );
const sourcemaps = require( 'gulp-sourcemaps' );
const concat = require( 'gulp-concat' );
const rename = require( 'gulp-rename' );
const uglify = require( 'gulp-uglify' );
const debug = require( 'gulp-debug' );
const replace = require( 'gulp-replace' );
const timestamp = require( 'unix-timestamp' );
const hash = require( 'gulp-hash-creator' );
const eslint = require( 'gulp-eslint' );
 
gulp.task( 'image', function () {
	return gulp.src( './assets/images/*' )
   		.pipe( image() )
		.pipe( gulp.dest( './dist/images' ) );
});

gulp.task( 'test', function() {
	return gulp.src( './assets/js/*.js' )
		.pipe( eslint() )
		.pipe( eslint.format() )		
});

gulp.task( 'js', function() {
	return gulp.src( jsFiles )
		.pipe( debug() )
		.pipe( concat( 'custom.js' ) )
		.pipe( gulp.dest( './dist/js' ) )
		.pipe( rename( 'custom.min.js' ) )
		.pipe( uglify() )
		.pipe( gulp.dest( './dist/js' ) )
});

gulp.task( 'cache', function() {
	return gulp.src( './assets/php/*.php' )
		.pipe( replace( '{cache_version}', hash( { length: 12, content: timestamp.now() } ) ) )
		.pipe( gulp.dest( './dist/php' ) )
});

gulp.task( 'sass', function () {
  return gulp.src( sassFiles )
 	.pipe( debug() ) 
	.pipe( sourcemaps.init() )
	.pipe( sass( { outputStyle: 'nested' } ).on( 'error', sass.logError ) )
	.pipe( sourcemaps.write( './' ) )
	.pipe( gulp.dest( './dist/css' ) );
});

gulp.task( 'sass:watch', function () {
  gulp.watch( sassFiles, ['sass'] );
});

gulp.task( 'js:watch', function () {
  gulp.watch( jsFiles, ['js']) ;
});

gulp.task( 'cache:watch', function () {
  gulp.watch( outputFiles, ['cache']) ;
});

gulp.task( 'default', [ 'sass', 'image', 'js', 'cache', 'test' ] );
gulp.task( 'watch', [ 'sass:watch', 'js:watch', 'cache:watch' ] );
