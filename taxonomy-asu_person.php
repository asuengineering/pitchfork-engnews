<?php
/**
 * Displays an archive page for an ASU Person, identified by their ASURITE.
 * Pulls data from Search including current school affiliation, profile photo and bio <details class=""></details>
 */

get_header();
$term = get_queried_object();

function get_asu_search_data($term_id) {

	$output = array();

	// Get Search data from ASURITE ID field.
	$asurite = get_field( 'asuperson_asurite', $term_id );

	if (! empty( $asurite )) {
		$search_json = 'https://search.asu.edu/api/v1/webdir-profiles/faculty-staff/filtered?asurite_ids=' . $asurite . '&size=1&client=fse_engnews';

		$search_request = wp_safe_remote_get( $search_json );

		// Error check for invalid JSON.
		if ( is_wp_error( $search_request ) ) {
			return false; // Bail early.
		}

		$search_body   = wp_remote_retrieve_body( $search_request );
		$search_data   = json_decode( $search_body );

		if ( ! empty( $search_data ) ) {
			$path = $search_data->results[0];
			// do_action('qm/debug', $path);

			$output['photo'] = $path->photo_url->raw;
			$output['bio'] = wp_kses_post($path->bio->raw);
			$output['shortBio'] = wp_kses_post($path->short_bio->raw);
			$output['expertise_areas'] = $path->expertise_areas->raw;
			$output['email_address'] = $path->email_address->raw;
			$output['research_website'] = $path->research_website->raw;
			$output['facebook'] = $path->facebook->raw;
			$output['twitter'] = $path->twitter->raw;
			$output['linkedin'] = $path->linkedin->raw;
			$output['eid'] = $path->eid->raw;

			// Checking if primary indicators are present with faculty members.
			// If nothing marked as primary, default is not to guess.
			if (! empty( $path->primary_title->raw[0] ) ) {
				$output['title'] = $path->primary_title->raw[0];
			} else {
				// We know they are at least an employee of ASU if they have a Search record.
				$output['title'] = 'Valued employee';
			}

			// Department building. Check for empty primary, add link if available.
			$dept = $path->primary_deptid->raw;
			if (! empty( $dept ) ) {
				$output['deptid'] = $dept;
				$output['department'] = $path->primary_department->raw;
			} else {
				$output['deptid'] = '';
				$output['department'] = 'Arizona State University';
			}

		}
	} else {
		// There's no ASURITE for this term, therefore nothing returned.
		// Define basic attributes for all expected returned values anyhow.
		$output['photo'] = '';
		$output['bio'] = '';
		$output['shortBio'] = '';
		$output['expertise_areas'] = '';
		$output['email_address'] = '';
		$output['research_website'] = '';
		$output['facebook'] = '';
		$output['twitter'] = '';
		$output['linkedin'] = '';
		$output['eid'] = '';
		$output['deptid'] = '';
		$output['deptURL'] = '';
		$output['title'] = '';
		$output['department'] = 'Arizona State University';


	}

	// do_action('qm/debug', $output);
	return $output;
}

$demos = get_asu_search_data($term);

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
