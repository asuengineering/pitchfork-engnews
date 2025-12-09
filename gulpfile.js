const gulp = require('gulp');

// Require GulpWP and pass your local `gulp` instance to it
const gulpWP = require('gulp-wp')(gulp);

/**
 * Alter default task to omit POT generation step.
 * DEFAULT: gulp.task('default', gulp.parallel('compile-sass', 'compile-js', 'generate-pot'));
*/
// gulp.task('default', gulp.parallel('compile-sass', 'compile-js'));

/**
 * Copy assets from node_modules.
 * Run: gulp upboot
 *
 * Does the following:
 * 1. Copies SASS files from unity-boostrap-theme to /src.
 *
 */

gulp.task("upboot", function (done) {

	var paths = {
		"node": "./node_modules",
		"dev": "./src",
	}

	/** ----------------------------------------------------------
	Part 1. Assembling the assets for UDS Bootstrap design kit.

	Child themes don't need to compile the whole BS package, but the
	variables SASS partial can be included directly in the SASS for the project.
	------------------------------------------------------------- */
	// Copy UDS SCSS files from the node /src folder.
	gulp
		.src(paths.node + "/@asu/unity-bootstrap-theme/src/scss/**/*.scss")
		.pipe(gulp.dest(paths.dev + "/unity-bootstrap-theme"));

	done();

	/**
	 * ----------------------------------------------------------
	 * Part 2. Copy Isotope (minified only)
	 * ----------------------------------------------------------
	 * - Copies isotope-layout/dist/isotope.pkgd.min.js
	 * - Places it in /src/isotope-layout/
	 */
	gulp
		.src(paths.node + "/isotope-layout/dist/isotope.pkgd.min.js")
		.pipe(gulp.dest(paths.dev + "/isotope-layout"));

	done();

});
