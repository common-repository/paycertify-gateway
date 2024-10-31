const gulp = require('gulp');
const babel = require('gulp-babel');
const uglify = require('gulp-uglify');
 
gulp.task('default', () => {
  return gulp.src('src/paycertify.js')
             .pipe(babel({ presets: ['es2015'] }))
             .pipe(uglify())
             .pipe(gulp.dest('dist'));
});

gulp.task('watch', ['default'], () => {
  gulp.watch('src/paycertify.js', ['default']);
});
