/* jshint node: true */
/* global $: true */
'use strict';

/*
   FIXME/TODO:
   x css sourcemaps not working (they were not properly uploaded)
   x only upload changed files
   * css reload seems to happen before upload is finished -> check again. fix is in upload-styles
   * allow multiple js files -> should be fixed?
   * delete before deploy / or some kind of sync
   * allow inserting bower dependencies via wiredep
   * how to deal with base path being specified in .bowerrc?
   * make this a repo
   * switch to eslint
   * option for ruby-sass
   * compile sass on partial changes
   * use mac notifications for errors
 */


 /**
  *  initialization
  */
// read config files
var config = require('./project.config.json');
var ftpConfig = require(config.ftpConfig);

// init gulp and plugins
var gulp = require('gulp');
var $ = require('gulp-load-plugins')({
   pattern: ['gulp-*', 'gulp.*', 'browser-sync', 'main-bower-files', 'vinyl-ftp', 'uglify-save-license']
});
var joinPath = require('path').join;

// configure ftp
ftpConfig.log = $.util.log;
$.ftp = $.vinylFtp.create(ftpConfig);

/**
 *  utilities
 */
// common error handler vor gulp plugins
var errorHandler = function(title) {
  return function(err) {
    $.util.log($.util.colors.red('[' + title + ']'), err.toString());
    this.emit('end');
  };
};

// get the src glob array from config
// TODO: error handling
var srcPath = function(name) {
   var glob = config.paths[name]['src']; // could be an array of globs
   if (!Array.isArray(glob)) {
      glob = [glob];
   }
   return glob.map(function (el) {
      return joinPath(config.basePath, el);
   });
};
// get the dest path (string) from config (optionally appending and ext to the path)
var destPath = function(name, ext) {
   var path = config.paths[name]['dest']; // needs to be a string
   ext = ext || '';
   return joinPath(config.basePath, path, ext);
};

// console.log(srcPath('styles'));
// console.log(destPath('styles', '*'));
// process.exit();

/**
 *  JS / jshint, uglify, sourcemaps
 */
gulp.task('scripts', ['bower-scripts'], function() {
   return gulp.src(srcPath('scripts') )
      .pipe( $.jshint() )
      .pipe( $.jshint.reporter('jshint-stylish') )
      .pipe( $.sourcemaps.init() )
      .pipe( $.uglify({ preserveComments: $.uglifySaveLicense })).on('error', errorHandler('Uglify') )
      .pipe( $.rename({extname: '.min.js'}) )
      .pipe( $.sourcemaps.write('.') )
      .pipe( gulp.dest(destPath('scripts')) )
      .pipe( $.size({title: "scripts:", showFiles: true}) );
});

gulp.task('bower-scripts', function() {
   // TODO: minify before concat. only files that don't end in .min.js
   var mainBowerFilesOptions = {
      debugging: false,
      includeDev: true,
      checkExistence: true,
      filter: "**/*.js"
   };
   return gulp.src( $.mainBowerFiles(mainBowerFilesOptions) )
      .pipe( $.debug({title: "bower-scripts:"}) )
      // .pipe($.sourcemaps.init())
      .pipe( $.concat('vendor.js') )
      .pipe( $.uglify({ preserveComments: $.uglifySaveLicense })).on('error', errorHandler('Uglify') )
      // .pipe($.rename({extname: '.min.js'}))
      // .pipe($.sourcemaps.write( '.' ))
      .pipe( gulp.dest(destPath('scripts')) )
      .pipe( $.size({title: "bower-scripts:", showFiles: true}) );
});

gulp.task('upload-scripts', ['scripts'], function() {
   var globs = srcPath('scripts'); // sources (glob array)
   globs.push( destPath('scripts', '*')); // add dest folder
   return gulp.src( globs, {base: config.basePath} )
      .pipe( $.cached() ) // only pass through changed files
      // .pipe( $.ftp.newerOrDifferentSize(ftpConfig.remoteBase) )
      .pipe( $.ftp.dest(ftpConfig.remoteBase) )
      .pipe( $.browserSync.stream({ once: true }) )
      .pipe( $.size() );
});



/**
 *  CSS / sass, autoprefixer, minify-css, sourcemaps, livereload
 */
gulp.task('styles', ['bower-styles'], function() {
   var sassOptions = {
      outputStyle: 'expanded'
   };
   return gulp.src( srcPath('styles') )
   //  .pipe($.inject(injectFiles, injectOptions))
   //  .pipe(wiredep(_.extend({}, conf.wiredep)))
      .pipe( $.sourcemaps.init() )
      .pipe( $.sass(sassOptions) ).on( 'error', errorHandler('Sass') )
      .pipe( $.autoprefixer() ).on( 'error', errorHandler('Autoprefixer') )
      .pipe( $.minifyCss() ).on( 'error', errorHandler('Minify CSS') )
      .pipe( $.sourcemaps.write('.') )
      .pipe( gulp.dest(destPath('styles')) )
      .pipe( $.size({title: "styles:", showFiles: true}) );
});

gulp.task('bower-styles', function() {
   var mainBowerFilesOptions = {
      debugging: false,
      includeDev: true,
      checkExistence: true,
      filter: "**/*.css"
   };
   return gulp.src( $.mainBowerFiles(mainBowerFilesOptions) )
      .pipe( $.debug({title: "bower-styles:"}) )
      // .pipe($.sourcemaps.init())
      .pipe( $.concat('vendor.css') )
      .pipe( $.minifyCss() ).on( 'error', errorHandler('Minify CSS') )
      // .pipe($.rename({extname: '.min.js'}))
      // .pipe($.sourcemaps.write( '.' ))
      .pipe( gulp.dest(destPath('styles')) )
      .pipe( $.size({title: "bower-styles:", showFiles: true}) );
});

gulp.task('upload-styles', ['styles'], function() {
   var globs = srcPath('styles'); // sources (glob array)
   globs.push( destPath('styles', '*') ); // add everything in destination folder
   return gulp.src( globs, {base: config.basePath} )
      .pipe( $.cached() ) // only pass through changed files
      .pipe( $.ftp.dest(ftpConfig.remoteBase) ).on('end', function(argument) {
         // console.log("ALL UPLOADED");
      })
      // .pipe( $.ftp.newerOrDifferentSize(ftpConfig.remoteBase) )
      .pipe( $.browserSync.stream({ once: true }) )
      .pipe( $.size() );
});



/**
 *  start the browsersync server
 */
gulp.task('browsersync', [], function() {
   $.browserSync.init({
      proxy: config.browserSyncProxy,
      browser: config.browser
   });
});



/**
 *  build (compile all scripts & styles)
 */
gulp.task('build', ['scripts', 'styles']);



/**
 *  watch styles & scripts (with livereload via browsersync)
 */
gulp.task('watch', ['browsersync', 'build'], function() {
   // watch styles
   gulp.watch( srcPath('styles'), ['styles', 'upload-styles'] );

   // watch scripts
   gulp.watch( srcPath('scripts'), ['scripts', 'upload-scripts']);

   // watch templates etc.
   gulp.watch( srcPath('templates'), function(event) {
      gulp.src( event.path, {base: config.basePath} )
         .pipe( $.cached() ) // only pass through changed files
         .pipe( $.ftp.dest(ftpConfig.remoteBase) )
         .pipe( $.browserSync.stream({ once: true }) )
         .pipe( $.size() );
      // browserSync.reload(event.path);
   });
});



/**
 *  deploy to ftp
 */
gulp.task('deploy', ['build'], function() {
   // upload everything in base folder
   var globs = [
      config.basePath + '/**/*',
      // '!' + config.basePath + '/bower_components/**/*'
   ];
   // TODO: fix paths beginning with !
   // globs = globs.map(function(path) {
   //    return joinPath(config.basePath, path);
   // });
   return gulp.src( globs, {base: config.basePath, buffer: false} )
      .pipe( $.ftp.dest(ftpConfig.remoteBase) );
});



/**
 *  default task
 */
gulp.task('default', ['build']);
