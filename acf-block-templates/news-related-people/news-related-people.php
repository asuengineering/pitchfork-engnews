<?php
/**
 * News Related People
 * - Produces a thumbnail image and title of the person "tagged" in the story.
 *
 * @package pitchfork_engnews
 * do_action('qm/debug', $asurite_string);
 */

$spacing = pitchfork_blocks_acf_calculate_spacing( $block );

/**
 * Retrieve additional classes from the 'advanced' field in the editor for inline styles.
 */
$block_classes = array('news-featured-img', 'size-full');
if (!empty($block['className'])) {
    $block_classes[] = $block['className'];
}

/**
 *
 * Produce an array of ASURITE IDs from associated post terms.
 * Pass that data to ASU Search API and return results.
 * Finally, build the thumbnail, name and job titles from the given data.
 */

// Get the terms, build the query string for the API call.
$post = get_post();
$terms = get_the_terms($post, 'asu_person');

$asurite = array();
foreach ( $terms as $term ) {
	$asuid = get_field( 'asuperson_asurite', $term );
	$asurite[] = $asuid;
}
$asurite_string = implode(',', $asurite);
$rel_people = get_asu_search_data($asurite_string, true);

// Build output objects
if (! empty ($rel_people)) {
	$profiles = '<div class="related-people">';
	foreach ($rel_people as $person) {
		$profiles .= '<div class="related-person">';
		$profiles .= '<img class="search-image img-fluid" src="' . $person['photo'] . '?blankImage2=1" alt="Portrait of ' . $person['display_name'] . '"/>';
		$profiles .= '<h4 class="display-name"><a href="https://search.asu.edu/profile/' . $person['eid'] . '" title="ASU Search profile for ' . $person['display_name'] . '">' . $person['display_name'] . '</a></h4>';
		$profiles .= '<p class="title">' . $person['title'] . '</p>';
		$profiles .= '<p class="department">' . $person['department'] . '</p>';
		$profiles .= '</div>';
	}
	$profiles .= '</div><!-- end .related-people -->';
}

echo $profiles;
