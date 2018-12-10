let gulp = require('gulp'),
    less = require('gulp-less'),
    concat = require('gulp-concat'),
    rename = require('gulp-rename'),
    uglify = require('gulp-uglify'),
    minifyCss = require('gulp-clean-css'),
    path = require('path'),
    fs = require('fs'),
    watch = require('gulp-watch'),
    config = JSON.parse(fs.readFileSync("config.json")),
    webpack = require('webpack-stream');

function swallowError (error) {
    console.log(error.toString());

    this.emit('end')
}

gulp.task('webpack', () => {
    return gulp.src(['js/app.js'])
        .pipe(webpack(require('./webpack.config.js')))
        .pipe(gulp.dest('./.tmp/js'));
});

gulp.task('less', () => {
    return gulp.src('less/theme.less')
        .pipe(less({
            rootpath: 'css'
        }))
        .on('error', swallowError)
        .pipe(rename("gs3.min.css"))
        .pipe(gulp.dest('.'));
});

gulp.task('js', ['webpack'], () => {
    return gulp.src(config.jsFiles)
        .pipe(concat("gs3.min.js", {
            newLine: "\n"
        }))
        .pipe(gulp.dest('.'));
});

gulp.task('js:backend', () => {
    return gulp.src(config.jsFilesBackend)
        .pipe(concat("gs3_backend.min.js", {
            newLine: "\n"
        }))
        .pipe(gulp.dest('.'));
});

gulp.task('uglify', ["js", "js:backend"], () => {
    return gulp.src("./gs3.min.js")
        .pipe(uglify())
        .on('error', swallowError)
        .pipe(gulp.dest('.'));
});

gulp.task('cssmin', ["less"], () => {
    return gulp.src("./*.min.css")
        .pipe(minifyCss({processImport: false}))
        .pipe(gulp.dest('.'));
});

gulp.task('default', ['less', 'js', 'js:backend'], function() {
    gulp.watch(config.watchJsFiles, ['js']);
    gulp.watch(config.jsFilesBackend, ['js:backend']);
    gulp.watch(config.lessFiles, ['less']);
});

gulp.task('prod', ['less', 'js', 'js:backend', "cssmin", "uglify"]);