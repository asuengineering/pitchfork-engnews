<?php
/**
 * News Related People
 * - Produces a thumbnail image and title of the person "tagged" in the story.
 *
 * @package pitchfork_engnews
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
 * Run a query to get the post terms associated with the post.
 * Produce an array of ASURITE IDs from that data to pass to ASU Search API
 * Finally, build the thumbnail, name and job titles from the given data.
 *
 * NOTE: It may be enough to produce some fake content for the block while the story is being edited
 * and reserve the actual API call to ONLY the published article.
 */
$post = get_post();


// Sets InnerBlocks with default content and default block arrangement.
// $allowed_blocks = array('core/paragraph');
// $template = array(
//     array(
//         'core/paragraph',
//         array(
//             'content' => '', // The initial content is empty since it's bound dynamically
//             'metadata' => array(
//                 'bindings' => array(
//                     'content' => array(
//                         'source' => 'engnews/featured-image-caption'
//                     )
// 				),
// 				'name' => 'Image Caption (Bound)',
//             )
//         )
//     )
// );

// // Render the block
// $newsimg = '<figure ' . $anchor . ' class="' . implode(' ', $block_classes) . '" style="' . $spacing . '">';
// $newsimg .= $featimg . '<figcaption>';
// $newsimg .= '<InnerBlocks allowedBlocks="' . esc_attr(wp_json_encode($allowed_blocks)) . '" template="' . esc_attr(wp_json_encode($template)) . '" />';
// $newsimg .= '</figcaption></figure>';

// do_action('qm/debug', $newsimg);
// echo $newsimg;
