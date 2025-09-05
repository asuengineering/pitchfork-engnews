<?php
/**
 *   Grid links for News terms
 *
 * - Not much left to the imagination here. Naming is hard. =)
 *
 * @package pitchfork_engnews
 */


/**
 * Set initial get_field declarations.
 * Defaults set here match the default values in ACF. Just in case. =)
 */
$columns = get_field('taxgridlink_column_count') ?? 'two-columns';
$source = get_field('taxgridlink_source') ?? 'topic';

/**
 * Set block classes
 * - Get additional classes from the 'advanced' field in the editor.
 * - Get alignment setting from toolbar if enabled in theme.json, or set default value
 * - Include any default classs for the block in the intial array.
 */

$block_attr = array( 'uds-grid-links', $columns);
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
 */
$attr  = implode( ' ', $block_attr );
$output = '<div ' . $anchor . ' class="' . $attr . '" style="' . $spacing . '">';

/**
 * Which terms should we display?
 */


$taxselect = match ($source) {
	'topics' 		=> 'taxgridlink_topics',
	'asu_person' 	=> 'taxgridlink_faculty',
	'school_unit' 	=> 'taxgridlink_school',
	default 		=> 'taxgridlink_topics',
};

$terms = get_field($taxselect);
if ( $terms ) {
	foreach ( $terms as $gridterm ) {
		// do_action('qm/debug', $gridterm);
		if ( $source === 'school_unit') {
			$output .= '<a href="' . esc_url( get_term_link( $gridterm ) ) . '">' . esc_html( $gridterm->description ) . '</a>';
		} else {
			$output .= '<a href="' . esc_url( get_term_link( $gridterm ) ) . '">' . esc_html( $gridterm->name ) . '</a>';
		}
	}
} else {
	$output .= '<a href="#">No terms selected.</a>';
}

/**
 * Close the block, echo the output.
 */
$output .= '</div>';
echo $output;
