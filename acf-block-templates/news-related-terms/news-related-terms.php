<?php
/**
 * News Related Terms
 *
 * - Output a list of associated terms from the currenr post in a list or as button tags.
 * - May 2025: Currently only supports "topic" post type, but can support others with additional logic.
 *
 * @package pitchfork_engnews
 *
 */

/**
 * Set initial get_field declarations.
 */

$display_tax = get_field('news_terms_taxonomy');
$display_type = get_field('news_terms_style');

$post = get_post();

/**
 * Set block classes
 * - Get additional classes from the 'advanced' field in the editor.
 * - Get alignment setting from toolbar if enabled in theme.json, or set default value
 * - Include any default classs for the block in the intial array.
 */

$block_attr = array( 'news-related-terms');
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
 * Use get_the_terms to find related set of terms to style.
 * Loop through each returned term and build the output for the block.
 */

$terms = get_the_terms( $post, $display_tax );

$output = '';
if (! $terms) {

	// Output help text within editor if no terms selected.
	if ( $is_preview ) {
		$output = '<p>No terms to display.</p>';
	}

} else {

	/**
	 * Create outer wrapper for block.
	 * Display type influences the actual HTML element used in the markup.
	 */

	$attr  = implode( ' ', $block_attr );
	$output = $anchor . ' class="' . $attr . '" style="' . $spacing . '">';

	if ( 'list' === $display_type ) {
		$output = '<ul ' . $output;
		foreach ($terms as $term) {
			$term_link = get_term_link( $term );
			$output .= '<li><a href="' . esc_url( $term_link ) . '">' . $term->name . '</a></li>';
		}
		$output .= '</ul>';
	}

	if ( 'tags' === $display_type ) {
		$output = '<div ' . $output;
		foreach ($terms as $term) {
			$term_link = get_term_link( $term );
			$output .= '<a class="btn btn-sm btn-dark" href="' . esc_url( $term_link ) . '">' . $term->name . '</a>';
		}
		$output .= '</div>';
	}
}

/**
 * Echo the output.
 */
echo $output;
