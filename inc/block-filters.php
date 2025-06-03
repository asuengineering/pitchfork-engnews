<?php
/**
 * Block filters
 * - Add to Any support via a filter to keep the markup of the actual blocks cleaner.
 * - Helps to possibly support a different product for share intent buttons in the future.
 *
 * @package pitchfork-engnews
 *
 */

add_filter( 'render_block', 'append_addtoany_to_news_blocks', 10, 2 );
function append_addtoany_to_news_blocks( $block_content, $block ) {
    $target_blocks = [
        'acf/news-featured-image',
        'acf/news-authorbox',
    ];

    if ( isset( $block['blockName'] ) && in_array( $block['blockName'], $target_blocks, true ) ) {
        ob_start();
        if ( function_exists( 'ADDTOANY_SHARE_SAVE_KIT' ) ) {
            ADDTOANY_SHARE_SAVE_KIT();
        }
        $addtoany_output = ob_get_clean();

        // Optional wrapper for styling
        $block_content .= '<div class="addtoany-wrapper">' . $addtoany_output . '</div>';
    }

    return $block_content;
}
