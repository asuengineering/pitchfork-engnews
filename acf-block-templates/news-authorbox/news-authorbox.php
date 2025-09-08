<?php
/**
 * News Author Box
 * - Produces the gravatar + profile details of the post assigned author.
 * - Contains an additional option for outputting details of an arbitrary user.
 * - ... which makes the block usable outside of the normal post context
 * - ... or allows more than one author box per page.
 *
 * @package pitchfork_engnews
 */

$spacing = pitchfork_blocks_acf_calculate_spacing( $block );

/**
 * Retrieve additional classes from the 'advanced' field in the editor for inline styles.
 */
$block_classes = array('news-authorbox');
if (!empty($block['className'])) {
    $block_classes[] = $block['className'];
}


/**
 * Which author are we displaying?
 */
$use_author = get_field('engnews_authorbox_use_default');
if ($use_author) {
	$author = get_the_author_meta('ID');
} else {
	$author = get_field('engnews_authorbox_user');
}

do_action('qm/debug', $author);

/**
 * Assign some variables to gather the user profile details.
 */
$author_name = get_the_author_meta('display_name', $author);
$author_profile = get_the_author_meta('description', $author);
$author_phone = get_the_author_meta('phone_number', $author) ?? '855-278-5080';
$author_dept = get_the_author_meta('full_department', $author) ?? 'Arizona State University';
$author_email = get_the_author_meta('user_email', $author);
$avatar = get_avatar( $author , 128);

/**
 * Setting default content in the rare case of unset profile meta values.
 */

if (empty($author_profile)) {
	$author_profile = 'This user is a valued employee or affiliate of Arizona State University.';
}

// if (empty($author_phone)) {
// 	$author_phone = '855-278-5080'; // General phone number for ASU switchboard
// }

// if (empty($author_dept)) {
// 	$author_dept = 'Arizona State University';
// }

$mediaout = '<div class="media-contact"><span class="label">Media contact: </span>';
$mediaout .= '<a class="email" aria-label= "Author email: ' . $author_email . '" href="mailto:' . $author_email . '">' . $author_email . '</a>';
$mediaout .= '<a class="phone" aria-label= "Author phone number: ' . $author_phone . '" href="tel:' . $author_phone . '">' . $author_phone . '</a>';
$mediaout .= '<span class="dept">' . esc_html($author_dept) . '</span>';

$mediaout .= '</div>';


// Build output
$output = '<div class="' . implode(' ', $block_classes) . '" style="' . $spacing . '">';
$output .= $avatar;
$output .= '<h3 class="author-name">' . $author_name . '</h3>';
$output .= '<p class="author-profile">' . $author_profile . '</p>';
$output .= $mediaout;
$output .= '</div>';

echo $output;
