<?php
/**
 * Archive page - default archive for any template not previously defined.
 *
 * - Page 1 layout: 6 cards in 2 rows + 12 additional stories.
 * - Page 2+ layout: 18 additional stories in two columns.
 */

get_header();

$paged = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;
$landmark = 'Term';

// Determine the post type for this archive.
$post_type = get_query_var( 'post_type' );
if ( empty( $post_type ) && is_post_type_archive() ) {
	$qo = get_queried_object();
	if ( $qo && ! empty( $qo->name ) ) {
		$post_type = $qo->name;
	}
}
if ( empty( $post_type ) ) {
	$post_type = 'post'; // sensible default
}

// Base args (layout/pagination stay the same)
$post_args = [
	'post_type'           => $post_type,
	'posts_per_page'      => 18,
	'paged'               => $paged,
	'orderby'             => 'date',
	'order'               => 'DESC',
	'ignore_sticky_posts' => true,
];

// TAXONOMY ARCHIVES (category, tag, custom tax)
if ( is_category() || is_tag() || is_tax() ) {
	$term = get_queried_object(); // WP_Term
	if ( $term && ! is_wp_error( $term ) && ! empty( $term->taxonomy ) ) {
		$post_args['tax_query'] = [
			[
				'taxonomy'         => $term->taxonomy,
				'field'            => 'term_id',          // robust & fast
				'terms'            => (int) $term->term_id,
				'include_children' => true,
			],
		];
	}
}

// TAXONOMY ARCHIVES (category, tag, custom tax)
if ( is_category() || is_tag() || is_tax() ) {
	$term = get_queried_object(); // WP_Term
	if ( $term && ! is_wp_error( $term ) && ! empty( $term->taxonomy ) ) {
		$post_args['tax_query'] = [
			[
				'taxonomy'         => $term->taxonomy,
				'field'            => 'term_id',          // robust & fast
				'terms'            => (int) $term->term_id,
				'include_children' => true,
			],
		];

		// Set $landmark label based on taxonomy term
		$taxonomy_obj   = get_taxonomy( $term->taxonomy );

		if ( $taxonomy_obj ) {
			// Prefer the singular name; fall back to label/name just in case
			$landmark = $taxonomy_obj->labels->singular_name
				?: ( $taxonomy_obj->label ?? $taxonomy_obj->name );
		}

		// Override any specific taxonomy labels here
		$landmark_overrides = [
			// 'program_date'   => 'Program Date',
			// 'faculty_mentor' => 'Faculty Mentor',
			// 'symposium_date' => 'Symposium Date',
		];

		if ( isset( $landmark_overrides[ $term->taxonomy ] ) ) {
			$landmark = $landmark_overrides[ $term->taxonomy ];
		}

		// Final fallback if nothing came through
		if ( ! $landmark ) {
			$landmark = ucwords( str_replace( '_', ' ', $term->taxonomy ) );
		}

	}
}

// AUTHOR ARCHIVES
if ( is_author() ) {
	$post_args['author'] = (int) get_queried_object_id();
	$landmark = 'Author';
}

// DATE ARCHIVES (year/month/day)
if ( is_date() ) {
	$date_bits = [];
	foreach ( [ 'year', 'monthnum', 'day' ] as $k ) {
		$val = get_query_var( $k );
		if ( $val ) $date_bits[ $k ] = (int) $val;
	}
	if ( $date_bits ) {
		$post_args['date_query'] = [ $date_bits ];
	}
	$landmark = 'Dates';
}

// $post_args should now be for whatever archive is being viewed.
$post_query  = new WP_Query( $post_args );
$posts_array = $post_query->have_posts() ? $post_query->posts : [];
$total_posts = count( $posts_array );
$maxpages    = $post_query->max_num_pages ? (int) $post_query->max_num_pages : 1;

?>

<main class="site-main" id="main">
	<section id="landing-info-wrap" class="alignfull">
		<div class="landing-info">
			<span class="landmark"><?php echo esc_html($landmark); ?></span>
			<h1 class="topic-name"><?php echo esc_html( $term->name ); ?></h1>

			<?php if ( $paged > 1 ) : ?>
				<p class="current-page lead">Page <?php echo intval( $paged ); ?> of <?php echo intval( $maxpages ); ?></p>
			<?php else : ?>
				<p class="topic-description lead"><?php echo wp_kses_post( $term->description ); ?></p>
			<?php endif; ?>

		</div>
	</section>

	<?php

	/**
	 * Loop through posts, assemble content into two arrays.
	 * Echo each array as a separate format of the post content.
	 */

	$card_indexes   = [];
	$column_indexes = [];
	$card_columns   = 'thirds'; // 2 rows of 3

	if ( $total_posts ) {
		if ( 1 === $paged ) {
			// First page: up to 6 cards, remainder (up to 12) in columns.
			$card_count    = min( 6, $total_posts );
			$card_indexes  = $card_count ? range( 0, $card_count - 1 ) : [];
			$start_columns = $card_count;

			if ( $total_posts > $start_columns ) {
				$column_indexes = range( $start_columns, $total_posts - 1 );
			}
		} else {
			// Subsequent pages: everything in the two-column list (no cards).
			$column_indexes = range( 0, $total_posts - 1 );
		}
	}

	if (!empty($card_indexes)) {
		echo '<section id="article-grid-wrap" class="alignfull">';
		echo '<div id="article-grid" class="' . $card_columns . '">';

		foreach ($card_indexes as $i) {

			$post = $posts_array[$i];
			setup_postdata($post);

			?>
			<div class="card card-vertical card-story">
				<img decoding="async"
					class="card-img-top"
					src="<?php echo esc_url(get_the_post_thumbnail_url(null, 'medium_large')); ?>"
					alt="<?php echo esc_attr(get_post_meta(get_post_thumbnail_id(), '_wp_attachment_image_alt', true)); ?>"
				/>
				<div class="card-header">
					<h3 class="wp-block-heading card-title">
						<a href="<?php echo get_the_permalink($post); ?>">
							<?php echo get_the_title($post); ?>
						</a>
					</h3>
				</div>

				<div class="card-body">
					<?php echo wp_kses_post( get_the_excerpt( $post ) ); ?>
				</div>

				<?php
					$badges = '';
					$badgeterms = get_the_terms( $post, 'school_unit' );
					if ($badgeterms) {
						$badges = '<div class="card-tags"><span class="visually-hidden">School or unit</span>';
						foreach ($badgeterms as $badgeterm) {
							$term_link = get_term_link( $badgeterm );
							$badges .= '<span class="badge badge-rectangle">' . $badgeterm->name . '</span>';
						}
						$badges .= '</div>';
					}

					// echo $badges;
				?>
			</div>
			<?php
		}

		echo '</div></section>';
	}

	if (!empty($column_indexes)) {

		echo '<section id="story-column">';

		foreach ($column_indexes as $i) {

			$post = $posts_array[$i];
			setup_postdata($post);

			?>
			<div class="story-thumb">
				<figure class="thumb-wrap">
					<img decoding="async"
						class=""
						src="<?php echo esc_url(get_the_post_thumbnail_url($post, null, 'medium')); ?>"
						alt="<?php echo esc_attr(get_post_meta(get_post_thumbnail_id($post), '_wp_attachment_image_alt', true)); ?>"
					/>
				</figure>
				<div class="story-thumb-content">
					<h3 class="post-title">
						<a href="<?php echo get_the_permalink($post); ?>">
							<?php echo get_the_title($post); ?>
						</a>
					</h3>

					<?php echo wp_kses_post( get_the_excerpt( $post ) ); ?>

					<?php
						$badges = '';
						$badgeterms = get_the_terms( $post, 'school_unit' );
						if ($badgeterms) {
							$badges = '<div class="badge-row"><span class="visually-hidden">School or unit</span>';
							foreach ($badgeterms as $badgeterm) {
								$term_link = get_term_link( $badgeterm );
								$badges .= '<span class="badge badge-rectangle">' . $badgeterm->name . '</span>';
							}
							$badges .= '</div>';
						}

						echo $badges;
					?>
				</div>
			</div>

			<?php
		}

		echo '</section>';
	}

	wp_reset_postdata();

	echo '<section id="pagination-wrapper">';
	pitchfork_pagination();
	// Date range for posts shown on this page.
	pf_the_date_range_for_posts(
		$posts_array,
		[
			'class'  => 'date-range lead',
			'prefix' => 'Date range', // or 'Date range' for screen readers
			'gmt'    => false, // true for GMT
			'icon_html'     => '<i class="fa-duotone fa-solid fa-calendar-range fa-lg" style="--fa-primary-color: #8c1d40; --fa-secondary-color: #8c1d40;"></i>',
			'icon_position' => 'before',
		]
	);
	echo '</section>'
	?>

</main>

<?php get_footer(); ?>
