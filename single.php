<?php
/**
 * single.php - a replica of the page.php template from the parent theme.
 * Layout of the page will be driven entirely by blocks.
 *
 * @package pitchfork
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();
?>

	<main id="skip-to-content" <?php post_class(); ?>>

		<?php

		$post = get_post( $post );

		while ( have_posts() ) {

			the_post();

			$content_blocks = parse_blocks( $post->post_content );
			$first_block_names = array('acf/hero', 'acf/hero-video', 'acf/hero-post', 'acf/news-header');

			if ( ! in_array( $content_blocks[0]['blockName'], $first_block_names )) {
				the_title( '<div class="page-title"><h1 class="entry-title">', '</h1></div>' );
			}

			the_content();

		}

		?>

	</main><!-- #main -->

	<?php
	/**
	 * Get post meta and determine what kind of related posts to include on the page.
	 * - Options are 'default' 'chosen' or 'none'
	 */

	$display = get_field('post_relstories_display');
	do_action('qm/debug', $display);

	// If $display == 'none', do nothing.
	// If anything else go ahead and start the output of a section below the content.

	if ( 'none' !== $display ) {

		echo '<section id="related-posts" class="alignfull">';
		echo '<div class="container"><h2><span class="highlight-black">Related stories</span></h2>';

		if ( 'chosen' === $display ) {

			$chosen = get_field('post_relstories_user_choice');

		} else {

			// The display is 'default'.
			// Get the current terms from the post and count returned results.

			$rel_people_array = get_the_terms($post, 'asu_person');
			$rel_topics_array = get_the_terms($post, 'topic');

			// Translate array of term objects into an array of term IDs.
			$rel_people = join(', ', wp_list_pluck($rel_people_array, 'term_id'));
			$rel_topics = join(', ', wp_list_pluck($rel_topics_array, 'term_id'));

			$args = array(
				'post_type' => 'post',
				'posts_per_page' => -1,
				'fields' => 'ids',
				'orderby' => 'date',
				'order'   => 'DESC',
				'post__not_in' => array( $post->ID ),
				'tax_query' => array(
					'relation' => 'AND',
					array(
						'taxonomy' => 'asu_person', // Replace with your first taxonomy slug
						'field'    => 'term_id', // Or 'term_id'
						'operator' => 'IN',
						'terms'    => $rel_people,
					),
					array(
						'taxonomy' => 'topic', // Replace with your second taxonomy slug
						'field'    => 'term_id', // Or 'term_id'
						'operator' => 'IN',
						'terms'    => $rel_topics,
					),
				),
			);

			$query_both = new WP_Query( $args );

			if ($query_both->found_posts >= 2) {

				$chosen = array_slice( $query_both->posts, 0, 3 );

			} else {

				// Didn't find enough posts. Run the fallback query using the OR logic
				$args_either = $args;
				$args_either['tax_query']['relation'] = 'OR';

				$query_either = new WP_Query($args_either);

				$chosen = array_slice( $query_either->posts, 0, 3 );
			}

		}

		/**
		 * Output. Uses array pof post IDs stored in $chosen
		 * Alter the classes present in the grid layout depending on the number of posts selected.
		 */

		$count = count($chosen);
		$gridclass = 'three-col';
		$twocol = [1,2,4];

		if ( in_array( $count, $twocol )) {
			$gridclass = 'two-col';
		}

		echo '<div class="card-wrapper ' . $gridclass . '">';

		foreach ($chosen as $choice) {
			echo '<div class="card">';
			echo get_the_post_thumbnail( $choice, 'medium', array( 'class' => 'card-img-top' ) );
			echo '<div class="card-header"><h3 class="card-title">' . get_the_title($choice) . '</h3></div>';
			echo '<div class="card-body">' . get_the_excerpt($choice) . '</div>';
			echo '<div class="card-link"><a href="' . esc_url( get_permalink($choice)) . '">Read more</a></div>';
			echo '</div>';
		}

	echo '</div></section>';

	}

	?>

<?php
get_footer();

