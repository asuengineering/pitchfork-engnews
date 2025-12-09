<?php
/**
 * Pitchfork child theme functions and definitions
 *
 * @package pitchfork-child
 */

 // Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue child scripts and styles.
 * - Current hook makes styles and JS files available in the block editor + the front end of the site.
 * - Enqueued as a dependency of theme files provided by the parent theme.
 *
 * Other hooks and actions available if needed.
 */

add_action( 'enqueue_block_assets', 'pitchfork_engnews_child_assets' );
function pitchfork_engnews_child_assets() {
	// Get the theme data.
	$the_theme     = wp_get_theme();
	$theme_version = $the_theme->get( 'Version' );

	$css_child_version = $theme_version . '.' . filemtime( get_stylesheet_directory() . '/dist/css/child-theme.css' );
	wp_enqueue_style( 'pitchfork-engnews-styles', get_stylesheet_directory_uri() . '/dist/css/child-theme.css', array(), $css_child_version );

}

// Enqueue to the admin. Gutenberg editor fixes.
add_action( 'enqueue_block_editor_assets', 'pitchfork_engnews_enqueue_block_editor_scripts' );
function pitchfork_engnews_enqueue_block_editor_scripts() {

	wp_enqueue_script( 'engnews-block-styles', get_stylesheet_directory_uri() . '/dist/js/block-variations.js', array( 'wp-blocks', 'wp-dom' ), null, false );

}

// Register Isotope assets for filter block
add_action( 'init', 'pitchfork_engnews_register_isotope_assets' );
function pitchfork_engnews_register_isotope_assets() {

    // Register Isotope (CDN). Use minified for production.
    wp_register_script( 'metafizzy-isotope', get_theme_file_uri( '/src/isotope-layout/isotope.pkgd.min.js' ), array(), '3.0.6', true);

    // Register an init script (your small per-block bootstrap)
    wp_register_script( 'metafizzy-isotope-init', get_theme_file_uri( '/dist/js/isotope-init.js' ), array( 'metafizzy-isotope'), '1.0.0', true );
};

add_action( 'wp_enqueue_scripts', 'pitchfork_engnews_enqueue_isotope_assets' );
function pitchfork_engnews_enqueue_isotope_assets() {
    if ( is_page( 'in-the-media' ) || is_page( 'media-isotope' ) ) {
        wp_enqueue_script( 'metafizzy-isotope' );
        wp_enqueue_script( 'metafizzy-isotope-init' );
    }
}
