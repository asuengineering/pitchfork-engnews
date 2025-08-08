<?php
/**
 * Pitchfork Blocks - Block template
 *
 * - Add a description here
 *
 * @package Pitchfork_Blocks
 */

/**
 * Set initial get_field declarations.
 */

$columns = get_field('outgradlist_display_columns') ?? '';
$academic_year = get_field('outgradlist_academic_year') ?? '';
$award_type = get_field('outgradlist_award_type') ?? '';

// Set default academic year if not provided or the control is unset.
if (empty($academic_year)) {
	do_action('qm/debug', 'Doing some default stuff with the AY.');
    $academic_year_terms = get_terms([
        'taxonomy' => 'academic_year',
        'orderby' => 'slug', // or 'term_id' if numeric order is used
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

$block_attr = array( 'outstanding-grads', $columns );
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
    'post_type'      => 'outstand_grad',
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

// If award type is set, add to tax_query
if (!empty($award_type)) {
    $args['tax_query'][] = [
        'taxonomy' => 'graduate_type',
        'field'    => 'term_id',
        'terms'    => $award_type,
    ];
}

// Run the query
do_action('qm/debug', $args);
$outgrads = new WP_Query($args);

$grads = '';
$posts = [];

if ($outgrads->have_posts()) {
    while ($outgrads->have_posts()) {
        $outgrads->the_post();

        $posts[] = [
            'ID'     => get_the_ID(),
            'title'  => get_the_title(),
            'link'   => get_the_permalink(),
            'thumb'  => get_the_post_thumbnail(get_the_ID(), 'medium', ['class' => 'img-fluid']),
            'excerpt'=> get_the_excerpt(),
			'degree' => get_field('_outgrad_program_study', get_the_ID()),
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
        $grads .= '<div class="graduate">';
        $grads .= $post['thumb'];
        $grads .= '<div class="graduate-info">';
        $grads .= '<h3><a href="' . esc_url($post['link']) . '">' . esc_html($post['title']) . '</a></h3>';
        $grads .= '<p class="graduate-program"><span class="fa-duotone fa-light fa-graduation-cap" style="--fa-primary-color: #8c1d40; --fa-secondary-color: #ffc627; --fa-secondary-opacity: 1;"></span>';
		$grads .= esc_html($post['degree']) . '</p>';
        $grads .= '<p class="graduate-excerpt">' . esc_html($post['excerpt']) . '</p>';
        $grads .= '</div></div>';
    }

} else {
    $grads .= '<p>No outstanding graduates found for the selected criteria.</p>';
}



/**
 * Create the outer wrapper and echo the block output.
 */
$attr  = implode( ' ', $block_attr );
$output = '<div ' . $anchor . ' class="' . $attr . '" style="' . $spacing . '">';
$output .= $grads;
$output .= '</div>';
echo $output;
