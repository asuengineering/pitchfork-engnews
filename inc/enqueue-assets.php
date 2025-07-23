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

add_action( 'enqueue_block_assets', 'pitchfork_child_assets' );
function pitchfork_child_assets() {
	// Get the theme data.
	$the_theme     = wp_get_theme();
	$theme_version = $the_theme->get( 'Version' );

	$css_child_version = $theme_version . '.' . filemtime( get_stylesheet_directory() . '/dist/css/child-theme.css' );
	wp_enqueue_style( 'pitchfork-child-styles', get_stylesheet_directory_uri() . '/dist/css/child-theme.css', array(), $css_child_version );

	$js_child_version = $theme_version . '.' . filemtime( get_stylesheet_directory() . '/dist/js/child-theme.js' );
	wp_enqueue_script( 'pitchfork-child-script', get_stylesheet_directory_uri() . '/dist/js/child-theme.js', array(), $js_child_version );
}

// Enqueue to the admin. Gutenberg editor fixes.
add_action( 'enqueue_block_editor_assets', 'pitchfork_engnews_enqueue_block_editor_scripts' );
function pitchfork_engnews_enqueue_block_editor_scripts() {

	wp_enqueue_script( 'engnews-block-styles', get_stylesheet_directory_uri() . '/dist/js/block-variations.js', array( 'wp-blocks', 'wp-dom' ), null, false );

}
