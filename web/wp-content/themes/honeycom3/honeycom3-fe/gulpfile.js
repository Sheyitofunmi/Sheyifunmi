'use strict';

// Load plugins
const { src, dest, task, watch, series, parallel } = require('gulp');
const gulp = require('gulp');
const concat = require('gulp-concat');
const filter = require('gulp-filter');
const path = require('path');
const fs = require('fs');
const notify = require('gulp-notify');
const plumber = require('gulp-plumber');
const sass = require('gulp-sass');
const svgmin = require('gulp-svgmin');
const svgstore = require('gulp-svgstore');
const uglify = require('gulp-uglify');
const babel = require('gulp-babel');
const autoprefixer = require('gulp-autoprefixer');
const sourcemaps = require('gulp-sourcemaps');
const image = require('gulp-imagemin');
const browsersync = require('browser-sync').create();

// Set the default paths
const dir = {
    src: {
        honeycomb: 'public/assets',
        project: '../assets/src'
    },
    dest: '../assets'
};

const paths = {
    base_sass: `${dir.src.honeycomb}/sass/**/*.scss`,
    new_sass: `${dir.src.project}/sass/**/*.scss`,
    base_js: `${dir.src.honeycomb}/js/*.js`,
    new_js: `${dir.src.project}/js/*.js`,
    base_svg: `${dir.src.honeycomb}/svg/icons/*.svg`,
    new_svg: `${dir.src.project}/svg/*.svg`,
    base_images: `${dir.src.honeycomb}/images/**/*`,
    new_images: `${dir.src.project}/images/**/*`,
    base_fonts: `${dir.src.honeycomb}/webfonts/**/*`,
    new_fonts: `${dir.src.project}/webfonts/**/*`
};

// Error handling
const onError = function (err) {
    notify.onError({
        title: 'Gulp',
        subtitle: 'Failure!',
        message: 'Error: <%= error.message %>',
    })(err);

    this.emit('end');
};

// Browsersync config
const bsConfig = {
    open: true,
    proxy: "http://wp-starter:8888/"

	// For HC3 dev
	// proxy: "http://hc3-starter:8888/"
};

// Compile css files from sass
task('sass', function () {
    return src([paths.base_sass, paths.new_sass])
        .pipe(sourcemaps.init())
        .pipe(plumber({ errorHandler: onError }))
        .pipe(sass({ outputStyle: 'compressed' }))
        .pipe(autoprefixer({ cascade: false }))
        .pipe(sourcemaps.write('/maps'))
        .pipe(dest(`${dir.dest}/css`))
        .pipe(browsersync.reload({ stream: true }));
});

// Compile js files from plugins
task('scripts', function () {
    const filterDuplicates = filter(function (file) {
      // allow all files in the project src dir
      if (file.dirname.endsWith('src/js')) {console.log(file.path); return true;}
      else {
        // We're in the honeycomb dir. Generate full path for the same file but
        // in the project dir to check if we should exclude the honeycomb one.
        let parentPath = file.cwd.substring(0, file.cwd.lastIndexOf('/'));
        let projectTestPath = path.join(parentPath, dir.src.project.replace('..', ''), 'js/', file.relative);
        // if the the honeycomb script file exists in the project src folder, don't gulp it.
        if (fs.existsSync(projectTestPath)) return false
      }
      console.log(file.path);
      return true;
    });

    return src([paths.new_js, paths.base_js, `!${dir.src.honeycomb}/js/fixed-header.js`])
        .pipe(filterDuplicates)
        .pipe(plumber({ errorHandler: onError }))
        .pipe(babel({ presets: [['@babel/env', { modules: false }]] }))
        .pipe(concat('core.min.js'))
        .pipe(uglify())
        .pipe(dest(`${dir.dest}/js`))
        .pipe(browsersync.reload({ stream: true }));
});

// compile SVGs
task('svg', function () {
    return src([paths.base_svg, paths.new_svg])
        .pipe(
            svgmin({
                plugins: [
                    {
                        removeAttrs: {
                            attrs: '(fill|stroke)',
                        },
                    },
                ],
            })
        )
        .pipe(svgstore())
        .pipe(dest(`${dir.dest}/svg`))
        .pipe(browsersync.reload({ stream: true }));
});

// compile images
task('images', function () {
    return src([paths.base_images, paths.new_images])
        .pipe(image())
        .pipe(dest(`${dir.dest}/images`))
        .pipe(browsersync.reload({ stream: true }));
});

// Copy task for fonts
task('fonts', function() {
    return src([paths.base_fonts, paths.new_fonts])
        .pipe(dest(`${dir.dest}/webfonts`))
        .pipe(browsersync.reload({ stream: true }));
});

// Build task. Run this to re-generate static files
task('build', parallel('sass', 'scripts', 'images', 'svg', 'fonts'));

// For HC3 dev
// task('build', parallel('sass', 'scripts'));



// Watch the sass and scripts for changing and compile on save
task('watch', () => {
    watch([paths.base_sass, paths.new_sass], series('sass')); //, done => { notify('Sass compiled').write(''); done(); }
    watch([paths.base_js, paths.new_js], series('scripts')); //, done => { notify('Scripts compiled').write(''); done(); }
    watch([paths.base_images, paths.new_images], series('images')); //, done => { notify('Images compiled').write(''); done(); }
    watch([paths.base_svg, paths.new_svg], series('svg')); //, done => { notify('SVGs compiled').write(''); done(); }
    watch([paths.base_fonts, paths.new_fonts], series('fonts')); //, done => { notify('Fonts copied').write(''); done(); }
});

// Dev task. Run this for quick development
task('dev', series('build', parallel(done => { browsersync.init(bsConfig); done(); }, 'watch')));

// Default task. This is for when you just type 'gulp' on the command line
task('default', series('build'));
