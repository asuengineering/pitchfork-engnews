# Pitchfork Child <br/> A WordPress child theme for Pitchfork

Requires at least: WP 6.0
Requires PHP: 7.4
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

**Contributors**

## Usage Requirements

## Recommended / Required Additional Plugins

- This child theme template contains basic setup for adding blocks via Advanced Custom Fields.
- But, ACF is not a hard requirement to get started with this child theme template.

## Development

The plugin uses [Gulp WP](https://github.com/cr0ybot/gulp-wp) to compile SASS and JS.

- Run `npx gulp-wp` from the project root to trigger the development tools.

An additional Gulp script has been added to `gulpfile.js` to extract a copy of the ASU Unity Bootstrap SASS files from the included library. This allows for the easy linking of the `_variables` file from the design tokens distribution.

- Run `gulp upboot` to update the current version of those files.

A small script to lint the codebase is also included via `composer`. It utilizes the rules outlined in the [WP Coding Standards](https://github.com/WordPress/WordPress-Coding-Standards).

- Run `composer install` to setup this process.
- Use `composer check:cs` to lint the plugin files.
- Use `composer fix:cs` to fix the problems that it can address automatically.

## Block Settings

<hr>

## Release Notes

### Version 0.1

- First stable release of the child theme.
