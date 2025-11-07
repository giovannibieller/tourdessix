import gulp, { src, series, parallel, dest } from 'gulp';
import * as dartSass from 'sass';
import gulpSass from 'gulp-sass';
import { deleteAsync } from 'del';
import notify from 'gulp-notify';
import uglify from 'gulp-uglify';
import concat from 'gulp-concat';
import babel from 'gulp-babel';
import webpack from 'webpack-stream';

const sass = gulpSass(dartSass);

const paths = {
	root: './',
	assets: './assets',
	node_modules: './node_modules',
	sass: './assets/sass',
	js: './assets/js',
	img: './assets/img',
	ico: './assets/ico',
	create: './create',
	dist: './dist',
	includes: './includes',
};

/**
 * Sass compiler
 */
function sassCompiler() {
	return src([paths.sass + '/main.scss'])
		.pipe(
			sass({ outputStyle: 'compressed' }).on(
				'error',
				notify.onError(function (error) {
					return 'Error: ' + error.message;
				})
			)
		)
		.pipe(concat('main.css'))
		.pipe(dest(paths.dist + '/css'));
}

/**
 * Editor styles compiler
 */
function editorStylesCompiler() {
	return src([paths.sass + '/editor-styles.scss'])
		.pipe(
			sass({ outputStyle: 'compressed' }).on(
				'error',
				notify.onError(function (error) {
					return 'Editor Styles Error: ' + error.message;
				})
			)
		)
		.pipe(concat('editor-style.css'))
		.pipe(dest(paths.dist + '/css'));
}

/**
 * JS compiler
 */
function jsCompiler() {
	return src([paths.js + '/main.js'])
		.pipe(
			webpack({
				mode: 'production',
				output: {
					filename: 'main.min.js',
				},
			})
		)
		.pipe(
			babel({
				presets: ['@babel/env'],
			})
		)
		.pipe(uglify())
		.pipe(dest(paths.dist + '/js'));
}

/**
 * JS libs copy
 */
function copyLibs() {
	return true;
	// return src([])
	// 	.pipe(fileinclude())
	// 	.pipe(dest(paths.dist + '/js/vendor'));
}

/**
 * Copy files in dist
 */
function copyImages() {
	return src([paths.img + '/**/*.*']).pipe(dest(paths.dist + '/img'));
}

function copyIco() {
	return src([paths.ico + '/**/*.*']).pipe(dest(paths.dist + '/ico'));
}

function copyFonts() {
	return src([paths.fonts + '/**/*.*']).pipe(dest(paths.dist + '/fonts'));
}

/**
 * Clean
 */
async function clean(cb) {
	await deleteAsync([paths.dist + '/css']);
	cb();
}

/**
 * Watch
 */
function watchFiles(cb) {
	gulp.watch(
		[paths.sass + '/**/*.scss'],
		series(sassCompiler, editorStylesCompiler)
	);
	gulp.watch([paths.js + '/*.js'], series(jsCompiler));
	cb();
}

const build = series(
	clean,
	copyImages,
	copyIco,
	parallel(sassCompiler, editorStylesCompiler, jsCompiler)
);
const watch = parallel(watchFiles);

export { build, watch, clean, editorStylesCompiler };
export default series(build, watch);
