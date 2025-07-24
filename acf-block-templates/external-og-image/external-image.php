<?php
/**
 * External News - Social image
 *
 * - Displays the social media image associated with the URL for an external post.
 *
 * @package pitchfork_engnews
 */

/**
 * No specific block fields, but we'll need to get meta from the post.
 */
$image_url = get_post_meta($post_id, '_itn_og_image_url', true);
$image_alt = get_post_meta($post_id, '_itn_og_image_alt', true);
$thumb = '';

$emptyimg = '<div class="card-img-top components-placeholder block-editor-media-placeholder is-medium has-illustration">';
$emptyimg .= '<svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60 60" preserveAspectRatio="none" class="components-placeholder__illustration" aria-hidden="true" focusable="false"><path vector-effect="non-scaling-stroke" d="M60 60 0 0"></path></svg>';
$emptyimg .= '</div>';


/**
 * Set block classes
 * - Get additional classes from the 'advanced' field in the editor.
 * - Include any default classs for the block in the intial array.
 */

$block_attr = array( 'external-social');
if ( ! empty( $block['className'] ) ) {
	$block_attr[] = $block['className'];
}

/**
 * Additional margin/padding settings
 * Returns a string for inclusion with style=""
 */
$spacing = pitchfork_blocks_acf_calculate_spacing( $block );

/**
 * Include block.json support for HTML anchor.
 */
$anchor = '';
if ( ! empty( $block['anchor'] ) ) {
	$anchor = 'id="' . $block['anchor'] . '"';
}

/**
 * Build OG image if there is a URL found for it in the meta.
 * Override the OG image with the post thumbnail if there's one set.
 * Final fallback: If in the editor, and these are unset, use empty SVG markup.
 */
if ($image_url) {
	$thumb = '<img src="' . $image_url . '" alt="' . $image_alt . '" class="img-fluid" loading="lazy" decoding="async"/>';
	$block_attr[] = 'social';
}

if ( has_post_thumbnail( $post_id ) ) {
	$thumb_id  = get_post_thumbnail_id( $post_id );
	$thumb_url = wp_get_attachment_image_url( $thumb_id, 'full' );
	$thumb_alt = get_post_meta( $thumb_id, '_wp_attachment_image_alt', true );

	$thumb = '<img src="' . esc_url( $thumb_url ) . '" alt="' . esc_attr( $thumb_alt ) . '" class="img-fluid" loading="lazy" decoding="async">';
	$block_attr[] = 'featured-img';
}

if ((empty($thumb)) && (is_preview())) {
	$thumb = $emptyimg;
}



/**
 * Create the wrapper for the block output.
 */
$attr  = implode( ' ', $block_attr );
$output = '<div ' . $anchor . ' class="' . $attr . '" style="' . $spacing . '">';
$output .= $thumb;
$output .= '</div>';
echo $output;
