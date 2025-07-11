<?php
/**
 * News Story Peak
 *
 * - Parse the content of a typical post and extract just the first few paragraphs of content.
 *
 * @package pitchfork_engnews
 */

/**
 * Set initial get_field declarations.
 */

$limit = get_field('news_contentpeak_limit');
$postID = get_the_ID();

/**
 * Set block classes
 * - Get additional classes from the 'advanced' field in the editor.
 * - Include any default classs for the block in the intial array.
 */

$block_attr = array( 'news-contentpeak');
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
 * Create the outer wrapper for the block output.
 * Call function to retrieve paragraph content and parse for display.
 */
$attr  = implode( ' ', $block_attr );
$block = '<div ' . $anchor . ' class="' . $attr . '" style="' . $spacing . '">';

$paragraphs = pitchfork_engnews_get_post_content_preview($postID, $limit);
foreach ($paragraphs as $graph) {
	$block .= $graph;
}

/**
 * Close the block, echo the output.
 */
$block .= '</div>';
echo $block;
