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
// $asurite = get_field( 'asuperson_asurite', $term );
// $demos = get_asu_search_data($asurite);
$demos = get_asu_person_profile( $term );

?>
<main class="site-main" id="main">

	<div id="profile-details">

		<section id="landmark">
			<h3><span class="highlight-black">Faculty or Staff</span></h3>
		</section>

		<div class="row">

			<?php

			$portrait = '';

			// Check if Search has an image for us and if it's available for display.
			if (! empty( $demos['photo'])) {

				$portrait = '<div class="image-col col-md-3">';
				$portrait .= '<img class="search-img img-fluid" ';
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

			<h1 class="profile-name"><?php echo $term->name; ?></h1>

			<?php

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
			$bio = $demos['bio'];
			if ( empty( $bio )) {
				$bio = '<p>' . $demos['shortBio'] . '</p>';
			}

			echo wp_kses_post($bio);

			// div.infobar: Social media icons, email address and search button.
			// Do a basic check for an employee ID number. If absent, assume no Search data.
			if (! empty( $demos['eid'] ) ) {
				$isearch_btn = '<a class="isearch btn btn-md btn-maroon" href="https://search.asu.edu/profile/' . $demos['eid'] . '" target="_blank">ASU Search</a>';
				$email_btn = '<a class="email btn btn-md btn-maroon" aria-label="Mail to: ' . $demos['email_address'] . ' href="mailto:' . $demos['email_address'] . '" target=_blank><span class="fas fa-envelope"></span>Email</a>';
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
