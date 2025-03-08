<?php
/**
 * News Article Grid
 *
 * - Custom block. Provides <article> tag for post content + CSS grid to allow
 * - for child elements to easily "break" the grid and extend full-width
 *
 * @package pitchfork_engnews
 */

$spacing = pitchfork_blocks_acf_calculate_spacing( $block );

/**
 * Retrieve additional classes from the 'advanced' field in the editor for inline styles.
 */
$block_classes = array('news-article');
if (!empty($block['className'])) {
    $block_classes[] = $block['className'];
}

// Sets InnerBlocks with default content and default block arrangement.
$template = array(
    array(
        'core/paragraph',
        array(
            'content' => 'News article content starts here.',
        )
    )
);

// Render the block
$articlegrid = '<article class="' . implode(' ', $block_classes) . '" style="' . $spacing . '">';
$articlegrid .= '<InnerBlocks class="news-grid" template="' . esc_attr(wp_json_encode($template)) . '" />';
$articlegrid .= '</article>';

echo $articlegrid;
