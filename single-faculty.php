<?php
/**
 *
 * A single outstanding graduate post.
 *
 * @package pitchfork
 */


// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();
?>

	<article id="skip-to-content post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<?php while ( have_posts() ) {

		the_post();

		$current_post_id = get_the_ID(); // Get the current post ID

		?>

		<header class="entry-header">

			<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

			<?php
				// Gather info for the eyebrow above the post title.

				$faculty_title = get_field('_faculty_title') ?? '';

				$ac_years = get_the_terms( $post->ID, 'academic_year');
				$ac_year = $ac_years[0]->name;
				$ac_year_id = $ac_years[0]->term_id; // Save this for the bottom query.

				$eyebrow = '';
				$eyebrow = $faculty_title;
				$eyebrow = '<h3><span class="highlight-gold">' . $eyebrow . '</span></h3>';

				echo $eyebrow;
			?>

		</header>

		<div class="content-wrap">

			<aside class="secondary">
				<div class="sidebar-wrap">

					<?php echo get_the_post_thumbnail($post_id, 'full', array( 'class' => 'img-fluid' )); ?>

					<?php

						$units = get_the_terms( $post->ID, 'school_unit');
						$unit_array = array();
						if ( ! empty( $units ) ) {
							foreach ($units as $unit) {
								$unit_array[] = $unit->name;
							}
						}

						$faculty_degree = get_field('_faculty_highest_degree') ?? '';
						$faculty_expertise = get_field('_faculty_expertise') ?? '';

						$gradmeta = '<ul class="grad-meta uds-list fa-ul">';
						$gradmeta .= '<li><i class="fa-li fa-regular fa-school"></i>' . implode(' + ', $unit_array) . '</li>';
						$gradmeta .= '<li><i class="fa-li fa-sharp fa-regular fa-award"></i>' . $faculty_degree . '</li>';
						$gradmeta .= '<li><i class="fa-li fa-regular fa-lightbulb-on"></i>' . $faculty_expertise . '</li>';
						$gradmeta .= '</ul>';

						echo $gradmeta;
					?>

				</div>
			</aside>

			<section class="content">
				<?php the_content(); ?>
			</section>

		<?php  // Comments template would go here.

	} // end while_have_posts

	echo '</article>';

	?>

	<section id="grad-list">
		<div class="container">
			<h2>More <span class="highlight-black">new faculty</span> from <?php echo $ac_year; ?></h2>

			<?php
				$args = array(
					'post_type'      => 'faculty',
					'posts_per_page' => -1, // Retrieve all matching posts
					'post__not_in'   => array($current_post_id),
					'order' 		 => 'ASC',
					'tax_query'      => array(
						array(
							'taxonomy' => 'academic_year',
							'field'    => 'term_id',
							'terms'    => $ac_year_id
						),
					),
				);

				$query = new WP_Query($args);

				if ($query->have_posts()) :

					$gradloop = '<div class="grad-loop">';

					while ($query->have_posts()) : $query->the_post();
						// Output post content or custom fields

						$rel_faculty_title = get_field('_faculty_title') ?? '';

						$gradpost = '';

						$gradpost .= '<div class="grad-profile">';
						$gradpost .= get_the_post_thumbnail($post_id, 'medium', array( 'class' => 'img-fluid' ));
						$gradpost .= '<div class="profile-data">';
						$gradpost .= '<h3 class="person-name"><a href="' . get_the_permalink() . '">' . get_the_title() . '</a></h3>';
						$gradpost .= '<p class="person-award">' . $rel_faculty_title . '</p>';
						$gradpost .= '</div></div>';

						$gradloop .= $gradpost;

					endwhile;
					wp_reset_postdata();

					$gradloop .= "</div>";

					echo $gradloop;

				else :
					echo '<p>No outstanding graduates found.</p>';
				endif;
				?>


		</div>
	</section>

	<?php
get_footer();
