<?php
/**
 * Taxonomy archive page: taxonomy_topic
 *
 * - Uses default archive page layout.
 * - Adds profile photo array at the bottom of page 1 for related people for the topic.
 */

get_header();

$term  = get_queried_object();
$paged = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;

$post_args = [
	'post_type'           => 'post',
	'posts_per_page'      => 18,
	'paged'               => $paged,
	'orderby'             => 'date',
	'order'               => 'DESC',
	'ignore_sticky_posts' => true,
	'tax_query'           => [
		[
			'taxonomy' => 'topic',
			'field'    => 'slug',
			'terms'    => $term->slug,
		],
	],
];

$post_query  = new WP_Query( $post_args );
$posts_array = $post_query->have_posts() ? $post_query->posts : [];
$total_posts = count( $posts_array );
$maxpages    = $post_query->max_num_pages ? (int) $post_query->max_num_pages : 1;

?>

<main class="site-main" id="main">
	<section id="landing-info-wrap" class="alignfull">
		<div class="landing-info">
			<span class="landmark">Category</span>
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
							$badges .= '<span class="badge text-bg-gray-2">' . $badgeterm->name . '</span>';
						}
						$badges .= '</div>';
					}

					echo $badges;
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
								$badges .= '<span class="badge text-bg-gray-2">' . $badgeterm->name . '</span>';
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

	do_action('qm/debug', $term);

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
	echo '</section>';

	/**
	 * Related people section (grouped by each person's school_unit from term meta)
	 * Shows only on page 1.
	 */
	if ( 1 === (int) $paged ) :

		$current_tax = isset( $term->taxonomy ) ? $term->taxonomy : 'topic';

		// 1) Get ALL post IDs for this topic (IDs only; no paging)
		$person_posts_args = [
			'post_type'      => [ 'post', 'external_news' ], // adjust if needed
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'tax_query'      => [
				[
					'taxonomy' => $current_tax,
					'field'    => 'slug',
					'terms'    => $term->slug,
				],
			],
		];

		$post_id_query = new WP_Query( $person_posts_args );
		$post_ids      = $post_id_query->posts; // already IDs due to 'fields' => 'ids'
		wp_reset_postdata();

		// 2) Collect all unique asu_person terms attached to those posts in one call
		$people_terms = [];
		if ( ! empty( $post_ids ) ) {
			$people_terms = wp_get_object_terms(
				$post_ids,
				'asu_person',
				[
					'fields'     => 'all',
					'hide_empty' => false, // people may no longer be tagged on newer posts; your call
				]
			);
		}

		if ( ! is_wp_error( $people_terms ) && ! empty( $people_terms ) ) {

			/**
			 * Build buckets keyed by school identifier from the PERSON'S meta.
			 * Bucket shape: [ $bucket_key => [ 'label' => string, 'url' => string, 'people' => WP_Term[] ] ]
			 */
			$school_buckets = [];

			foreach ( $people_terms as $person_term ) {
				// Fetch cached profile you store in term meta
				// $profile = get_asu_person_profile( $person_term );

				// TEMP: Invalidate cache for profile data for testing
				$profile = get_asu_person_profile( $person_term, true );

				// Derive bucket key/label/url from the profile's school_unit meta
				$school_id   = (int) ( $profile['school_unit_term_id'] ?? 0 );
				$school_name = (string) ( $profile['school_unit_term_name'] ?? '' );
				$school_url  = (string) ( $profile['school_unit']['url'] ?? '' );

				// Fallback bucket for missing/unknown school
				if ( $school_id <= 0 || $school_name === '' ) {
					$bucket_key = 'unknown';
					$bucket_label = 'Other / Unspecified';
					$bucket_url = '';
				} else {
					$bucket_key   = 'school_' . $school_id;
					$bucket_label = $school_name;
					$bucket_url   = $school_url;
				}

				// Init bucket
				if ( ! isset( $school_buckets[ $bucket_key ] ) ) {
					$school_buckets[ $bucket_key ] = [
						'label'  => $bucket_label,
						'url'    => $bucket_url,
						'people' => [],
					];
				}

				// De-dupe by term_id within the bucket
				$school_buckets[ $bucket_key ]['people'][ $person_term->term_id ] = $person_term;
			}

			// 3) Output
			if ( ! empty( $school_buckets ) ) {

				// Sort buckets by school label (A→Z), with 'Other / Unspecified' last
				uksort( $school_buckets, function( $a, $b ) use ( $school_buckets ) {
					if ( 'unknown' === $a ) return 1;
					if ( 'unknown' === $b ) return -1;
					return strcasecmp( $school_buckets[ $a ]['label'], $school_buckets[ $b ]['label'] );
				} );

				echo '<section id="related-people-wrap" class="alignfull">';
				echo '<div id="related-people">';
				echo '<h2>Related People by School</h2>';
				echo '<p class="lead">People mentioned in stories for this topic, grouped by their home school or unit.</p>';

				foreach ( $school_buckets as $bucket_key => $bucket ) {
					$label  = $bucket['label'];
					$url    = $bucket['url'];
					$people = $bucket['people'];

					if ( empty( $people ) ) {
						continue;
					}

					// Sort people by last name (simple rightmost-word heuristic)
					uasort( $people, function( $a, $b ) {
						$la = substr( strrchr( $a->name, ' ' ), 1 ) ?: $a->name;
						$lb = substr( strrchr( $b->name, ' ' ), 1 ) ?: $b->name;
						return strcasecmp( $la, $lb );
					} );

					echo '<div class="related-school">';

					// School heading with optional link
					if ( ! empty( $url ) ) {
						echo '<h3 class="school-name"><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></h3>';
					} else {
						echo '<h3 class="school-name">' . esc_html( $label ) . '</h3>';
					}

					// Simple bullets for now
					echo '<ul class="people-list">';
					foreach ( $people as $person_term ) {
						$person_link = get_term_link( $person_term );
						if ( is_wp_error( $person_link ) ) {
							$person_link = '';
						}
						$name = $person_term->name;
						if ( $person_link ) {
							echo '<li><a href="' . esc_url( $person_link ) . '">' . esc_html( $name ) . '</a></li>';
						} else {
							echo '<li>' . esc_html( $name ) . '</li>';
						}
					}
					echo '</ul>';

					echo '</div>'; // .related-school
				}

				echo '</div></section>';
			}
		}
	endif;
	?>

</main>

<?php get_footer(); ?>
