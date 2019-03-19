'use strict';

const gulp = require('gulp');
const babel = require('gulp-babel');
const fs = require('fs');
const checktextdomain = require('gulp-checktextdomain');
const wpPot = require('gulp-wp-pot');

// Hooks Symlink
gulp.task('create-hooks-symlink', function (done) {
    fs.symlink('../../.hooks/pre-commit', './.git/hooks/pre-commit', done);
    fs.chmod('./.git/hooks/pre-commit', '755', done);
});

gulp.task('init', gulp.series(['create-hooks-symlink']));

const i18n_config = {
    'text_domain': 'radish-checkout-fields',
    'package': 'radish-checkout-fields',
    'php_files': ['./classes/*.php', './*.php'],
    'cacheFolder': './languages/cache',
    'destFolder': './languages',
    'keepCache': false
};

gulp.task('checktextdomain', function () {
    return gulp
        .src(['.'])
        .pipe(checktextdomain({
            text_domain: 'radish-checkout-fields',
            keywords: [
                '__:1,2d',
                '_e:1,2d',
                '_x:1,2c,3d',
                'esc_html__:1,2d',
                'esc_html_e:1,2d',
                'esc_html_x:1,2c,3d',
                'esc_attr__:1,2d',
                'esc_attr_e:1,2d',
                'esc_attr_x:1,2c,3d',
                '_ex:1,2c,3d',
                '_n:1,2,4d',
                '_nx:1,2,4c,5d',
                '_n_noop:1,2,3d',
                '_nx_noop:1,2,3c,4d'
            ]
        }));
});

gulp.task('generate-pot', function () {
    return gulp.src(i18n_config.php_files)
        .pipe(wpPot({
            domain: i18n_config.text_domain,
            package: i18n_config.package,
            src: i18n_config.php_files,
        }))
        .pipe(gulp.dest(i18n_config.destFolder + '/' + i18n_config.text_domain + '.pot'))
});