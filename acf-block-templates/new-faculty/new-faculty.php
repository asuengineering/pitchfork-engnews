<?php
/**
 * Pitchfork Blocks - New Faculty
 *
 * - Queries and lists new faculty members (CPT faculty) by year and school.
 * - Displays the results in profile-card form or by using the social media image.
 *
 * @package pitchfork_engnews
 */

/**
 * Set initial get_field declarations.
 */

$columns = get_field('newfacultylist_display_columns') ?? '';
$academic_year = get_field('newfacultylist_academic_year') ?? '';
$school_unit = get_field('newfacultylist_school_unit') ?? '';
$displaytype = get_field('newfacultylist_display_type') ?? false;

// Set default academic year if not provided or the control is unset.
if (empty($academic_year)) {

    $academic_year_terms = get_terms([
        'taxonomy' => 'academic_year',
        'orderby' => 'slug',
        'order'   => 'DESC',
        'hide_empty' => true,
        'number' => 1,
    ]);

    if (!empty($academic_year_terms) && !is_wp_error($academic_year_terms)) {
        $academic_year = $academic_year_terms[0]->slug;
    }
}

/**
 * Set block classes
 * - Get additional classes from the 'advanced' field in the editor.
 * - Get alignment setting from toolbar if enabled in theme.json, or set default value
 * - Include any default classs for the block in the intial array.
 */

$block_attr = array( 'newfaculty-list', $columns );
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
 * Build a query loop based on the taxonomy terms input by the user.
 */

$args = [
    'post_type'      => 'faculty',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'tax_query'      => [
        [
            'taxonomy' => 'academic_year',
            'field'    => 'term_id',
            'terms'    => $academic_year,
        ]
    ]
];

// If school_unit is set, add to tax_query
if (!empty($school_unit)) {
    $args['tax_query'][] = [
        'taxonomy' => 'school_unit',
        'field'    => 'term_id',
        'terms'    => $school_unit,
    ];
}

// Run the query
$newfaculty = new WP_Query($args);

$grads = '';
$posts = [];

if ($newfaculty->have_posts()) {
    while ($newfaculty->have_posts()) {
        $newfaculty->the_post();

        $posts[] = [
            'ID'     => get_the_ID(),
            'title'  => get_the_title(),
            'link'   => get_the_permalink(),
            'thumb'  => get_the_post_thumbnail(get_the_ID(), 'medium', ['class' => 'img-fluid']),
            'excerpt'=> get_the_excerpt(),
			'work_title' => get_field('_faculty_title', get_the_ID()),
			'expertise' => get_field('_faculty_expertise', get_the_ID()),
			'socialimg' => get_field('_faculty_social_image', get_the_ID()) ?? '',
        ];
    }
    wp_reset_postdata();

    // Sort by last word in title (assumed to be last name)
    usort($posts, function ($a, $b) {
        $lastA = strtolower(array_slice(preg_split('/\s+/', $a['title']), -1)[0]);
        $lastB = strtolower(array_slice(preg_split('/\s+/', $b['title']), -1)[0]);
        return strcmp($lastA, $lastB);
    });

    foreach ($posts as $post) {

		// Checking to see if there is a social image and if we just want that instead.

		if ( ($displaytype) && (!empty($post['socialimg'])) ) {

			$grads .= '<div class="newfaculty-social">';
			$grads .= '<a href="' . esc_url($post['link']) . '">';
			$grads .= wp_get_attachment_image( $post['socialimg'], 'large', false, array('class' => 'img-fluid' ));
			$grads .= '</a></div>';

		} else {

			$grads .= '<div class="newfaculty">';
			$grads .= $post['thumb'];
			$grads .= '<div class="faculty-info">';
			$grads .= '<h3><a href="' . esc_url($post['link']) . '">' . esc_html($post['title']) . '</a></h3>';
			$grads .= '<h4 class="working-title">' . esc_html($post['work_title']) . '</h4>';
			$grads .= '<p class="expertise"><i class="fa-duotone fa-regular fa-book-sparkles" style="--fa-primary-color: #8c1d40; --fa-secondary-color: #ffc627; --fa-secondary-opacity: 1;"></i>';
			$grads .= esc_html($post['expertise']) . '</p>';
			// $grads .= '<p class="faculty-excerpt">' . esc_html($post['excerpt']) . '</p>';
			$grads .= '</div></div>';

		}

    }

} else {
    $grads .= '<p>No new faculty found for the selected criteria.</p>';
}



/**
 * Create the outer wrapper and echo the block output.
 */
$attr  = implode( ' ', $block_attr );
$output = '<div ' . $anchor . ' class="' . $attr . '" style="' . $spacing . '">';
$output .= $grads;
$output .= '</div>';
echo $output;
