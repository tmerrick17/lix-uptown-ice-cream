'use strict';

const { src, dest, watch } = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const postCss = require('gulp-postcss');
const cssNano = require('cssnano');
const prefix = require('autoprefixer');
const terser = require('gulp-terser');
const concat = require('gulp-concat');
const webpack = require('webpack-stream');
const browserSync = require('browser-sync').create();

const SASS_PATH = 'src/scss/**/*.scss';
const JS_PATH = 'src/js/**/*.js';

function scssMin(callback) {
  src(SASS_PATH)
    .pipe(sass().on('error', sass.logError))
    .pipe(postCss([prefix('last 2 versions'), cssNano()]))
    .pipe(concat('main.min.css'))
    .pipe(dest('./public/css'));
  callback()
};

function jsMin(callback) {
  src(JS_PATH)
    .pipe(webpack())
    .pipe(terser())
    .pipe(dest('./public/js'));
  callback()
}

function watchTask(callback) {
  browserSync.init({
      proxy: 'localhost:8000',
      notify: false
  });
  watch('./**/*.php').on('change', browserSync.reload);
  watch('./**/*.scss').on('change', browserSync.reload);
  watch(SASS_PATH, scssMin);
  watch(JS_PATH, jsMin);
  // watch(IMG_PATH, imgMin);
  // watch(WEBP_PATH, webpImg);
  callback();
}

exports.scssMin = scssMin;
exports.jsMin = jsMin;
exports.watchTask = watchTask;
