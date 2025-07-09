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

	// if ( ! empty( $block_context['postId'] ) ) {
	// 	return get_permalink( $block_context['postId'] );
	// }

	return get_permalink($post_id);
}
