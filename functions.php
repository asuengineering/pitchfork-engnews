<?php
/**
 * Pitchfork child theme functions
 *
 * @package pitchfork-engnews
 */

 // Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require get_stylesheet_directory() . '/inc/enqueue-assets.php';
require get_stylesheet_directory() . '/inc/custom-post-types.php';
require get_stylesheet_directory() . '/inc/block-bindings.php';
require get_stylesheet_directory() . '/inc/asu-search.php';

require get_stylesheet_directory() . '/inc/acf-register.php';

/**
 * Remove post thumbnail height and width values.
 * TODO: Relocate this function to another include?
 * TODO: Add conditional so that ONLY normal news template is affected.
 */
add_filter( 'post_thumbnail_html', 'remove_thumbnail_width_height', 10, 5 );
function remove_thumbnail_width_height( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
    $html = preg_replace( '/(width|height)=\"\d*\"\s/', "", $html );
    return $html;
}

/**
 * Add profile details for media contact
 * TODO: Move to separate include?
 */
function pitchfork_engnews_media_contact_profile_fields($user_contact) {

    // Remove the default "Website" field
    unset($user_contact['url']);

	// Add new fields
	$user_contact['phone_number'] = 'Phone Number';
	$user_contact['full_department'] = 'Full Department';

	return $user_contact;
}

add_filter('user_contactmethods', 'pitchfork_engnews_media_contact_profile_fields');


