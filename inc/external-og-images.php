<?php
/**
 * Engineering News - External News featured image scripts.
 *
 * @package pitchfork_engnews
 */

/**
 *
 */
add_action( 'acf/save_post', 'itn_cache_og_image_from_mainurl', 20 );
function itn_cache_og_image_from_mainurl( $post_id ) {
    // Ignore if not a real post
    if ( strpos( $post_id, 'post' ) === false && ! is_numeric( $post_id ) ) {
        return;
    }

    // Get the main URL field, make sure that the URL is valid.
    $main_url = get_field( '_itn_mainurl', $post_id );
    if ( empty( $main_url ) || ! filter_var( $main_url, FILTER_VALIDATE_URL ) ) {
        return;
    }

    // Fetch remote HTML
    $response = wp_remote_get( $main_url, [ 'timeout' => 10 ] );
    if ( is_wp_error( $response ) ) {
        return;
    }

    $html = wp_remote_retrieve_body( $response );
	if ( empty( $html ) ) {
		return;
	}

	// HTML Tag processer usage for parsing the returned results.
	$image_url = null;
	$alt_text  = null;

	$tag_processor = new WP_HTML_Tag_Processor( $html );

	while ( $tag_processor->next_tag( array( 'tag_name' => 'meta' ) ) ) {
		$property = $tag_processor->get_attribute( 'property' );
		$name     = $tag_processor->get_attribute( 'name' );
		$content  = $tag_processor->get_attribute( 'content' );

		// Image URL candidates
		if ( ! $image_url && (
			$property === 'og:image' ||
			$property === 'og:image:secure_url' ||
			$name === 'twitter:image'
		) ) {
			if ( filter_var( $content, FILTER_VALIDATE_URL ) ) {
				$image_url = $content;
			}
		}

		// Twitter alt text
		if ( ! $alt_text && $name === 'twitter:image:alt' ) {
			$alt_text = sanitize_text_field( $content );
		}

		// Fallback alt from OG title
		if ( ! $alt_text && $property === 'og:title' ) {
			$alt_text = sanitize_text_field( $content );
		}

		// Exit early if we have both values
		if ( $image_url && $alt_text ) {
			break;
		}
	}

	// Validate the image URL with a HEAD request
	if ( $image_url ) {
		$head_response = wp_remote_head( $image_url );
		$status_code   = wp_remote_retrieve_response_code( $head_response );

		if ( $status_code === 200 ) {
			update_post_meta( $post_id, '_itn_og_image_url', esc_url_raw( $image_url ) );

			if ( $alt_text ) {
				update_post_meta( $post_id, '_itn_og_image_alt', $alt_text );
			}
			return;
		}
	}

    // If no valid image found or it's unreachable, remove any previous one
    delete_post_meta( $post_id, '_itn_og_image_url' );
	delete_post_meta( $post_id, '_itn_og_image_alt' );
}


/**
 * Adjust _itn_og_image_url field to be read only.
 * Allows for user validation that an image is available from within the post screen.
 */
add_filter('acf/prepare_field/name=_itn_og_image_url', function( $field ) {
	$field['readonly'] = 1;
	$field['disabled'] = 1;
	return $field;
});
