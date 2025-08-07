<?php
/**
 * Taxonomy archive page: asu_person
 *
 * - Displays an archive page for an ASU Person, identified by their ASURITE.
 * - Pulls data from Search including current school affiliation, profile photo and bio information.
 * - Builds an archive of content from posts and from external_news
 * - Provides links to any topic archive pages which are cross-tagged with the person.
 */

get_header();

$term = get_queried_object();
$paged = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;

?>
<main class="site-main" id="main">

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

	<section class="article-layout">

		<?php

		/**
		 * Loop through posts, assemble content into two arrays.
		 * Echo each array as a separate format of the post content.
		 */
		$post_args = [
			'post_type' => 'post',
			'posts_per_page' => 15,
			'paged'          => $paged,
			'tax_query' => [
				[
					'taxonomy' => 'topic',
					'field'    => 'slug',
					'terms'    => get_queried_object()->slug,
				],
			],
		];

		$post_query = new WP_Query($post_args);

		$posts_array = $post_query->have_posts() ? $post_query->posts : [];
		$total_posts = count($posts_array);

		$card_indexes   = [];
		$column_indexes = [];
		$card_columns   = 'halves'; // default

		if ($total_posts) {
			if ($total_posts <= 4) {
				// All posts rendered as cards
				$card_indexes = range(0, $total_posts - 1);

				// Determine column layout based on count
				if ($total_posts === 3) {
					$card_columns = 'thirds';
				}
				// $card_columns remains 'halves' for 1, 2, or 4
			} else {
				// First 3 are cards, rest go to column format
				$card_indexes   = [0, 1, 2];
				$column_indexes = range(3, $total_posts - 1);
				$card_columns = 'thirds';
			}
		}

		if (!empty($card_indexes)) {

			echo '<div id="post-cards" class="' . $card_columns . '">';

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
						<h3 class="wp-block-heading card-title"><?php echo get_the_title(); ?></h3>
					</div>
					<div class="wp-block-group is-layout-flow wp-block-group-is-layout-flow card-body">
						<?php the_excerpt(); ?>
					</div>
					<div class="card-link">
						<a href="<?php echo get_the_permalink(); ?>">Read more</a>
					</div>
				</div>
				<?php
			}

			echo '</div>';
		}

		if (!empty($column_indexes)) {

			echo '<div class="story-column">';

			foreach ($column_indexes as $i) {

				$post = $posts_array[$i];
				setup_postdata($post);

				?>
				<div class="story-thumb">
					<figure class="thumb-wrap">
						<img decoding="async"
							class=""
							src="<?php echo esc_url(get_the_post_thumbnail_url(null, 'medium')); ?>"
							alt="<?php echo esc_attr(get_post_meta(get_post_thumbnail_id(), '_wp_attachment_image_alt', true)); ?>"
						/>
					</figure>
					<div class="story-thumb-content">
						<h3 class="post-title"><a href="<?php echo get_the_permalink(); ?>"><?php echo get_the_title(); ?></a></h3>
						<?php the_excerpt(); ?>
					</div>
				</div>

				<?php
			}

			echo '</div>';
		}

		wp_reset_postdata();

		pitchfork_pagination();

		?>

	</section>

	<section id="related-people-wrap" class="alignfull">
	<?php

	$related_people = [];

	// Query args for posts and external_news tagged with this topic
	$query_args = [
		'post_type'      => ['post', 'external_news'],
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'tax_query'      => [
			[
				'taxonomy' => 'topic',
				'field'    => 'term_id',
				'terms'    => $term->term_id,
			],
		],
		'fields' => 'ids', // get only post IDs for performance
	];

	$person_query = new WP_Query( $query_args );

	if ( $person_query->have_posts() ) {
		foreach ( $person_query->posts as $post_id ) {
			// Get all asu_person terms for this post
			$post_people = get_the_terms( $post_id, 'asu_person' );
			if ( $post_people && ! is_wp_error( $post_people ) ) {
				foreach ( $post_people as $person_term ) {
					// Use term_id as key to avoid duplicates
					$related_people[ $person_term->term_id ] = $person_term;
				}
			}
		}
	}

	wp_reset_postdata();

	if ( ! empty( $related_people ) ) {

		$profiles = '';

		// Sort the array by last name. Not 100% accurate but close.

		usort( $related_people, function ( $a, $b ) {
			// Get last word from the name string (rightmost word)
			$last_name_a = substr( strrchr( $a->name, ' ' ), 1 ) ?: $a->name;
			$last_name_b = substr( strrchr( $b->name, ' ' ), 1 ) ?: $b->name;

			return strcasecmp( $last_name_a, $last_name_b );
		});

		// Output sorted list
		echo '<div id="related-people">';
		echo '<h2>Related People</h2>';
		echo '<p class="lead">These individuals are frequently associated with this topic.</p>';
		echo '<div class="related-list">';
		foreach ( $related_people as $person ) {

			$profile_data = get_asu_person_profile( $person );
			$person_link = get_term_link( $person );

			if ( isset( $profile_data['status'] ) && $profile_data['status'] === 'found' ) {

				$profiles .= '<div class="related-person">';
				$profiles .= '<img class="search-image img-fluid" src="' . $profile_data['photo'] . '?blankImage2=1" alt="Portrait of ' . $profile_data['display_name'] . '"/>';
				$profiles .= '<h4 class="display-name"><a href="' . $person_link . '" title="Profile for ' . $profile_data['display_name'] . '">' . $profile_data['display_name'] . '</a></h4>';
				$profiles .= '<p class="title">' . $profile_data['title'] . '</p>';
				$profiles .= '<p class="department">' . $profile_data['department'] . '</p>';
				$profiles .= '</div>';

			} else {

				do_action('qm/debug', $profile_data);

				// Need graceful fallback for a profile that has no data.
				$unk_desc = get_field('asuperson_default_desc', $person);

				$profiles .= '<div class="related-person unknown">';
				$profiles .= '<img class="search-image img-fluid" src="' . get_stylesheet_directory_uri() . '/img/unknown-person.png" alt="Unknown person"/>';
				$profiles .= '<h4 class="display-name"><a href="' . $person_link . '" title="Profile for ' . $person->name . '">' . $person->name . '</a></h4>';
				$profiles .= '<p class="department">' . $unk_desc . '</p>';
				$profiles .= '</div>';
			}

		}
		echo $profiles;
		echo '</div></div>';
	}
	?>
	</section>

</main>

<?php get_footer(); ?>
