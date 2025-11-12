<?php
/**
 * News Related Terms
 *
 * - Output a list of associated terms from the current post.
 * - Formatting options: unordered list, button tags or badges.
 * - Taxonomies: topic, school_unit, category
 * - Can be used in a single post (sidebar) or within a query loop.
 *
 * @package pitchfork_engnews
 *
 */

/**
 * Set initial get_field declarations.
 */

$display_tax = get_field('news_terms_taxonomy');
$display_type = get_field('news_terms_style');
$display_preference = get_field('news_terms_prefer');

// $temp_topic       = get_field( 'news_terms_preferred_topic' );
// $temp_school_unit = get_field( 'news_terms_preferred_school' );
// $temp_category    = get_field( 'news_terms_preferred_category' );

// do_action('qm/debug', $temp_school_unit);
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

// Prepare quick arrays for safe comparison
$term_ids   = wp_list_pluck( $terms, 'term_id' );
$term_names = wp_list_pluck( $terms, 'name' );

// No terms found
if ( ! $terms || is_wp_error( $terms ) ) {
	if ( $is_preview ) {
		$output = '<p>' . esc_html__( 'No terms to display.', 'your-text-domain' ) . '</p>';
	}
	echo $output;
	return;
}

/**
 * Create outer wrapper for block.
 * Display type influences the actual HTML element used in the markup.
 */

$attr  = implode( ' ', $block_attr );
$output = $anchor . ' class="' . $attr . '" style="' . $spacing . '">';


if ( 'list' === $display_type ) {

	// Produce the whole list, no further logic needed.
	$output = '<ul ' . $output;
	foreach ($terms as $term) {
		$term_link = get_term_link( $term );
		$output .= '<li><a href="' . esc_url( $term_link ) . '">' . $term->name . '</a></li>';
	}
	$output .= '</ul>';

} else {

	// Rectangle. Set output wrapper to generic <div>
	$output = '<div ' . $output;

	// Are we displaying all the terms?
	if ( 'show' === $display_preference ) {
		foreach ($terms as $term) {
			$term_link = get_term_link( $term );
			$output .= '<span class="badge badge-rectangle">' . $term->name . '</span>';
		}

	} elseif ( 'count' === $display_preference ) {

		$plural_term_name = '';
		$plural_term_name .= match ( $display_tax ) {
			'topic' => 'topics',
			'school_unit' => 'schools',
			'category' => 'categories',
			default => 'terms',
		};

		// Is there more than one term? If so, default to adding just the one term.
		if (count($terms) > 1 ) {
			$output .= '<span class="badge badge-rectangle">' . count($terms) . ' ' . $plural_term_name . '</span>';
		} else {
			$output .= '<span class="badge badge-rectangle">' . $terms[0]->name . '</span>';
		}

	// Preferential display: show the preferred term (if present) and a +n indicator
	} else {

		// Create preferred term ID as an integer from the object in get_term()
		$preferred_term = match ( $display_tax ) {
			'topic'       => get_field( 'news_terms_preferred_topic' ),
			'school_unit' => get_field( 'news_terms_preferred_school' ),
			'category'    => get_field( 'news_terms_preferred_category' ),
			default       => null,
		};

		$preferred_term_id = is_object( $preferred_term ) ? (int) $preferred_term->term_id : (int) $preferred_term;

		// Build an array of term IDs for comparison
		$term_ids = array_map(
			fn( $t ) => (int) $t->term_id,
			is_array( $terms ) ? $terms : []
		);

		// Check membership
		if ( $preferred_term_id && in_array( $preferred_term_id, $term_ids, true ) ) {
			$other_count = max( 0, count( $terms ) - 1 );
			$output .= sprintf(
				'<span class="badge badge-rectangle">%s%s</span>',
				esc_html( $preferred_term->name ),
				$other_count > 0 ? ' and ' . $other_count . ' more' : ''
			);

		} else {

			// Preferred term not found — fall back to a generic representation.
			// Example: show "X categories" or just the first term name. Adjust as desired.
			$plural_term_name = match ( $display_tax ) {
				'topic'       => 'topics',
				'school_unit' => 'schools',
				'category'    => 'categories',
				default       => 'terms',
			};

			if (count($terms) > 1 ) {
				$output .= '<span class="badge badge-rectangle">' . count($terms) . ' ' . $plural_term_name . '</span>';
			} else {
				$output .= '<span class="badge badge-rectangle">' . $terms[0]->name . '</span>';
			}

		}
	}

	// Close the outer <div>
	$output .= '</div>';

}

/**
 * Echo the output.
 */
echo $output;
