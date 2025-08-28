<?php
/**
 * Taxonomy archive page: category
 *
 * - Default archive page layout: 3 card row + 12 additional stories.
 */

get_header();

$term = get_queried_object();
$paged = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;

?>
<main class="site-main" id="main">
	<section class="landing-info-wrap alignfull">
		<div class="landing-info">

			<?php
			if ( $paged > 1 ) {
				echo '<h1 class="topic-name">' . esc_html($term->name) . '</h1>';
				echo '<h3 class="landmark"><span class="highlight-black">Page ' . $paged . '</span></h3>';
			} else {
				echo '<h3 class="landmark"><span class="highlight-black">Topic</span></h3>';
				echo '<h1 class="topic-name">' . esc_html($term->name) . '</h1>';
				echo '<p class="topic-description">' . $term->description . '</p>';
			}
			?>
		</div>
	</section>

	<?php

	/**
	 * Loop through posts, assemble content into two arrays.
	 * Echo each array as a separate format of the post content.
	 */

	$post_args = [
		'post_type'      => 'post',
		'posts_per_page' => 18,
		'paged'          => $paged,
		'tax_query'      => [
			[
				'taxonomy' => 'category',
				'field'    => 'slug',
				'terms'    => $term->slug,
			],
		],
	];

	$post_query  = new WP_Query( $post_args );
	$posts_array = $post_query->have_posts() ? $post_query->posts : [];
	$total_posts = count( $posts_array );

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
		echo '<section id="article-grid" class="' . $card_columns . '">';

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
					$terms = get_the_terms( $post, 'school_unit' );
					if ($terms) {
						$badges = '<div class="card-tags"><span class="visually-hidden">School or unit</span>';
						foreach ($terms as $term) {
							$term_link = get_term_link( $term );
							$badges .= '<span class="badge text-bg-gray-2">' . $term->name . '</span>';
						}
						$badges .= '</div>';
					}

					echo $badges;
				?>
			</div>
			<?php
		}

		echo '</section></section>';
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
						$terms = get_the_terms( $post, 'school_unit' );
						if ($terms) {
							$badges = '<div class="badge-row"><span class="visually-hidden">School or unit</span>';
							foreach ($terms as $term) {
								$term_link = get_term_link( $term );
								$badges .= '<span class="badge text-bg-gray-2">' . $term->name . '</span>';
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

	pitchfork_pagination();

	?>

</main>

<?php get_footer(); ?>
