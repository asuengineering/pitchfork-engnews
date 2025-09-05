<?php
/**
 * Story + Thumb
 * - "Standard" non-card presentation of a story with a small thumbnail to the left.
 * - Linked title, optional badge row including school names
 * - Same element is encoded into archive.php.
 * - Making the pattern available in a block with thw query loop.
 *
 * @package pitchfork_engnews
 */

/**
 * Set initial get_field declarations.
 */

$show_badges = (bool) get_field('storycol_display_badges');
$badge_source = get_field('storycol_badge_terms') ?: [];
$badge_tax = $badge_source['value'];
$badge_label = $badge_source['label'];


/**
 * Set block classes
 * - Get additional classes from the 'advanced' field in the editor.
 * - Get alignment setting from toolbar if enabled in theme.json, or set default value
 * - Include any default classs for the block in the intial array.
 */

$block_attr = array( 'story-thumb');
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
 * Story thumbnail, regular <img> markup
 */
$thumb = '<figure class="thumb-wrap"><img decoding="async" class="" src="';
$thumb .= esc_url(get_the_post_thumbnail_url($post_id, null, 'medium'));
$thumb .= '" alt="' . esc_attr(get_post_meta(get_post_thumbnail_id($post_id), '_wp_attachment_image_alt', true)) . '"';
$thumb .= '/></figure>';

/**
 * Content <div> including excerpt. Closed when rendering $output later.
 */
$content = '<div class="story-thumb-content">';
$content .= '<h3 class="post-title"><a href="' . get_the_permalink($post_id) . '">' . get_the_title($post_id) . '</a></h3>';
$content .= wp_kses_post( get_the_excerpt( $post_id ) );

/**
 * Badges. Options for school or topic.
 */
$badges = '';
if ($show_badges) {
	$badgeterms = get_the_terms( $post_id, $badge_tax );
	if ($badgeterms) {
		$badges = '<div class="badge-row"><span class="visually-hidden">' . $badge_label . '</span>';
		foreach ($badgeterms as $badgeterm) {
			$term_link = get_term_link( $badgeterm );
			$badges .= '<span class="badge text-bg-gray-2">' . $badgeterm->name . '</span>';
		}
		$badges .= '</div>';
	}
}


/**
 * Echo the markup.
 */
$attr  = implode( ' ', $block_attr );
$output = '<div ' . $anchor . ' class="' . $attr . '" style="' . $spacing . '">';
$output .= $thumb . $content . $badges . '</div></div>';


/**
 * Echo the output.
 */
echo $output;
