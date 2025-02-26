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

			<?php
				// Gather info for the eyebrow above the post title.

				$award_terms = get_the_terms( $post->ID, 'graduate_type');
				$award_array = array();
				if ( ! empty( $award_terms ) ) {
					foreach ($award_terms as $award) {
						$award_array[] = $award->name;
					}
				}

				$graddates = get_the_terms( $post->ID, 'academic_year');
				$grad_date = $graddates[0]->name;
				$grad_date_id = $graddates[0]->term_id; // Save this for the bottom query.

				$eyebrow = '';
				$eyebrow = implode(' + ', $award_array) . ', ' . $grad_date;
				$eyebrow = '<h3><span class="highlight-gold">' . $eyebrow . '</span></h3>';

				echo $eyebrow;
				// <h3><span class="highlight-gold">{Award type + Award type}, Graduation Year</span></h3>
			?>

			<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

		</header>

		<div class="content-wrap">

			<aside class="secondary">
				<div class="sidebar-wrap">

					<?php echo get_the_post_thumbnail($post_id, 'full', array( 'class' => 'img-fluid' )); ?>

					<?php
						$prog_study = get_field('_outgrad_program_study') ?? '';
						$hometown = get_field('_outgrad_hometown') ?? '';
						$prev_school = get_field('_outgrad_previous_school') ?? '';

						$gradmeta = '<ul class="grad-meta uds-list fa-ul">';
						$gradmeta .= '<li><i class="fa-li fa-sharp fa-regular fa-award"></i>' . $prog_study . '</li>';
						$gradmeta .= '<li><i class="fa-li fa-sharp fa-regular fa-location-dot"></i>' . $hometown . '</li>';
						$gradmeta .= '<li><i class="fa-li fa-regular fa-school"></i>' . $prev_school . '</li>';
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
			<h2>More <span class="highlight-black">exceptional graduates</span> from <?php echo $grad_date; ?></h2>

			<?php
				$args = array(
					'post_type'      => 'outstand_grad',
					'posts_per_page' => -1, // Retrieve all matching posts
					'post__not_in'   => array($current_post_id),
					'order' 		 => 'ASC',
					'tax_query'      => array(
						array(
							'taxonomy' => 'academic_year',
							'field'    => 'term_id',
							'terms'    => $grad_date_id
						),
					),
				);

				$query = new WP_Query($args);

				if ($query->have_posts()) :

					$gradloop = '<div class="grad-loop">';

					while ($query->have_posts()) : $query->the_post();
						// Output post content or custom fields

						$award_terms = get_the_terms( $post_id, 'graduate_type');
						$award_array = array();
						if ( ! empty( $award_terms ) ) {
							foreach ($award_terms as $award) {
								$award_array[] = $award->name;
							}
						}

						$gradpost = '';

						$gradpost .= '<div class="grad-profile">';
						$gradpost .= get_the_post_thumbnail($post_id, 'medium', array( 'class' => 'img-fluid' ));
						$gradpost .= '<div class="profile-data">';
						$gradpost .= '<h3 class="person-name"><a href="' . get_the_permalink() . '">' . get_the_title() . '</a></h3>';
						$gradpost .= '<p class="person-award lead">' . implode(', ', $award_array) . '</p>';
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
