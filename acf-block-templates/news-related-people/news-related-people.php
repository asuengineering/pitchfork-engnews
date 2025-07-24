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

		if ($profile_data['status'] == 'found') {
			$profiles .= '<div class="related-person">';
			$profiles .= '<img class="search-image img-fluid" src="' . $profile_data['photo'] . '?blankImage2=1" alt="Portrait of ' . $profile_data['display_name'] . '"/>';
			$profiles .= '<h4 class="display-name"><a href="https://search.asu.edu/profile/' . $profile_data['eid'] . '" title="ASU Search profile for ' . $profile_data['display_name'] . '">' . $profile_data['display_name'] . '</a></h4>';
			$profiles .= '<p class="title">' . $profile_data['title'] . '</p>';
			$profiles .= '<p class="department">' . $profile_data['department'] . '</p>';
			$profiles .= '</div>';

		} else {

			// Need graceful fallback for a profile that has no data.
			// $profiles .= '<div class="related-person">';
			// $profiles .= Unknown person image?
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
