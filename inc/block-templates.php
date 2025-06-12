<?php
/**
 * Block templates for any post type that needs them
 *
 *  - POST: Contains the default news blocks making the pattern for a standard post.
 *
 * @package pitchfork-engnews
 */

add_action( 'init', 'pitchfork_engnews_register_block_template_post' );
function pitchfork_engnews_register_block_template_post() {
    $post_type_object = get_post_type_object( 'post' );

    if ( $post_type_object ) {
        $post_type_object->template = array(
            array(
                'acf/news-header',
                array(
                    'name' => 'acf/news-header',
                    'data' => array(),
                    'mode' => 'preview',
                    'lock' => array(
                        'move' => true,
                        'remove' => true
                    )
                ),
                array()
            ),
            array(
                'acf/news-featured-image',
                array(
                    'name' => 'acf/news-featured-image',
                    'data' => array(),
                    'mode' => 'preview',
                    'lock' => array(
                        'move' => true,
                        'remove' => true
                    )
                ),
                array(
                    array(
                        'core/paragraph',
                        array(
                            'metadata' => array(
                                'bindings' => array(
                                    'content' => array(
                                        'source' => 'engnews/featured-image-caption'
                                    )
                                ),
                                'name' => 'Image Caption (Bound)'
                            )
                        ),
                        array()
                    ),
                )
            ),
            array(
                'acf/news-article-grid',
                array(
                    'name' => 'acf/news-article-grid',
                    'data' => array(),
                    'mode' => 'preview',
                    'lock' => array(
                        'move' => true,
                        'remove' => false
                    )
                ),
                array(
                    array(
                        'core/paragraph',
                        array(),
                        array()
                    ),
                    array(
                        'core/group',
                        array(
                            'tagName' => 'aside',
                            'lock' => array(
                                'move' => true,
                                'remove' => false
                            ),
                            'metadata' => array(
                                'name' => 'News Aside (Group)'
                            ),
                            'className' => 'is-style-news-aside',
                            'style' => array(
                                'spacing' => array(
                                    'padding' => array(
                                        'top' => 'var:preset|spacing|uds-size-4',
                                        'bottom' => 'var:preset|spacing|uds-size-4',
                                        'left' => 'var:preset|spacing|uds-size-4',
                                        'right' => 'var:preset|spacing|uds-size-4'
                                    )
                                )
                            ),
                            'backgroundColor' => 'gray-2',
                            'layout' => array(
                                'type' => 'constrained'
                            )
                        ),
                        array(
                            array(
                                'core/heading',
                                array(
                                    'level' => 3,
									'content' => 'Related people',
                                ),
                                array()
                            ),
                            array(
                                'acf/news-related-people',
                                array(
                                    'name' => 'acf/news-related-people',
                                    'mode' => 'preview'
                                ),
                                array()
                            ),
                            array(
                                'core/heading',
                                array(
                                    'level' => 3,
									'content' => 'Featured topics',
                                ),
                                array()
                            ),
                            array(
                                'acf/news-related-terms',
                                array(
                                    'name' => 'acf/news-related-terms',
                                    'data' => array(
                                        'news_terms_taxonomy' => 'topic',
                                        '_news_terms_taxonomy' => 'field_6836239340c8e',
                                        'news_terms_style' => 'list',
                                        '_news_terms_style' => 'field_683626a66f2d0'
                                    ),
                                    'mode' => 'preview'
                                ),
                                array()
                            ),
                        )
                    ),
                )
            ),
            array(
                'acf/news-authorbox',
                array(
                    'name' => 'acf/news-authorbox',
                    'data' => array(
                        'engnews_authorbox_use_default' => '1',
                        '_engnews_authorbox_use_default' => 'field_67db5311112cd'
                    ),
                    'mode' => 'preview',
                    'lock' => array(
                        'move' => true,
                        'remove' => true
                    )
                ),
                array()
            )
        );

        // Optional: prevent user from removing or rearranging blocks.
        $post_type_object->template_lock = false;
    }
}
