<?php
/**
 * Functions to return data from ASU Search.
 *
 *  - TAX: ASU_Person, used to return profile details.
 *  - BLOCK: News Related People - Returns profile img + title
 *
 *
 * @package pitchfork-engnews
 */

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
