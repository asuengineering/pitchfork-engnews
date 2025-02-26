<?php
/**
 * UDS Profiles
 * - Creates a wrapper for acf/profile-manual and (data block) to organize the profiles
 * - into a grid. Suitable for building an ad-hoc direectory page.
 *
 * @package Pitchfork_People
 */

$columns = get_field( 'uds_profiles_columns' );
$prefer_dept = get_field( 'uds_profiles_select_dept' );

$spacing = pitchfork_blocks_acf_calculate_spacing( $block );

/**
 * Retrieve additional classes from the 'advanced' field in the editor for inline styles.
 * Explode given string into an array so we can search it later.
 */
$block_classes = array( 'uds-profile-grid', $columns );
if ( ! empty( $block['className'] ) ) {
	$block_classes[] = $block['className'];
}

// Build the profile container.
// $profile  = '<div class="' . implode( ' ', $block_classes ) . '" style="' . $spacing . '">';
// $profile .= '<InnerBlocks allowedBlocks="' . esc_attr( wp_json_encode( $allowed_blocks ) ) . '" template="' . esc_attr( wp_json_encode( $template ) ) . '" />';
// $profile .= '</div>';

// Build the post-header
// $profile  = '<header class="' . implode( ' ', $block_classes ) . '" style="' . $spacing . '">';
// $profile .= '<InnerBlocks allowedBlocks="' . esc_attr( wp_json_encode( $allowed_blocks ) ) . '" template="' . esc_attr( wp_json_encode( $template ) ) . '" />';
// $profile .= '</header>';

// echo $profile;

?>

// Mobile first markup
<header>
	<h1 class="article"><span class="highlight-gold">Human brains teach AI new skills</span></h1>
	<p class="desktop-like-h2" class="excerpt">ASU researcher Ying-Cheng Lai is taking inspiration from human thought processes to improve machine learning strategies</p>

	<div class="attribution">
		<p class="entry-byline"><?php pitchfork_posted_by(); ?></p>
		<p class="entry-date"><?php pitchfork_posted_originally(); ?></p>
	</div>

	<div class="tags">
		<div class="tag-category">Features</div>
		<div class="tag-school">SCAI</div>
		<div class="tag-topics">AI, machine learning, lazers</div>
	</div>
</header>


