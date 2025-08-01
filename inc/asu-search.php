<?php
/**
 * Functions to return data from ASU Search and cache results in the DB.
 *
 *  - TAX: ASU_Person, used to return profile details.
 *  - BLOCK: News Related People - Returns profile img + title
 *
 * @package pitchfork-engnews
 */

/**
 * Fetch ASU Person data when an asu_person term is created or updated.
 */
add_action( 'edited_asu_person', 'update_asu_person_profile_meta', 10, 2 );
add_action( 'create_asu_person', 'update_asu_person_profile_meta', 10, 2 );

/**
 * Fetch and store ASU Person data in term meta.
 *
 * @param int    $term_id Term ID.
 * @param int    $tt_id   Term taxonomy ID (unused).
 */
function update_asu_person_profile_meta( $term_id, $tt_id ) {

	$asurite = get_field( 'asuperson_asurite', 'asu_person_' . $term_id );

	if ( empty( $asurite ) ) {
		return;
	}

	// Ping the ASU directory API.
	$profile = get_asu_search_data( $asurite );

	if ( $profile && is_array( $profile ) ) {
		update_term_meta( $term_id, 'asu_person_profile', $profile );
		update_term_meta( $term_id, 'asu_person_last_updated', time() );
	}
}

/**
 * Pings ASU Search and returns a structured array for a single ASURITE.
 *
 * @param string $asurite ASURITE ID to include in API call.
 * @return array|false Returns structured data array or false on failure.
 */
function get_asu_search_data( $asurite ) {
	if ( empty( $asurite ) || ! is_string( $asurite ) ) {
		return false;
	}

	$search_url = 'https://search.asu.edu/api/v1/webdir-profiles/faculty-staff/filtered?asurite_ids=' . urlencode( $asurite ) . '&client=fse_engnews';
	$response   = wp_safe_remote_get( $search_url );

	if ( is_wp_error( $response ) ) {
		return false; // Network error, bail.
	}

	$body     = wp_remote_retrieve_body( $response );
	$data     = json_decode( $body );
	$results  = $data->results ?? [];

	$person = [];

	if ( ! empty( $results ) && is_array( $results ) ) {
		$path = $results[0]; // Expecting one profile max per ASURITE

		$person['status']            = 'found';
		$person['display_name']      = $path->display_name->raw ?? '';
		$person['photo']             = $path->photo_url->raw ?? '';
		$person['bio']               = wp_kses_post( $path->bio->raw ?? '' );
		$person['shortBio']          = wp_kses_post( $path->short_bio->raw ?? '' );
		$person['expertise_areas']   = $path->expertise_areas->raw ?? '';
		$person['email_address']     = $path->email_address->raw ?? '';
		$person['research_website']  = $path->research_website->raw ?? '';
		$person['facebook']          = $path->facebook->raw ?? '';
		$person['twitter']           = $path->twitter->raw ?? '';
		$person['linkedin']          = $path->linkedin->raw ?? '';
		$person['eid']               = $path->eid->raw ?? '';
		$person['title']             = ! empty( $path->primary_title->raw[0] ) ? $path->primary_title->raw[0] : 'Valued employee';
		$person['deptid']            = $path->primary_deptid->raw ?? '';
		$person['department']        = ! empty( $path->primary_deptid->raw ) ? $path->primary_department->raw : 'Arizona State University';
	} else {
		// Fallback for not found ASURITE
		$person = [
			'status'            => 'not_found',
			'display_name'      => '',
			'photo'             => '',
			'bio'               => '',
			'shortBio'          => '',
			'expertise_areas'   => '',
			'email_address'     => '',
			'research_website'  => '',
			'facebook'          => '',
			'twitter'           => '',
			'linkedin'          => '',
			'eid'               => '',
			'deptid'            => '',
			'deptURL'           => '',
			'title'             => '',
			'department'        => 'Arizona State University',
		];
	}

	return $person;
}

/**
 * Retrieves ASU person profile data from term meta or refreshes it from the API if needed.
 *
 * @param WP_Term $term A term object from the 'asu_person' taxonomy.
 * @return array|false The profile array or false on failure.
 */
function get_asu_person_profile( $term ) {
	if ( ! $term instanceof WP_Term || $term->taxonomy !== 'asu_person' ) {
		return false;
	}

	$term_id = $term->term_id;

	$asurite = get_field( 'asuperson_asurite', 'asu_person_' . $term_id );
	if ( empty( $asurite ) ) {
		return false;
	}

	$profile = get_term_meta( $term_id, 'asu_person_profile', true );
	$last_updated = get_term_meta( $term_id, 'asu_person_last_updated', true );

	$max_age = DAY_IN_SECONDS * 14;

	$should_refresh = (
		empty( $profile ) ||
		empty( $last_updated ) ||
		( time() - intval( $last_updated ) ) > $max_age
	);

	if ( $should_refresh ) {
		$profile = get_asu_search_data( $asurite, false );
		if ( $profile && is_array( $profile ) ) {
			update_term_meta( $term_id, 'asu_person_profile', $profile );
			update_term_meta( $term_id, 'asu_person_last_updated', time() );
		} else {
			// If the profile lookup fails (e.g., the person retired), keep any fallback values.
			$profile = false;
		}
	}

	return $profile;
}


/**
 * Make the asu_person_last_updated ACF field read-only.
 */
add_filter( 'acf/prepare_field/name=asu_person_last_updated', function( $field ) {
	$field['readonly'] = true;

	// Format the timestamp as a readable date in the site’s timezone.
	if ( ! empty( $field['value'] ) && is_numeric( $field['value'] ) ) {
		$field['value'] = wp_date( 'F j, Y \a\t g:i a', intval( $field['value'] ) );
	}

	return $field;
});
