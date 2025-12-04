<?php
/**
 * Pitchfork Blocks - Filterable external media
 *
 * - Interactive block which present external media stories.
 *
 * @package pitchfork_engnews
 */

/**
 * Set initial get_field declarations.
 */

/**
 * Set block classes
 * - Get additional classes from the 'advanced' field in the editor.
 * - Get alignment setting from toolbar if enabled in theme.json, or set default value
 * - Include any default classs for the block in the intial array.
 */

$block_attr = array( 'wp-block-external-news');
if ( ! empty( $block['className'] ) ) {
	$block_attr[] = $block['className'];
}
/**
 * Additional margin/padding settings
 * Returns a string for inclusion with style=""
 */
$spacing = pitchfork_blocks_acf_calculate_spacing( $block );

/**
 * Include block.json support for HTML anchor.
 */
$anchor = '';
if ( ! empty( $block['anchor'] ) ) {
	$anchor = 'id="' . $block['anchor'] . '"';
}

/**
 * Create the outer wrapper for the block output.
 */
$attr  = implode( ' ', $block_attr );
$blockwrap = '<div ' . $anchor . ' class="' . $attr . '" style="' . $spacing . '">';
$output = '';

/**
 * Query loop to /external media elements.
 * Returns latest 300 posts in the query
 * Isotope will limit the display to only a handful of those posts at a time.
 */

$external_args = array(
    'post_type'      => 'external_news',
    'post_status'    => 'publish',
    'posts_per_page' => 300,
    'orderby'        => 'date',
    'order'          => 'DESC',
);

// Collections for filter selects
$pub_index = array();
$topic_index = array();
$person_index = array();
$month_index  = array();

// Number of "latest posts" to initially display.
$latest_count = 10;
$loop_index    = 0;

// Get the party started
$the_query = new WP_Query( $external_args );

if ( $the_query->have_posts() ) {

    while ( $the_query->have_posts() ) {
        $the_query->the_post();
        $post_id     = get_the_ID();

		/**
		 * Assemble classes needed for each .news-post item.
		 * Increment counter to track first {##} of posts for date filter.
		 */
		$loop_index++;
		$post_class_list = array( 'news-post', 'post-' . intval( $post_id ) );

		if ( $loop_index <= $latest_count ) {
			$post_class_list[] = 'latest';
		}

		/**
		 * Title and external link
		 */
        $title = get_the_title();
		$link = get_field('_itn_mainurl', $post_id);

		if ($link) {
			$headline = '<h2 class="headline"><a href="' . esc_url($link) . '" target="_blank">' . $title ;
			$headline .= '<span class="fa-regular fa-arrow-up-right-from-square fa-xs"></span></a></h2>';
		} else {
			$headline = $title;
		}

		/**
		 * The date
		 */
		$posted_on = '<time datetime="' . get_the_date('Y-m-d\TH:i:sP', $post_id) . '">' . get_the_date('F j, Y', $post_id) . '</time>';

		/**
		 * The content, filters from the normal content call applied here.
		 */
		$content = '<div class="content">';
		$content .= apply_filters( 'the_content', get_the_content($post_id) );
		$content .= '</div>';

		/**
         * The publication - taxonomy terms
		 * - Builds display element
		 * - Collects the correct slug/labels in an array for later.
         */
        $pubterms = get_the_terms( $post_id, 'publication' );

        // Build display element for the post (unchanged)
        $publications = '';
        if ( $pubterms && ! is_wp_error( $pubterms ) ) {
            $pubnames = wp_list_pluck( $pubterms, 'name' );
            $publications .= '<div class="publications">';
            $publications .= '<span class="badge badge-rectangle publication">' . esc_html( join( ', ', $pubnames ) ) . '</span>';
            $publications .= '</div>';

            // Collect for the select: use the term slug as the value (safer) and name as label
            foreach ( $pubterms as $pubterm ) {
                if ( empty( $pubterm->slug ) ) {
                    continue;
                }

				$slug = sanitize_html_class( $pubterm->slug );
        		$post_class_list[] = 'publication-' . $slug;

                // keep the first-seen label for a slug
                if ( ! isset( $pub_index[ $pubterm->slug ] ) ) {
                    $pub_index[ $pubterm->slug ] = $pubterm->name;
                }
            }
        }

		/**
		 * The topic - taxonomy terms
		 * - Same strategy as above. Build output, collect slugs/names in array.
		 */

		$topic_terms = get_the_terms($post_id, 'topic');

		$topics = '';
		if ( $topic_terms && ! is_wp_error( $topic_terms ) ) {
			$topic_names = wp_list_pluck($topic_terms, 'name');

			$topics = '<div class="topics">';
			$topics .= '<span class="badge badge-rectangle topic">' . esc_html( join( ', ', $topic_names ) ) . '</span>';
			$topics .= '</div>';

			// Collect for select
			foreach ( $topic_terms as $t ) {

				$slug = sanitize_html_class( $t->slug );
				$post_class_list[] = 'topic-' . $slug;

                if ( ! isset( $topic_index[ $t->slug ] ) ) {
                    $topic_index[ $t->slug ] = $t->name;
                }
            }
		}

		/**
		 * Related people
		 * Logic taken from news-related-people.php
		 * TODO - Needs wrapper
		 */

		$profile_terms = get_the_terms($post_id, 'asu_person');
		$profiles = '';

		if ( $profile_terms && ! is_wp_error( $profile_terms ) ) {
			foreach ( $profile_terms as $term ) {

				// Collect array elements for select box.
				if ( empty( $term->slug ) ) { continue; }
				$slug = sanitize_html_class( $term->slug );
				$post_class_list[] = 'person-' . $slug;           // per-post class used by Isotope filter
				if ( ! isset( $person_index[ $slug ] ) ) {
					$person_index[ $slug ] = $term->name; // store display name for the select
				}

				// Build out the related person tile
				$profile_data = get_asu_person_profile( $term );
				$term_link = get_term_link( $term);

				if ($profile_data['status'] == 'found') {

					$profiles .= '<div class="related-person">';
					$profiles .= '<img class="search-image img-fluid" src="' . $profile_data['photo'] . '?blankImage2=1" alt="Portrait of ' . $profile_data['display_name'] . '"/>';
					$profiles .= '<h4 class="display-name"><a href="' . $term_link . '" title="Profile for ' . $profile_data['display_name'] . '">' . $profile_data['display_name'] . '</a></h4>';
					$profiles .= '<p class="title">' . $profile_data['title'] . '</p>';
					$profiles .= '<p class="department">' . $profile_data['department'] . '</p>';
					$profiles .= '</div>';

				}
			}
		}

		// Month: use year-month (YYYY-MM) as the key, label as "Month Year"
		$month_key = get_the_date( 'Y-m', $post_id );   // e.g. "2025-12"
		$month_label = get_the_date( 'F Y', $post_id ); // e.g. "December 2025"

		if ( ! empty( $month_key ) ) {
			$month_slug = sanitize_html_class( $month_key );              // safe for class names
			$post_class_list[] = 'month-' . $month_slug;                  // add per-post month class

			// Keep first-seen label (should be same for same month_key)
			if ( ! isset( $month_index[ $month_slug ] ) ) {
				$month_index[ $month_slug ] = $month_label;
			}
		}

		/**
		 * Logic for featured image or OG captured image URL
		 * See external-image.php for near duplicate code.
		 * $thumb = the resulting image for the display.
		 */
		$image_url = get_post_meta($post_id, '_itn_og_image_url', true);
		$image_alt = get_post_meta($post_id, '_itn_og_image_alt', true);
		$thumb = '';

		$emptyimg = '<div class="card-img-top components-placeholder block-editor-media-placeholder is-medium has-illustration">';
		$emptyimg .= '<svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60 60" preserveAspectRatio="none" class="components-placeholder__illustration" aria-hidden="true" focusable="false"><path vector-effect="non-scaling-stroke" d="M60 60 0 0"></path></svg>';
		$emptyimg .= '</div>';

		if ($image_url) {
			$thumb = '<img src="' . $image_url . '" alt="' . $image_alt . '" class="img-fluid social" loading="lazy" decoding="async"/>';
		}

		if ( has_post_thumbnail( $post_id ) ) {
			$thumb_id  = get_post_thumbnail_id( $post_id );
			$thumb_url = wp_get_attachment_image_url( $thumb_id, 'full' );
			$thumb_alt = get_post_meta( $thumb_id, '_wp_attachment_image_alt', true );

			$thumb = '<img src="' . esc_url( $thumb_url ) . '" alt="' . esc_attr( $thumb_alt ) . '" class="img-fluid featured-img" loading="lazy" decoding="async">';
		}

		if ((empty($thumb)) && (is_preview())) {
			$thumb = $emptyimg;
		}

		// Remove duplicates and build the final class attribute
		$post_class_list = array_unique( $post_class_list );
		$class_attr = implode( ' ', array_map( 'sanitize_html_class', $post_class_list ) );

		/**
		 * Format all the things for a post
		 */
		$output .= '<div class="' . esc_attr( $class_attr ) . '">';
		$output .= $publications . $headline;
		$output .= '<div class="content-wrap">' . $posted_on . $content . '</div>';
		$output .= '<div class="content-tags">' . $thumb . $topics . $profiles . '</div>';
		$output .= '</div>';

	// End while, end loop
	}
}

/**
 * Build select fields for filtering
 * - Pub filter group
 * - Topic filter group
 */

$pub_filtergroup = '';
if ( ! empty( $pub_index ) ) {
    // sort alphabetically by label
    asort( $pub_index, SORT_LOCALE_STRING );

    $pub_filtergroup .= '<form id="publication" class="uds-form" role="search" aria-label="Filter by publication">';
    $pub_filtergroup .= '<div class="form-group">';
    $pub_filtergroup .= '<label for="filter-publication">Publication</label>';
    $pub_filtergroup .= '<select id="filter-publication" class="filter form-select" title="Select a publication">';
    $pub_filtergroup .= '<option value="" selected> -- select a publication -- </option>';

    foreach ( $pub_index as $slug => $label ) {
        $val = '.publication-' . sanitize_html_class( $slug );
        $pub_filtergroup .= '<option value="' . esc_attr( $val ) . '">' . esc_html( $label ) . '</option>';
    }

    $pub_filtergroup .= '</select></div></form>';
}

$topic_filtergroup = '';
if ( ! empty( $topic_index ) ) {
    // sort alphabetically by label
    asort( $topic_index, SORT_LOCALE_STRING );

    $topic_filtergroup .= '<form id="topic" class="uds-form" role="search" aria-label="Filter by topic">';
    $topic_filtergroup .= '<div class="form-group">';
    $topic_filtergroup .= '<label for="filter-topic">Topic</label>';
    $topic_filtergroup .= '<select id="filter-topic" class="filter form-select" title="Select a topic">';
    $topic_filtergroup .= '<option value="" selected> -- select a topic -- </option>';

    foreach ( $topic_index as $slug => $label ) {
        $val = '.topic-' . sanitize_html_class( $slug );
        $topic_filtergroup .= '<option value="' . esc_attr( $val ) . '">' . esc_html( $label ) . '</option>';
    }

    $topic_filtergroup .= '</select></div></form>';
}

$person_filtergroup = '';
if ( ! empty( $person_index ) ) {
    asort( $person_index, SORT_LOCALE_STRING );

    $person_filtergroup .= '<form id="person" class="uds-form" role="search" aria-label="Filter by person">';
    $person_filtergroup .= '<div class="form-group">';
    $person_filtergroup .= '<label for="filter-person">Person</label>';
    $person_filtergroup .= '<select id="filter-person" class="filter form-select" title="Select a person">';
    $person_filtergroup .= '<option value="" selected> -- select a person -- </option>';

    foreach ( $person_index as $slug => $label ) {
        $val = '.person-' . sanitize_html_class( $slug );
        $person_filtergroup .= '<option value="' . esc_attr( $val ) . '">' . esc_html( $label ) . '</option>';
    }

    $person_filtergroup .= '</select></div></form>';
}

$month_filtergroup = '';
if ( ! empty( $month_index ) ) {
    // Sort keys (month slugs) descending so newest months appear first
    krsort( $month_index );

    $month_filtergroup .= '<form id="month" class="uds-form" role="search" aria-label="Filter by month">';
    $month_filtergroup .= '<div class="form-group">';
    $month_filtergroup .= '<label for="filter-month">Month</label>';
    $month_filtergroup .= '<select id="filter-month" class="filter form-select" title="Select a month">';
	$month_filtergroup .= '<option value=".latest" selected> Display latest (' . intval( $latest_count ) . ' newest) </option>';

    foreach ( $month_index as $slug => $label ) {
        $val = '.month-' . esc_attr( $slug ); // e.g. ".month-2025-12"
        $month_filtergroup .= '<option value="' . esc_attr( $val ) . '">' . esc_html( $label ) . '</option>';
    }

    $month_filtergroup .= '</select></div></form>';
}


/**
 * Echo the output.
 */
echo $blockwrap;
echo '<div class="col-filters">' . $pub_filtergroup . $topic_filtergroup . $person_filtergroup . $month_filtergroup . '</div>';
echo '<div class="news-feed">' . $output . '</div>';
echo '</div>';
