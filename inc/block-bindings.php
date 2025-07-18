<?php
/**
 * Block bindings
 * - Featured image caption from media library, bound to <p> in news feat img block.
 *
 * @package pitchfork-engnews
 *
 */

/**
 * Block binding init script
 */

add_action( 'init', 'pitchfork_engnews_register_block_bindings' );
function pitchfork_engnews_register_block_bindings() {

	register_block_bindings_source( 'engnews/featured-image-caption', array(
		'label'              => __( 'Featured Image Caption', 'pitchfork_engnews' ),
		'get_value_callback' => 'pitchfork_engnews_ficaption_binding'
	) );

	register_block_bindings_source( 'engnews/queried-story-permalink', array(
		'label'              => __( 'Queried permalink', 'pitchfork_engnews' ),
		'get_value_callback' => 'pitchfork_engnews_loop_permalink_binding',
		'uses_context' => array( 'postId' ),
	) );

	register_block_bindings_source( 'engnews/queried-story-excerpt', array(
		'label'              => __( 'Queried Excerpt', 'pitchfork_engnews' ),
		'get_value_callback' => 'pitchfork_engnews_loop_excerpt_binding',
		'uses_context' => array( 'postId' ),
	) );

}

/**
 * Find featured image, grab the caption from the media library.
 * Return empty if no caption found or used outside of loop.
 */
function pitchfork_engnews_ficaption_binding() {
	$post_id = get_the_ID();
    if (!$post_id) {
        return '';
    }

    $thumbnail_id = get_post_thumbnail_id($post_id);
    if ($thumbnail_id) {
        $caption = wp_get_attachment_caption($thumbnail_id);
        return $caption ? $caption : '';
    }

    return '';
}

/**
 * Return the permalink of a queried post in a post loop.
 */

function pitchfork_engnews_loop_permalink_binding() {

	// Within a query loop, the context of get_the_id should be the queried object.
	$post_id = get_the_ID();
    if (!$post_id) {
        return '';
    }

	return get_permalink($post_id);
}

/**
 * Return the permalink of a queried post in a post loop.
 */

function pitchfork_engnews_loop_excerpt_binding() {

	$post_id = get_the_ID();
    if (!$post_id) {
        return '';
    }

	return get_the_excerpt($post_id);
}


/**
 * Retrieves a limited number of direct paragraph blocks from within the
 * `acf/news-article-grid` block of a given post.
 *
 * @param int $post_id The ID of the post to extract content from.
 * @param int $limit   Optional. Maximum number of paragraph blocks to return. Default 10.
 * @return string[]    Array of rendered HTML paragraph strings.
 */
function pitchfork_engnews_get_post_content_preview( $post_id, $limit = 10 ) {

	$post = get_post( $post_id );

	if ( ! $post ) {
		return array();
	}

	$blocks = parse_blocks( $post->post_content );
	$paragraphs = array();

	foreach ( $blocks as $block ) {
		if ( $block['blockName'] === 'acf/news-article-grid' && ! empty( $block['innerBlocks'] ) ) {
			foreach ( $block['innerBlocks'] as $child_block ) {
				if ( $child_block['blockName'] === 'core/paragraph' ) {
					$paragraphs[] = apply_filters( 'the_content', render_block( $child_block ) );

					if ( count( $paragraphs ) >= $limit ) {
						break 2; // Exit both loops once limit is hit
					}
				}
			}
		}
	}

	return $paragraphs;
}
