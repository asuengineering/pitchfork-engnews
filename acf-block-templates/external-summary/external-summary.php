<?php
/**
 * External News Summary
 *
 * - Creates an expandable selectino of text associated with an external news story.
 * - The layout of eng news will actively discourage linking directly to any /external post
 * - So, this block represents the typical only way to view the context of a captured media reference.
 *
 * @package pitchfork_engnews
 */

/**
 * Set initial get_field declarations.
 */

$display = get_field('external_summary_display');
$opentext = ($display) ? 'open' : '';

$content = get_the_content();


/**
 * Set block classes
 * - Get additional classes from the 'advanced' field in the editor.
 * - Get alignment setting from toolbar if enabled in theme.json, or set default value
 * - Include any default classs for the block in the intial array.
 */

$block_attr = array( 'external-summary');
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
 * Block logic here.
 */

/**
 * Block output.
 */
$attr  = implode( ' ', $block_attr );
$output = '<details ' . $anchor . ' class="' . $attr . '" style="' . $spacing . '" ' . $opentext . '>';

$output .= '<summary>Show context</summary>';

$output .= '<div class="summary-text summary-' . $post_id . '">';
$output .= $content;

$output .= '</details>';
echo $output;
