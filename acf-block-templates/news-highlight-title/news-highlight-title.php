<?php
/**
 *** News Highlight Title
 *
 * - Produces the title of a post within a query loop block.
 * - Removes the option for a linked title, instead offering highlight colors from the ASU palette.
 *
 * @package pitchfork_engnews
 */

/**
 * Set initial get_field declarations.
 */

$heading = get_field('news_hightitle_level');
$color = get_field('news_hightitle_color');
$article = get_field('news_hightitle_article');
$title = get_the_title();

/**
 * Set block classes
 * - Get additional classes from the 'advanced' field in the editor.
 * - Get alignment setting from toolbar if enabled in theme.json, or set default value
 * - Include any default classs for the block in the intial array.
 */

// Include article class if indicated.
if ($article) {
	$block_attr = array( 'article');
} else {
	$block_attr = array();
}

// Include user defined CSS classes
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
	$anchor = ' id="' . $block['anchor'] . '"';
}

/**
 * Block output, echo.
 */
$attr  = implode( ' ', $block_attr );
$block = '<' . $heading . $anchor . ' class="' . $attr . '" style="' . $spacing . '">';
$block .= '<span class="' . $color . '">' . $title . '</span>';
$block .= '</' . $heading . '>';
echo $block;


