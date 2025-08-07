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

/**
 * Get ASURITE ID, pass to search function.
 * Returned results = API call containing all data.
 */
$term = get_queried_object();
$demos = get_asu_person_profile( $term );

?>
<main class="site-main" id="main">

	<section class="article-layout">

		<div id="profile-wrapper">
			<div id="profile-details">

				<!-- <h3 class="landmark"><span class="highlight-black">Faculty or Staff</span></h3> -->

				<?php

				$portrait = '';

				// Check if Search has an image for us and if it is available for display.
				if (! empty( $demos['photo'])) {
					$portrait .= '<img class="search-img img-fluid" ';
					$portrait .= 'src="' . $demos['photo'] . '?blankImage2=1" alt="Portrait of ' . get_queried_object()->term_name . '"/>';
					echo $portrait;
				}

				echo '<h1 class="profile-name">' . $term->name . '</h1>';

				// If there is a department landing page within Eng News, link to it.
				$school ='';
				if ( ($demos['deptLandPage']) ) {
					$school = '<a href="' . $demos['deptLandPage'] . '">' . $demos['department'] . '</a>';
				} else {
					$school = $demos['department'];
				}

				echo '<p class="lead">' . $demos['title'] . ', ' . $school . '</p>';

				// Use the full bio if there is one. Look for a short bio if the long one is empty.
				$bio = '';
				if ( ! empty( $demos['shortBio'] )) {
					$bio = '<p>' . $demos['shortBio'] . '</p>';
				}

				echo wp_kses_post($bio);

				// div.infobar: Social media icons, email address and search button.
				// Do a basic check for an employee ID number. If absent, assume no Search data.
				if (! empty( $demos['eid'] ) ) {
					$isearch_btn = '<a class="isearch btn btn-md btn-maroon" href="https://search.asu.edu/profile/' . $demos['eid'] . '" target="_blank">ASU Search</a>';
					$email_btn = '<a class="email btn btn-md btn-maroon" aria-label="Mail to: ' . $demos['email_address'] . ' href="mailto:' . $demos['email_address'] . '" target=_blank><span class="fas fa-envelope"></span>Email</a>';
					echo '<div class="info-bar">' . $isearch_btn . $email_btn . '</div>';
				}

				?>

			</div>
				<?php

					$topic_terms = [];

					// Query args for posts and external_news
					$query_args = [
						'post_type' => ['post', 'external_news'],
						'posts_per_page' => -1,
						'post_status' => 'publish',
						'tax_query' => [
							[
								'taxonomy' => 'asu_person',
								'field'    => 'term_id',
								'terms'    => $term->term_id,
							],
						],
						'fields' => 'ids', // optimize: get post IDs only
					];

					$person_query = new WP_Query( $query_args );

					if ( $person_query->have_posts() ) {
						foreach ( $person_query->posts as $post_id ) {
							// Get all topics terms for this post
							$post_topics = get_the_terms( $post_id, 'topic' );
							if ( $post_topics && ! is_wp_error( $post_topics ) ) {
								foreach ( $post_topics as $topic_term ) {
									// Use term_id as key to prevent duplicates
									$topic_terms[ $topic_term->term_id ] = $topic_term;
								}
							}
						}
					}

					wp_reset_postdata();

					// Output the list if any topics were found
					if ( ! empty( $topic_terms ) ) {
						echo '<div id="related-topics">';
						echo '<h4>Related topics</h4>';
						echo '<p>This person is seen frequently talking about the following topics.</p>';
						echo '<ul class="related-topics-list">';
						foreach ( $topic_terms as $topic_term ) {
							echo '<li><a href="' . esc_url( get_term_link( $topic_term) ) . '">' . esc_html( $topic_term->name ) . '</a></li>';
						}
						echo '</ul>';
						echo '</div>';
					}
				?>
			</div>
		</div>


		<?php

		/**
		 * Loop through posts, assemble content into two arrays.
		 * Echo each array as a separate format of the post content.
		 */
		$post_args = [
			'post_type' => 'post',
			'posts_per_page' => -1,
			'tax_query' => [
				[
					'taxonomy' => 'asu_person',
					'field'    => 'slug',
					'terms'    => get_queried_object()->slug,
				],
			],
		];

		$post_query = new WP_Query($post_args);

		$posts_array = $post_query->have_posts() ? $post_query->posts : [];
		$total_posts = count($posts_array);

		if ($total_posts) {

			$card_indexes = [];
			$column_indexes = [];

			// Display all items as cards if there are 1, 2 or 4 results.
			// Display only the first 2 items as cards if there are 3
			// Display > 4 items as post-column elements under card layout.

			if ($total_posts <= 2) {
				$card_indexes = range(0, $total_posts - 1);
			} elseif ($total_posts == 3) {
				$card_indexes = [0, 1];
				$column_indexes = [2];
			} elseif ($total_posts == 4) {
				$card_indexes = range(0, 3);
			} elseif ($total_posts > 4) {
				$card_indexes = range(0, 3);
				$column_indexes = range(4, $total_posts - 1);
			}

			if (!empty($card_indexes)) {

				echo '<div id="post-cards">';

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
						<img decoding="async"
							class=""
							src="<?php echo esc_url(get_the_post_thumbnail_url(null, 'medium')); ?>"
							alt="<?php echo esc_attr(get_post_meta(get_post_thumbnail_id(), '_wp_attachment_image_alt', true)); ?>"
						/>
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

		} // end posts loop

		?>

	</section>

	<?php
	/**
	 * Query for /external posts. Build separate section if additional content located.
	 */

	$args = [
		'post_type' => 'external_news',
		'posts_per_page' => -1,
		'tax_query' => [
			[
				'taxonomy' => 'asu_person',
				'field'    => 'slug',
				'terms'    => $term->slug,
			],
		],
	];

	$external_query = new WP_Query($args);
	?>

	<?php if ($external_query->have_posts()): ?>

		<section id="external-wrapper" class="alignfull has-gray-2-background-color">
			<div class="external-layout">
				<div class="landmark">
					<h2>External News postings</h2>
					<p class="lead">Mentioned in other media sources like these.</p>
				</div>

				<div class="story-column">
					<?php while ($external_query->have_posts()): $external_query->the_post(); ?>

						<div class="card card-story" style="">
							<img decoding="async"
								class="card-img-top"
								src="<?php echo esc_url(get_the_post_thumbnail_url(null, 'large')); ?>"
								alt="<?php echo esc_attr(get_post_meta(get_post_thumbnail_id(), '_wp_attachment_image_alt', true)); ?>"
							/>
							<div class="card-header">
								<?php
								$external_link = get_field('_itn_mainurl', $external_query->ID);
								echo '<h3 class="card-title"><a href="' . $external_link . '">' . get_the_title() . '</a></h3>';
								?>
							</div>
							<div class="card-body">
								<div class="external-sourceinfo">
									<?php
										// Fancy quick loop through publications term list. Normally one term.
										$pub_obj_list = get_the_terms( $external_query->ID, 'publication' );
										$pubs_string = join(', ', wp_list_pluck($pub_obj_list, 'name'));
									?>
									<div class="publication"><span class="fa-light fa-newspaper"></span><?php echo $pubs_string; ?></div>
									<div class="post-date">
										<span class="fa-light fa-calendar-days" title="External news story published on:"></span>
										<?php echo get_the_date('F j, Y'); ?>
									</div>
								</div>
								<?php the_content(); ?>
							</div>
						</div>

					<?php endwhile; ?>
				</div>

			</div>
		</section>
		<?php
			wp_reset_postdata();
			unset($post); // extra precaution
	endif;
	?>

</main>

<?php get_footer(); ?>
