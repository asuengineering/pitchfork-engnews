<?php
/**
 * News Related People
 * - Produces a thumbnail image and title of the person "tagged" in the story.
 *
 * @package pitchfork_engnews
 *
 */

/**
 * Retrieve additional classes from the 'advanced' field in the editor for inline styles.
 * Support spacing and padding.
 * Include block.json support for HTML anchor.
 */
$block_classes = array('related-people');
if (!empty($block['className'])) {
    $block_classes[] = $block['className'];
}

// Set loop for producing the correct block style.
if ( str_contains( $block['className'] ?? '', 'is-style-icon-only' ) ) {
	$style = 'icon';
} else {
	$style = 'default';
}

do_action('qm/debug', $block);

$spacing = pitchfork_blocks_acf_calculate_spacing( $block );

$anchor = '';
if ( ! empty( $block['anchor'] ) ) {
	$anchor = 'id="' . $block['anchor'] . '"';
}

/**
 *
 * Produce an array of ASURITE IDs from associated post terms.
 * Pass that data to ASU Search API and return results.
 * Finally, build the thumbnail, name and job titles from the given data.
 */

// Get the terms, build the query string for the API call.
$terms = get_the_terms($post_id, 'asu_person');

$profiles = '';

if ( $terms ) {
	foreach ( $terms as $term ) {
		$profile_data = get_asu_person_profile( $term );
		$term_link = get_term_link( $term);

		if ($profile_data['status'] == 'found') {

			if ( $style == 'icon' ) {

				$profiles .= '<div class="related-person">';
				$profiles .= '<a href="' . $term_link . '" title="Profile for ' . $profile_data['display_name'] . '">';
				$profiles .= '<img class="search-image img-fluid" src="' . $profile_data['photo'] . '?blankImage2=1" alt="Portrait of ' . $profile_data['display_name'] . '"/>';
				$profiles .= '</a></div>';

			} else {

				$profiles .= '<div class="related-person">';
				$profiles .= '<img class="search-image img-fluid" src="' . $profile_data['photo'] . '?blankImage2=1" alt="Portrait of ' . $profile_data['display_name'] . '"/>';
				$profiles .= '<h4 class="display-name"><a href="' . $term_link . '" title="Profile for ' . $profile_data['display_name'] . '">' . $profile_data['display_name'] . '</a></h4>';
				$profiles .= '<p class="title">' . $profile_data['title'] . '</p>';
				$profiles .= '<p class="department">' . $profile_data['department'] . '</p>';
				$profiles .= '</div>';

			}

		} else {

			// Need graceful fallback for a profile that has no data.
			$unk_desc = get_field('asuperson_default_desc', $term);

			$profiles .= '<div class="related-person unknown">';
			$profiles .= '<img class="search-image img-fluid" src="' . get_stylesheet_directory_uri() . '/img/unknown-person.png" alt="Unknown person"/>';
			$profiles .= '<h4 class="display-name"><a href="' . $term_link . '" title="Profile for ' . $term->name . '">' . $term->name . '</a></h4>';
			$profiles .= '<p class="department">' . $unk_desc . '</p>';
			$profiles .= '</div>';
		}

	}

}

/**
 * Create the outer wrapper for the block output.
 */
$attr  = implode( ' ', $block_classes );
$output = '<div ' . $anchor . ' class="' . $attr . '" style="' . $spacing . '">';

// Build output objects
if ( empty ($profiles)) {

	// Output help text within editor if no terms selected.
	if ( $is_preview ) {
		$profiles .= '<p>No terms assigned to the post.</p>';
	}

}

$output .= $profiles . '</div>';
echo $output;
