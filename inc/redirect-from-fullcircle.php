<?php
/**
 * Plugin Name: Redirect from Full Circle (Legacy URL Redirector)
 * Description: Handles redirects from the legacy Full Circle site’s `/category/slug` permalink structure
 *              to the new WordPress canonical permalink format (`/%year%/%monthnum%/%postname%/`).
 *
 * Version:     1.0.0
 * Author:      ASU Engineering
 * License:     GPL-2.0-or-later
 *
 * ------------------------------------------------------------------------
 * DEPLOYMENT NOTES
 * ------------------------------------------------------------------------
 * • This file is stored in the child theme repository under:
 *       /inc/redirect-from-fullcircle.php
 *   for version control and tracking.
 *
 * • To activate it, manually copy the file into the MU plugin directory:
 *
 * • This plugin only executes during `template_redirect` on 404s.
 *   It looks for URLs matching the legacy pattern `/category/slug/`
 *   and issues a 301 redirect to the canonical permalink for the
 *   matching post, if found.
 *
 * • Safe to leave active indefinitely; it introduces minimal overhead
 *   since it runs only on 404 requests.
 *
 * ------------------------------------------------------------------------
 * HISTORY
 * ------------------------------------------------------------------------
 * 1.0.0 — Initial release for redirecting legacy Full Circle URLs.
 */

add_action( 'template_redirect', function() {
    // Only when WP didn't already find a valid resource
    if ( ! is_404() ) {
        return;
    }

    // Get the path (no query string), trim slashes
    $path = trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );
    if ( $path === '' ) {
        return;
    }

    // Split into segments. Old structure was category/slug so we expect 2 segments.
    $segments = array_values( array_filter( explode( '/', $path ), 'strlen' ) );
    if ( count( $segments ) !== 2 ) {
        return;
    }

    // Candidate slug is the last segment
    $slug = sanitize_title( $segments[1] );

    // First try: ask WP to resolve the URL /$slug/ to a post ID
    $candidate_url = home_url( "/{$slug}/" );
    $post_id = url_to_postid( $candidate_url );

    // If url_to_postid failed, fallback to direct lookup by post_name (name => slug)
    if ( ! $post_id ) {
        $posts = get_posts( [
            'name'        => $slug,
            'post_type'   => 'post',
            'post_status' => 'publish',
            'numberposts' => 1,
            'fields'      => 'ids',
        ] );
        if ( ! empty( $posts ) ) {
            $post_id = $posts[0];
        }
    }

    if ( $post_id ) {
        $target = get_permalink( $post_id );
        if ( $target ) {
            wp_safe_redirect( $target, 301 );
            exit;
        }
    }

}, 1 );
