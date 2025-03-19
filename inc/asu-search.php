<?php
/**
 * Functions to return data from ASU Search.
 *
 *  - TAX: ASU_Person, used to return profile details.
 *  - BLOCK: News Related People - Returns profile img + title
 *
 * @package pitchfork-engnews
 */

 /**
  * Pings ASU Search and returns an array of arrays.
  * @param string $asurites comma separated string of ASURITE IDs to include in API call.
  * @param bool $single return all array objects or only the first result of the API call.
  */
function get_asu_search_data($asurites, $return_all) {

	$people = array();

	if (! empty( $asurites )) {
		$search_json = 'https://search.asu.edu/api/v1/webdir-profiles/faculty-staff/filtered?asurite_ids=' . $asurites . '&client=fse_engnews';

		$search_request = wp_safe_remote_get( $search_json );

		// Error check for invalid JSON.
		if ( is_wp_error( $search_request ) ) {
			return false; // Bail early.
		}

		$search_body   = wp_remote_retrieve_body( $search_request );
		$search_data   = json_decode( $search_body );
		$search_results = $search_data->results;

		if ( ! empty( $search_results ) ) {

			foreach ($search_results as $path) {

				$person = array();
				$person['display_name'] = $path->display_name->raw;
				$person['photo'] = $path->photo_url->raw;
				$person['bio'] = wp_kses_post($path->bio->raw);
				$person['shortBio'] = wp_kses_post($path->short_bio->raw);
				$person['expertise_areas'] = $path->expertise_areas->raw;
				$person['email_address'] = $path->email_address->raw;
				$person['research_website'] = $path->research_website->raw;
				$person['facebook'] = $path->facebook->raw;
				$person['twitter'] = $path->twitter->raw;
				$person['linkedin'] = $path->linkedin->raw;
				$person['eid'] = $path->eid->raw;

				// Checking if primary indicators are present with faculty members.
				// If nothing marked as primary, default is not to guess.
				if (! empty( $path->primary_title->raw[0] ) ) {
					$person['title'] = $path->primary_title->raw[0];
				} else {
					// We know they are at least an employee of ASU if they have a Search record.
					$person['title'] = 'Valued employee';
				}

				// Department building. Check for empty primary, add link if available.
				$dept = $path->primary_deptid->raw;
				if (! empty( $dept ) ) {
					$person['deptid'] = $dept;
					$person['department'] = $path->primary_department->raw;
				} else {
					$person['deptid'] = '';
					$person['department'] = 'Arizona State University';
				}

				// Assign the $person array to object to be returned.
				$people[] = $person;

			}

			// do_action('qm/debug', $people);

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

		// Since there are no returned API results, just return the single array of empty strings.
		return $people[0];
	}

	// Which result is expected? A single array object or an array of arrays?
	if ($return_all) {
		return $people;
	} else {
		return $people[0];
	}
}
