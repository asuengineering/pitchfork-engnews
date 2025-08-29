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

		// NEW: map deptid → school_unit term info
		$school_map = pf_get_schoolunit_term_from_dept( $person['deptid'] );

		// Keep your original field for compatibility
		$person['deptLandPage'] = $school_map['url'] ?? '';

		// Add structured term info
		// Add convenience keys for quick access/bucketing
		$person['school_unit'] = $school_map; // ['term_id','taxonomy','slug','name','url']
		$person['school_unit_term_id']   = $school_map['term_id'] ?? 0;
		$person['school_unit_term_slug'] = $school_map['slug'] ?? '';
		$person['school_unit_term_name'] = $school_map['name'] ?? '';

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
 * Retrieves ASU person profile (cached), with optional force refresh.
 *
 * @param WP_Term $term  A term object from the 'asu_person' taxonomy.
 * @param bool    $force Force refresh from API and overwrite cache. Default false.
 * @return array|false
 */
function get_asu_person_profile( $term, $force = false ) {
	if ( ! $term instanceof WP_Term || $term->taxonomy !== 'asu_person' ) {
		return false;
	}

	$term_id = $term->term_id;
	$asurite = get_field( 'asuperson_asurite', 'asu_person_' . $term_id );
	if ( empty( $asurite ) ) {
		return false;
	}

	$profile      = get_term_meta( $term_id, 'asu_person_profile', true );
	$last_updated = get_term_meta( $term_id, 'asu_person_last_updated', true );

	$max_age = DAY_IN_SECONDS * 14;

	$should_refresh = (
		$force ||
		empty( $profile ) ||
		empty( $last_updated ) ||
		( time() - (int) $last_updated ) > $max_age
	);

	if ( $should_refresh ) {
		$profile = get_asu_search_data( $asurite );
		if ( $profile && is_array( $profile ) ) {
			update_term_meta( $term_id, 'asu_person_profile', $profile );
			update_term_meta( $term_id, 'asu_person_last_updated', time() );
			if ( ! empty( $profile['school_unit_term_id'] ) ) {
				update_term_meta( $term_id, 'asu_person_school_unit_term_id', (int) $profile['school_unit_term_id'] );
			}
		} else {
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

/**
 * Logic for generating a link to a department landing page within Eng News.
 * Array associates a known deptID with a term_id for a term within school_unit
 */

/**
 * Map ASU primary deptid -> taxonomy school_unit term data.
 *
 * @param string|int $dept DeptID from ASU Search (string keys allowed).
 * @return array{
 *   term_id:int,
 *   taxonomy:string,
 *   slug:string,
 *   name:string,
 *   url:string
 * }|array Empty array on no-match or errors.
 */
function pf_get_schoolunit_term_from_dept( $dept ) {
	$dept = (string) $dept;

	$dept_links = array(
		'1659' => '26',  // SBHSE
		'1660' => '36',  // SSEBE
		'1662' => '25',  // SEMTE
		'1663' => '30',  // ECEE
		'1661' => '912', // SCAI
		'35480' => '159', // POLY
		'N1659649552' => '914',  // MSN
		'N1705593272' => '1113', // SIE

		// Poly has multiple subdepartments, each should also link back to the main site.
		'35488' => '159',
		'35489' => '159',
		'35490' => '159',
		'35491' => '159',
		'35492' => '159',
		'35493' => '159',
		'35494' => '159',
		'35495' => '159',
		'35496' => '159',
		'35497' => '159',
		'35498' => '159',
		'35499' => '159',
		'35558' => '159',
		'35559' => '159',
		'35560' => '159',

		// Other ASU Engineering departments
		'1405' => '6', 		// Fulton Schools
		'1639' => '188',  	// Academic and Student Affairs (ASA)
		'1649' => '40',  	// Global Outreach and Extended Education
		'2215' => '47',		// Del E. Web Construction, not likely to be primary anyplace

		// Barrett Honors college not included in array. Also not likely to be primary.
	);

	if ( ! isset( $dept_links[ $dept ] ) ) {
		return [];
	}

	$term_id = (int) $dept_links[ $dept ];
	$term    = get_term( $term_id, 'school_unit' );

	if ( ! $term instanceof WP_Term || is_wp_error( $term ) ) {
		return [];
	}

	$link = get_term_link( $term );
	if ( is_wp_error( $link ) ) {
		$link = '';
	}

	return [
		'term_id'  => $term_id,
		'taxonomy' => 'school_unit',
		'slug'     => $term->slug,
		'name'     => $term->name,
		'url'      => $link,
	];
}

/**
 * Returning only the URL for the school landing page.
 * Backwards compabible with a function introduced earlier.
 */
function get_schoolunit_landing_page_url( $dept ) {
	$map = pf_get_schoolunit_term_from_dept( $dept );
	return $map['url'] ?? '';
}

