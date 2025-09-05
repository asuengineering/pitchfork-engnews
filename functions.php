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
require get_stylesheet_directory() . '/inc/cpt-auto-seo-settings.php';
require get_stylesheet_directory() . '/inc/block-bindings.php';
require get_stylesheet_directory() . '/inc/block-filters.php';
require get_stylesheet_directory() . '/inc/block-templates.php';
require get_stylesheet_directory() . '/inc/asu-search.php';
require get_stylesheet_directory() . '/inc/external-og-images.php';
require get_stylesheet_directory() . '/inc/calc-date-ranges.php';
require get_stylesheet_directory() . '/inc/no-duplicat-stories.php';

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
add_filter('user_contactmethods', 'pitchfork_engnews_media_contact_profile_fields');
function pitchfork_engnews_media_contact_profile_fields($user_contact) {

    // Remove the default "Website" field
    unset($user_contact['url']);

	// Add new fields
	$user_contact['phone_number'] = 'Phone Number';
	$user_contact['full_department'] = 'Full Department';

	return $user_contact;
}



/**
 * Remove Yoast social fields from user profile forms.
 * @see https://gist.github.com/amboutwe/36a08f9d369860aec99500726065bd3f
 */
add_filter('user_contactmethods', 'yoast_seo_admin_user_remove_social', 99);
function yoast_seo_admin_user_remove_social ( $contactmethods ) {
	unset( $contactmethods['facebook'] );
	unset( $contactmethods['instagram'] );
	unset( $contactmethods['linkedin'] );
	unset( $contactmethods['myspace'] );
	unset( $contactmethods['pinterest'] );
	unset( $contactmethods['soundcloud'] );
	unset( $contactmethods['tumblr'] );
	unset( $contactmethods['twitter'] );
	unset( $contactmethods['youtube'] );
	unset( $contactmethods['wikipedia'] );
	unset( $contactmethods['mastodon'] ); // Premium feature

	return $contactmethods;
}

