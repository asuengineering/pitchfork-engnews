<?php
/**
 * Displays an archive page for an ASU Person, identified by their ASURITE.
 * Pulls data from Search including current school affiliation, profile photo and bio <details class=""></details>
 */

get_header();

/**
 * Get ASURITE ID, pass to search function.
 * Returned results = API call containing all data.
 */
$term = get_queried_object();
$asurite = get_field( 'asuperson_asurite', $term );
$demos = get_asu_search_data($asurite, false);

?>
<main class="site-main" id="main">

	<section id="landmark">
		<div class="slash">Faculty or Staff</div>
	</section>

	<div id="page-header">

		<div class="row">

			<?php

			$portrait = '';

			// Check if Search has an image for us and if it's available for display.
			if (! empty( $demos['photo'])) {

				$portrait = '<div class="image-col col-md-3">';

				if ($mentor_ready) {
					$portrait .= '<img class="isearch-image mentor-ready img-fluid" ';
				} else {
					$portrait .= '<img class="isearch-image img-fluid" ';
				}

				$portrait .= 'src="' . $demos['photo'] . '?blankImage2=1" alt="Portrait of ' . get_queried_object()->term_name . '"/></div>';
			}

			// As long as we have something in $portrait, output the scaffolding + the image.
			if ( ! empty($portrait)) {
				echo $portrait;
				echo '<div class="col-md">';
			} else {
				// No portrait, but we want to constrain the following column to 3/4 width.
				echo '<div class="col-md-8">';
			}

			?>

			<h1 class="mentor-name"><?php echo $term->name; ?></h1>

			<?php

			// Check for featured mentor status. Output highlight label if so.
			$mentorstring = '';
			$mentorprogram = get_field( '_mentor_featured_program', $term );
			if ( !empty ( $mentorprogram )) {
				$mentorstring = '<h2><span class="highlight-gold">Featured mentor, ' . esc_html( $mentorprogram->name ) . '</span></h2>';
				echo $mentorstring;
			}

			// Build job title and linked school affiliation output.
			$school ='';
			if ( ($demos['deptURL']) ) {
				$school = '<a href="' . $demos['deptURL'] . '">' . $demos['department'] . '</a>';
			} else {
				$school = $demos['department'];
			}

			echo '<p class="lead">' . $demos['title'] . ', ' . $school . '</p>';

			// Use the full bio if there is one. Look for a short bio if the long one is empty.
			$bio = '';
			$bio = $demos['bio'];
			if ( empty( $bio )) {
				$bio = '<p>' . $demos['shortBio'] . '</p>';
			}

			echo wp_kses_post($bio);

			// div.infobar: Social media icons, email address and isearch button.
			// Do a basic check for an employee ID number. If absent, assume no Search data.
			if (! empty( $demos['eid'] ) ) {
				$isearch_btn = '<a class="isearch btn btn-md btn-gray" href="https://search.asu.edu/profile/' . $demos['eid'] . '" target="_blank">ASU Search</a>';
				$email_btn = '<a class="email btn btn-md btn-gray" href="mailto:' . $demos['email_address'] . '" target=_blank><span class="fas fa-envelope"></span>Email</a>';
				$socialbar = '';

				if ( ! empty( trim($demos['twitter'] ) ) ) {
					$socialbar .= '<li><a href="' . $demos['twitter'] . '" target=_blank><span class="fab fa-twitter"></span></a></li>';
				}

				if ( ! empty( trim($demos['linkedin'] ) ) ) {
					$socialbar .= '<li><a href="' . $demos['linkedin'] . '" target=_blank><span class="fab fa-linkedin"></span></a></li>';
				}

				if ( ! empty( trim($demos['facebook'] ) ) ) {
					$socialbar .= '<li><a href="' . $demos['facebook'] . '" target=_blank><span class="fab fa-facebook"></span></a></li>';
				}

				if ( ! empty( trim($demos['research_website'] ) ) ) {
					$socialbar .= '<li><a href="' . $demos['research_website'] . '" target=_blank><span class="fas fa-globe"></span></a></li>';
				}

				if ( ! empty( $socialbar ) ) {
					$socialbar =  '<ul class="social-icons">' . $socialbar . '</ul>';
				}

				echo '<div class="info-bar">' . $isearch_btn . $email_btn . $socialbar . '</div>';
			}

			?>

			</div>
		</div>
	</div>

	<?php

	// Begin related posts and external_news
	echo '<h2>Steve has a template.</h2>';

	?>

</main>

<?php get_footer(); ?>
