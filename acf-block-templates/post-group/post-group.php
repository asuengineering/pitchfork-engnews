<?php
/**
 * Post Group
 *
 * - A collection of related posts that include a featured image.
 *
 * @package pitchfork_engnews
 */

/**
 * Set initial get_field declarations.
 * $text_origin is roughly array('arbitrary' + taxonomy terms for post)
 */
$text_origin = get_field('post_group_origin');
$featured_story = get_field('post_group_featured');
$img_override = get_field('post_group_image_upload');

/**
 * Set image override if there is an image selected by the user
 */
if ($img_override) {
	$image = wp_get_attachment_image( $img_override , 'large' );
}

/**
 * Set block classes
 * - Get additional classes from the 'advanced' field in the editor.
 * - Include any default classs for the block in the intial array.
 */

$block_attr = array( 'post-group');
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
 * Build a query loop and output building logic
 * - Selecting indivudual posts frim UI returns an array of post objects directly. No query needed.
 * - Determine and fetch featured image based on field selected if no override.
 * - Construct story divs from query.
 */
if ($text_origin !== 'arbitrary') {

	if ('category' === $text_origin ) {
        $selected = get_field('post_group_category');
    } elseif ('school_unit' === $text_origin ) {
        $selected = get_field('post_group_schoolunit');
    } elseif ('asu_person' === $text_origin ) {
        $selected = get_field('post_group_person');
	} elseif ('topic' === $text_origin ) {
        $selected = get_field('post_group_topic');
	}

	$limit = get_field('post_group_count');
	$offset = get_field('post_group_offset');

    $args = array(
        'post_type' => 'post',
        'posts_per_page' => $limit,
        'offset' => $offset,
        'post_status' => array( 'publish' ),
        'tax_query' => array(
            array(
                'taxonomy' => $text_origin,
                'terms'    => $selected,
            ),
        ),
    );

    $query = new WP_Query($args);
	$posts = $query->posts;

} else {

	// Posts selected in arbitrary manner are returned directly as array of post objects.
	$posts = get_field('post_group_posts');

}

$postcount = 0;
$storydiv = '';

foreach ($posts as $post) {

	$currentID = $post->ID;
	$postcount++;

	if (($postcount == $featured_story) && ( empty($image))) {

		// If this is the featured story, also set the featured image.
		if ( has_post_thumbnail($currentID) ) {
			$image = get_the_post_thumbnail($currentID, 'large' );
		}

		// Mark the associated story active for styling.
		$storydiv .= '<div class="story active">';

	} else {
		$storydiv .= '<div class="story">';
	}

	$storydiv .= '<h3><a href="' . get_the_permalink($currentID) . '" title="' . get_the_title($currentID) . '">';
	$storydiv .= get_the_title($currentID) . '</a></h3>';
	$storydiv .= '<p class="story-date">' . get_the_date( 'F j, Y', $currentID ) . '</p>';
	$storydiv .= '</div>';

}

/**
 * Handle empty divs or unset contols in the UI
 *  - No posts selected, provide warning to user.
 *  - If the image is still unset at this point, deliver empty image SVG instead.
 **/

if (empty($storydiv)) {
	$storydiv = '<div class="story no-content"><h3>No stories selected.</h3></div>';
}

if (empty($image)) {
	$image = '<div class="components-placeholder block-editor-media-placeholder is-medium has-illustration">';
	$image .= '<svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60 60" preserveAspectRatio="none" class="components-placeholder__illustration" aria-hidden="true" focusable="false"><path vector-effect="non-scaling-stroke" d="M60 60 0 0"></path></svg>';
	$image .= '</div>';
}

/**
 * Block output
 */

$attr  = implode( ' ', $block_attr );

$block = '<div ' . $anchor . ' class="' . $attr . '" style="' . $spacing . '">';
$block .= $image . '<div class="story-wrap">' . $storydiv . '</div></div>';
echo $block;
