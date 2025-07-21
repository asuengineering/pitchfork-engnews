<?php
/**
 * External News Headline
 *
 * - Displays a linked headline associated with an external news story.
 *
 * @package pitchfork_engnews
 */

/**
 * Set initial get_field declarations.
 */
$heading = get_field('external_head_level');
$link = get_field('_itn_mainurl', $post_id);
$title = get_the_title();

/**
 * Set block classes
 * - Get additional classes from the 'advanced' field in the editor.
 * - Get alignment setting from toolbar if enabled in theme.json, or set default value
 * - Include any default classs for the block in the intial array.
 */

$block_attr = array( 'external-headline');
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
 * Block output, echo.
 */
$attr  = implode( ' ', $block_attr );
$output = '<' . $heading . $anchor . ' class="' . $attr . '" style="' . $spacing . '">';
if ($link) {
	$output .= '<a href="' . esc_url($link) . '" target="_blank">' . $title . '</a>';
}
$output .= '</' . $heading . '>';

echo $output;

