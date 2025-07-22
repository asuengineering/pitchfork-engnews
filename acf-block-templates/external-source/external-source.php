<?php
/**
 * External News Source
 *
 * - Renders the source of the news article from the taxonomy.
 * - Provides options to include the post date and icon language in the layout.
 * - Deals with block styles for vertical and horizontal presentation.
 *
 * @package pitchfork_engnews
 */

/**
 * Set initial get_field declarations.
 */

$showdate = get_field('external_source_date');

/**
 * Get 'publication' taxonomy information from current post.
 * Block renders ONLY within a query loop, so $post_id should be the queried post.
 * See docs for get_the_terms for optimized way to build the comma separated list!
 */

$pubterms = get_the_terms($post_id, 'publication');
$publications = join(', ', wp_list_pluck($pubterms, 'name'));

/**
 * Get the post date and set the icons
 */

$icon_newspaper = '<span class="fa-light fa-newspaper"></span>';
$icon_date = '<span class="fa-light fa-calendar-days"></span>';

/**
 * Set block classes
 * - Get additional classes from the 'advanced' field in the editor.
 * - Get alignment setting from toolbar if enabled in theme.json, or set default value
 * - Include any default classs for the block in the intial array.
 */

$block_attr = array( 'external-sourceinfo');
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
 * Block output starts here
 */



/**
 * Block output
 */
$attr  = implode( ' ', $block_attr );
$output = '<div ' . $anchor . ' class="' . $attr . '" style="' . $spacing . '">';
$output .= '<div class="publication">' . $icon_newspaper . $publications . '</div>';
if ($showdate) {
	$output .= '<div class="post-date">' . $icon_date . get_the_date( 'F j, Y' ) . '</div>';
}
$output .= '</div>';

echo $output;
