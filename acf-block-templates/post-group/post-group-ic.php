<?php
/**
 * Block Registration and Block Template
 *
 * Block name: Post Group
 * Author: Steve Ryan
 * Version: 1.0
 *
 * @package pitchfork-innercircle
 *
 */

$text_origin = get_field('ic_post_group_content_origin');
$image_origin = get_field('ic_post_group_image_origin');
$featured_story = get_field('ic_post_group_content_featured');

if ('arbitrary' === $image_origin) {
    $image = wp_get_attachment_image( get_field('ic_post_group_image_upload'), 'full' );
} else {
    // 'automatic' === $image_origin
    // If this is supposed to be automatic, the logic will need to happen within the loop.
    
    // The logic should be this: 
    // -- If present, return the featured image of the story marked as the "featured story"
    // -- Then look for the featured image of the selected tag, if that is the text_origin.
    // -- Look for any featured image of any selected post, regardless of origin.
    // -- Finally, select a default generic image and display it.
    $image = '';

} 

if (('post_tag' === $text_origin ) || ('category' === $text_origin ))  {

    if ('post_tag' === $text_origin ) {
        $selected = get_field('ic_post_group_content_tag');
    } elseif ('category' === $text_origin ) {
        $selected = get_field('ic_post_group_content_category');
    }

    $offset = get_field('ic_post_group_content_offset');

    $args = array(
        'post_type' => 'post',
        'posts_per_page' => 3,
        'offset' => $offset,
        'post_status' => array( 'publish' ),
        'tax_query' => array(
            array(
                'taxonomy' => $text_origin,
                'terms'    => $selected,
            ),
        ),
    );

    $group = new WP_Query( $args );

    if ( $group->have_posts() ) :
        
        $postcount = 0;

        $storydiv = '';

        while ( $group->have_posts() ) : $group->the_post();

            $postcount++;
            
            if ($postcount == $featured_story) {
                $storydiv .= '<div class="story active">';
                
                // Check for a featured image if there hasn't already been one set. 
                // The "arbitrary" choice would be the only one previously set.
                if ( ( has_post_thumbnail() ) && ( empty($image) ) ) {
                    $image = get_the_post_thumbnail();
                }
            } else {
                $storydiv .= '<div class="story">';
            }

            $storydiv .= '<a href="' . get_the_permalink() . '" title="' . get_the_title() . '">';
            $storydiv .= '<h4>' . get_the_title() . '</h4>';
            // $eventline = innercircle_event_line( get_the_ID(), false );
            // $storydiv .= $eventline;
            $storydiv .= '</a></div>';
            
        endwhile;

        // Final featured image checks. 
        // Grab the featured image from the tag taxonomy if there is one.
        if (empty($image) && ('post_tag' === $text_origin )) {
            $term = get_term($selected, $text_origin);
            $tagimage = get_field('ic_tag_featured_image', $term);
            $image = wp_get_attachment_image( $tagimage, 'full');
        }

        if (empty($image)) {
            $image = '<img class="img-fluid" src="' . get_stylesheet_directory_uri() . '/img/160602-Success-PapagoPark-011-AD.jpg" alt="ASU student in sillouette, sunset over Papago Park" />';
        }

        // Output.

        echo '<div class="ic-post-group">';
        echo $image;
        echo '<div class="story-wrap">';

        echo $storydiv;

        echo '</div>';
        echo '</div><!-- end .ic-post-group -->';

    else :

        echo '<div class="ic-post-group">';
        echo '<img class="img-fluid" src="' . get_stylesheet_directory_uri() . '/img/160602-Success-PapagoPark-011-AD.jpg" alt="ASU student in sillouette, sunset over Papago Park" />';
        echo '<div class="story-wrap">';
        echo '<div class="story"><a href="#">There are no stories selected</a></story>';
        echo '</div>';
        echo '</div><!-- end .ic-post-group -->';

    endif;

} else {
    // $text_origin is equal to "arbirtrary"
    // Returned object from ACF is a WP_Post object already, so no additional query needed.

    $selected = get_field('ic_post_group_content_posts');

    $postcount = 0;
    $storydiv = '';

    if (!empty($selected)) {

        foreach( $selected as $story ): 

            $postcount++;
                
            if ($postcount == $featured_story) {
                $storydiv .= '<div class="story active">';
                
                // Check for a featured image if there hasn't already been one set. 
                // The "arbitrary" choice would be the only one previously set.
                if ( ( has_post_thumbnail($story->ID) ) && ( empty($image) ) ) {
                    $image = get_the_post_thumbnail($story->ID);
                }
            } else {
                $storydiv .= '<div class="story">';
            }

            $storydiv .= '<a href="' . get_the_permalink($story->ID) . '" title="' . get_the_title($story->ID) . '">';
            $storydiv .= '<h4>' . get_the_title($story->ID) . '</h4>';
            $eventline = innercircle_event_line( $story->ID, false );
            $storydiv .= $eventline;
            $storydiv .= '</a></div>';


        endforeach;

        if (empty($image)) {
            $image = '<img class="img-fluid" src="' . get_stylesheet_directory() . '/img/160602-Success-PapagoPark-011-AD.jpg" alt="ASU student in sillouette, sunset over Papago Park" />';
        }

        // Output.
        echo '<div class="ic-post-group">';
        echo $image;
        echo '<div class="story-wrap">';

        echo $storydiv;

        echo '</div>';
        echo '</div><!-- end .ic-post-group -->';
    
    } else {

        echo '<div class="ic-post-group">';
        echo '<img class="img-fluid" src="' . get_stylesheet_directory_uri() . '/img/160602-Success-PapagoPark-011-AD.jpg" alt="ASU student in sillouette, sunset over Papago Park" />';
        echo '<div class="story-wrap">';
        echo '<div class="story"><a href="#">There are no stories selected</a></story>';
        echo '</div>';
        echo '</div><!-- end .ic-post-group -->';
    }
}


