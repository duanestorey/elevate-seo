var jsLibFiles = [ 
	'./assets/lib/opentip/lib/opentip.js',	// force loading first	
	'./assets/lib/opentip/lib/adapter-jquery.js',
	'./assets/lib/chartjs/Chart.bundle.js',	// force loading first
	'./assets/lib/chartjs/Chart.js',	// force loading first
	'./assets/lib/**/*.js' 
];

var jsAssetFiles = [ 
	'./assets/js/*.js' 
];

var jsFiles = [
	'./build/js/lib.min.js',
	'./build/js/asset.min.js' 
];

var jsFilesNoMin = [
	'./build/js/lib.js',
	'./build/js/asset.js' 
];

var sassFiles = [ 
	'./assets/scss/*.scss'
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
const resize = require( 'gulp-image-resize' );
 
gulp.task( 'image', function() {
	var result =  
		gulp.src( ['./assets/images/*.jpg', './assets/images/*.png'] )
        .pipe( resize( {
                width: 1600,
                filter: 'Catrom'
         }))
   		.pipe( image() )
		.pipe( gulp.dest( './dist/images' ) );

	result = result ||  
		gulp.src( [ './assets/images/*.svg' ] )
		.pipe( gulp.dest( './dist/images' ) );

	return result;
});

gulp.task( 'test', function() {
	return gulp.src( './assets/js/*.js' )
		.pipe( eslint() )
		.pipe( eslint.format() )		
});

gulp.task( 'jsasset', function() {
	return gulp.src( jsAssetFiles )
		.pipe( debug() )
		.pipe( concat( 'asset.js' ) )
		.pipe( gulp.dest( './build/js' ) )
		.pipe( rename( 'asset.min.js' ) )
		.pipe( uglify() )			
		.pipe( gulp.dest( './build/js' ) )
});

gulp.task( 'jslib', function() {
	return gulp.src( jsLibFiles )
		.pipe( debug() )
		.pipe( concat( 'lib.js' ) )
		.pipe( gulp.dest( './build/js' ) )
		.pipe( rename( 'lib.min.js' ) )
		.pipe( uglify() )		
		.pipe( gulp.dest( './build/js' ) )
});

gulp.task( 'jsconcat', function() {
	return gulp.src( jsFiles )
		.pipe( debug() )
		.pipe( concat( 'bundle.min.js' ) )
		.pipe( gulp.dest( './dist/js' ) )
});

gulp.task( 'jsconcatnonmin', function() {
	return gulp.src( jsFilesNoMin )
		.pipe( debug() )
		.pipe( concat( 'bundle.js' ) )
		.pipe( gulp.dest( './dist/js' ) )
});

gulp.task( 'js', gulp.series( 'jsasset', 'jslib', 'jsconcat'  ) );

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

gulp.task( 'image:watch', function() {
	var watcher = gulp.watch( './assets/images/*' );
	watcher.on( 'all', function( e, p, s ) {
		 gulp.series( 'image', 'cache' );
	});	
});

gulp.task( 'js:watch', function() {	
	gulp.series( 'js:assetwatch', 'js:libwatch' );
});

gulp.task( 'fonts', function() {
  return gulp.src( './assets/fonts/*' )
	.pipe( sourcemaps.write( './' ) )
	.pipe( gulp.dest( './dist/fonts' ) );	
})

function allWatch() {
	// SCSS
	gulp.watch( './assets/scss/*.scss', gulp.series( 'sass', 'cache' ) );

	// JS Libraries
	gulp.watch( jsLibFiles, gulp.series( 'jslib', 'jsconcat', 'jsconcatnonmin', 'cache' ) );

	// Our JS
	gulp.watch( jsAssetFiles, gulp.series( 'jsasset', 'jsconcat', 'jsconcatnonmin', 'cache' ) );

	// Images
	gulp.watch( './assets/images/*', gulp.series( 'image', 'cache' ) );

	// Images
	gulp.watch( './assets/fonts/*', gulp.series( 'fonts' ) );	
}

gulp.task( 'default', gulp.series( gulp.parallel( 'sass', 'image', 'js', 'fonts', 'test' ), 'cache' ) );
gulp.task( 'watch', gulp.series( allWatch ) );
