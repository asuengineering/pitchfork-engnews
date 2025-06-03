<?php
/**
 * News Quote
 * - Produces the background texture needed to feature a pull quote from an article.
 *
 * @package pitchfork_engnews
 */

/**
 * No initial get_field declarations.
 */

/**
 * Set block classes
 * - Get additional classes from the 'advanced' field in the editor.
 * - Default classs for the block included in the intial array.
 */

$block_attr = array( 'news-quote');
if ( ! empty( $block['className'] ) ) {
	$block_attr[] = $block['className'];
}

// Add the background-color to block classes.
if ( ! empty( $block['backgroundColor'] ) ) {
	$block_attr[] = 'has-' . $block['backgroundColor'] . '-background-color ';
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
 */
$attr  = implode( ' ', $block_attr );
$block = '<div ' . $anchor . ' class="' . $attr . '" style="' . $spacing . '">';
$block .= '<div class="open-quote"><svg title="Open quote" role="presentation" viewBox="0 0 302.87 245.82">
		   <path d="M113.61,245.82H0V164.56q0-49.34,8.69-77.83T40.84,35.58Q64.29,12.95,100.67,0l22.24,46.9q-34,11.33-48.72,31.54T58.63,132.21h55Zm180,0H180V164.56q0-49.74,8.7-78T221,35.58Q244.65,12.95,280.63,0l22.24,46.9q-34,11.33-48.72,31.54t-15.57,53.77h55Z"/>
		   </svg></div>';

/**
 * Inner Block attributes, example templates and output string.
 */
$allowed_blocks = array( 'core/paragraph' );
$template       = array(
	array(
		'core/paragraph',
		array(
			'content' => 'This is a pull quote for an article. Lorem ipsum dolor sit amet',
		),
	),
);

$block .= '<InnerBlocks class="quote-wrap" allowedBlocks="' . esc_attr( wp_json_encode( $allowed_blocks ) ) . '" template="' . esc_attr( wp_json_encode( $template ) ) . '" />';

/**
 * Outer quote
 */
$block .= '<div class="close-quote"><svg title="Close quote" role="presentation" viewBox="0 0 302.87 245.82">
		   <path d="M113.61,245.82H0V164.56q0-49.34,8.69-77.83T40.84,35.58Q64.29,12.95,100.67,0l22.24,46.9q-34,11.33-48.72,31.54T58.63,132.21h55Zm180,0H180V164.56q0-49.74,8.7-78T221,35.58Q244.65,12.95,280.63,0l22.24,46.9q-34,11.33-48.72,31.54t-15.57,53.77h55Z"/>
		   </svg></div>';

/**
 * Close the block, echo the output.
 */
$block .= '</div>';
echo $block;
