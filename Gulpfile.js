'use strict';

const gulp = require('gulp');
const babel = require('gulp-babel');
const fs = require('fs');

// Hooks Symlink
gulp.task('create-hooks-symlink', function (done) {
    fs.symlink('../../.hooks/pre-commit', './.git/hooks/pre-commit', done);
    fs.chmod('./.git/hooks/pre-commit', '755', done);
});

gulp.task('init', gulp.series(['create-hooks-symlink']));