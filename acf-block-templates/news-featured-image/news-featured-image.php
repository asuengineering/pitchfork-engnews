<?php
/**
 * News Featured Image
 * - A custom block, replaces usage of core/featured-image with output specific to UDS.
 * - Options: Drop shadow, border, caption from media library vs inner blocks
 *
 * @package pitchfork_engnews
 */

$spacing = pitchfork_blocks_acf_calculate_spacing( $block );

$post = get_post();
$featimg = get_the_post_thumbnail($post, 'full', array('class' => 'img-fluid'));

/**
 * Retrieve additional classes from the 'advanced' field in the editor for inline styles.
 */
$block_classes = array('news-featured-img', 'size-full');
if (!empty($block['className'])) {
    $block_classes[] = $block['className'];
}

// Sets InnerBlocks with default content and default block arrangement.
$allowed_blocks = array('core/paragraph');
$template = array(
    array(
        'core/paragraph',
        array(
            'content' => '', // The initial content is empty since it's bound dynamically
            'metadata' => array(
                'bindings' => array(
                    'content' => array(
                        'source' => 'engnews/featured-image-caption'
                    )
				),
				'name' => 'Image Caption (Bound)',
            )
        )
    )
);

// Render the block
$newsimg = '<figure class="' . implode(' ', $block_classes) . '" style="' . $spacing . '">';
$newsimg .= $featimg . '<figcaption>';
$newsimg .= '<InnerBlocks allowedBlocks="' . esc_attr(wp_json_encode($allowed_blocks)) . '" template="' . esc_attr(wp_json_encode($template)) . '" />';
$newsimg .= '</figcaption></figure>';

echo $newsimg;
