<?php
/**
 * inc/cpt-auto-seo-settings.php
 *
 * Adds SEO settings for outstand_grad and faculty that use the uploaded
 * social image from ACF and the remainder of the settings in the post to
 * duplicate what Rank Math or Yoast SEO would do.
 */

/**
 * Disable Yoast SEO for outstand_grad and faculty.
 * Official way to turn off Yoast output on the front end for this request.
 */

add_action( 'template_redirect', function () {
	// If Yoast isn't active, nothing to do.
	if ( ! defined( 'WPSEO_VERSION' ) ) return;

	if ( is_singular( array( 'outstand_grad', 'faculty' ) ) ) {

		$front_end = YoastSEO()->classes->get( Yoast\WP\SEO\Integrations\Front_End_Integration::class );
		remove_action( 'wpseo_head', [ $front_end, 'present_head' ], -9999 );
	}
}, 9);

/**
 * Render tags in head of single CPT elements.
 */
add_action( 'wp_head', 'pf_meta_wp_head', 5 );
function pf_meta_wp_head() {
	if ( ! is_singular() ) return;

	$post_id   = get_queried_object_id();
	$post_type = get_post_type( $post_id );

	$owned = array( 'outstand_grad', 'faculty' );
	$yoast_active = defined( 'WPSEO_VERSION' );

	// If Yoast is active AND this CPT is NOT listed, let Yoast handle it.
	if ( $yoast_active && ! in_array( $post_type, $owned, true ) ) {
		return;
	}

	if ( ! in_array( $post_type, $owned, true ) ) {
		return; // Not a target CPT; do nothing
	}

	$ctx = pf_build_meta_context( $post_id, $post_type );
	pf_render_meta_tags( $ctx );
}


/**
 * Build a normalized context array used by the renderer.
 */
function pf_build_meta_context( $post_id, $post_type ) {
	$ctx = array();

	$ctx['site_name']    = get_bloginfo( 'name' );
	$ctx['title']        = wp_get_document_title();
	$ctx['url']          = function_exists('wp_get_canonical_url') ? wp_get_canonical_url( $post_id ) : get_permalink( $post_id );
	$ctx['desc']         = pf_get_meta_description( $post_id, $post_type );
	$ctx['social_title'] = pf_get_social_title( $post_id, $post_type ) ?: $ctx['title'];
	$ctx['image']        = pf_get_social_image( $post_id, $post_type ); // ['url','width','height','alt','mime','secure_url']
	$ctx['type']         = 'article';
	$ctx['published']    = get_the_date( DATE_W3C, $post_id );
	$ctx['modified']     = get_the_modified_date( DATE_W3C, $post_id );
	$ctx['schema']       = pf_get_schema_for_post( $post_id, $post_type, $ctx );

	/**
	 * Final filter in case you want to tweak anything site-wide later.
	 */
	return apply_filters( 'pf_meta_context', $ctx, $post_id, $post_type );
}

function pf_get_social_title( $post_id, $post_type ) {
	switch ( $post_type ) {
		case 'outstand_grad': {
			$grad_term = pf_first_term_name( $post_id, array( 'graduation_date', 'academic_year' ) );
			$type_term = pf_first_term_name( $post_id, array( 'graduate_type' ) );

			// "Spring 2025 Fulton Schools Outstanding Graduate Timothy Chase"
			$parts = array_filter( array(
				$grad_term,
				'Fulton Schools',
				$type_term,
				get_the_title( $post_id ),
			) );

			return $parts ? implode( ' ', $parts ) : get_the_title( $post_id );
		}

		case 'faculty': {
			$year   = pf_first_term_name( $post_id, array( 'academic_year' ) );
			$title  = get_the_title( $post_id );
			$suffix = 'Fulton Schools New Faculty Member';

			// "Yeonjung Lee - 2024–25 Fulton Schools New Faculty Member"
			return $year
				? "{$title} - {$year} {$suffix}"
				: "{$title} - {$suffix}";
		}
	}

	// Default: fall back to document title via caller.
	return '';
}


/**
 * Set meta description for post types.
 */
function pf_get_meta_description( $post_id, $post_type ) {

	// Generic approach for now. Use the excerpt for the description.
	// Fallback for the function is to build from the content.
	$excerpt = get_the_excerpt( $post_id );
	if ( $excerpt ) {
		return pf_trim_sentence( wp_strip_all_tags( $excerpt ), 160 );
	}

	$content = wp_strip_all_tags( get_post_field( 'post_content', $post_id ) );
	return pf_trim_sentence( preg_replace( '/\s+/', ' ', $content ), 160 );
}


/**
 * Choose the social image per post type.
 * For 'outstand_grad', use `_outgrad_social_image` via ACF, then featured image.
 * For 'faculty', use `_faculty_social_image` via ACF, then featured image.
 */
function pf_get_social_image( $post_id, $post_type ) {
	$map = array(
		'outstand_grad' => '_outgrad_social_image',
		'faculty'       => '_faculty_social_image',
	);
	$acf_key = $map[ $post_type ] ?? null;

	$img_id  = 0;
	$img_url = '';

	// Accept ID / array / URL from ACF
	if ( $acf_key && function_exists('get_field') ) {
		$val = get_field( $acf_key, $post_id );
		if ( is_array( $val ) && ! empty( $val['ID'] ) ) {
			$img_id = (int) $val['ID'];
		} elseif ( is_numeric( $val ) ) {
			$img_id = (int) $val;
		} elseif ( is_string( $val ) && filter_var( $val, FILTER_VALIDATE_URL ) ) {
			$img_url = $val; // fallback path if you truly only have a URL
		}
	}

	if ( ! $img_id && ! $img_url ) {
		$img_id = get_post_thumbnail_id( $post_id );
	}

	$w = $h = 0; $mime = ''; $alt = '';

	if ( $img_id ) {
		$src = wp_get_attachment_image_src( $img_id, 'full' );
		if ( $src ) {
			// Normalize to HTTPS for scrapers
			$img_url = set_url_scheme( $src[0], 'https' );
			$w = (int) $src[1];
			$h = (int) $src[2];
		}
		$mime = get_post_mime_type( $img_id ) ?: '';
		$alt  = get_post_meta( $img_id, '_wp_attachment_image_alt', true ) ?: '';
	}
	// If we came from an ACF URL (no $img_id), still normalize to HTTPS.
	if ( $img_url ) {
		$img_url = set_url_scheme( $img_url, 'https' );
	}

	return array(
		'url'        => $img_url ?: '',
		'width'      => $w,
		'height'     => $h,
		'alt'        => $alt,
		'mime'       => $mime,
		// After normalization, secure_url can simply mirror url.
		'secure_url' => $img_url ?: '',
	);
}


function pf_render_meta_tags( $ctx ) {
	echo "\n<!-- Auto SEO/Social (Engineering News child theme) -->\n";

	// Only output description if it’s non-empty.
	if ( ! empty( $ctx['desc'] ) ) {
		printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $ctx['desc'] ) );
	}

	printf( '<meta property="og:site_name" content="%s" />' . "\n", esc_attr( $ctx['site_name'] ) );

	$og_title = $ctx['social_title'] ?? $ctx['title'];
	printf( '<meta property="og:title" content="%s" />' . "\n", esc_attr( $og_title ) );

	if ( ! empty( $ctx['desc'] ) ) {
		printf( '<meta property="og:description" content="%s" />' . "\n", esc_attr( $ctx['desc'] ) );
	}
	printf( '<meta property="og:type" content="%s" />' . "\n", esc_attr( $ctx['type'] ) );
	if ( ! empty( $ctx['url'] ) ) {
		printf( '<meta property="og:url" content="%s" />' . "\n", esc_url( $ctx['url'] ) );
	}

	if ( ! empty( $ctx['image']['url'] ) ) {
		printf( '<meta property="og:image" content="%s" />' . "\n", esc_url( $ctx['image']['url'] ) );
		if ( ! empty( $ctx['image']['secure_url'] ) ) {
			printf( '<meta property="og:image:secure_url" content="%s" />' . "\n", esc_url( $ctx['image']['secure_url'] ) );
		}
		if ( ! empty( $ctx['image']['width'] ) ) {
			printf( '<meta property="og:image:width" content="%d" />' . "\n", (int) $ctx['image']['width'] );
		}
		if ( ! empty( $ctx['image']['height'] ) ) {
			printf( '<meta property="og:image:height" content="%d" />' . "\n", (int) $ctx['image']['height'] );
		}
		if ( ! empty( $ctx['image']['mime'] ) ) {
			printf( '<meta property="og:image:type" content="%s" />' . "\n", esc_attr( $ctx['image']['mime'] ) );
		}
		if ( ! empty( $ctx['image']['alt'] ) ) {
			printf( '<meta property="og:image:alt" content="%s" />' . "\n", esc_attr( $ctx['image']['alt'] ) );
			printf( '<meta name="twitter:image:alt" content="%s" />' . "\n", esc_attr( $ctx['image']['alt'] ) );
		}
	}

	echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
	printf( '<meta name="twitter:title" content="%s" />' . "\n", esc_attr( $og_title ) );

	if ( ! empty( $ctx['desc'] ) ) {
		printf( '<meta name="twitter:description" content="%s" />' . "\n", esc_attr( $ctx['desc'] ) );
	}
	if ( ! empty( $ctx['image']['url'] ) ) {
		printf( '<meta name="twitter:image" content="%s" />' . "\n", esc_url( $ctx['image']['url'] ) );
	}

	if ( ! empty( $ctx['published'] ) ) {
		printf( '<meta property="article:published_time" content="%s" />' . "\n", esc_attr( $ctx['published'] ) );
	}
	if ( ! empty( $ctx['modified'] ) ) {
		printf( '<meta property="article:modified_time" content="%s" />' . "\n", esc_attr( $ctx['modified'] ) );
	}

	// JSON-LD stays as-is.
	if ( ! empty( $ctx['schema'] ) ) {
		echo '<script type="application/ld+json">' . wp_json_encode( $ctx['schema'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
	}

	echo "<!-- /Auto SEO/Social -->\n";
}


/**
 * Minimal JSON-LD that you can branch per type.
 */
function pf_get_schema_for_post( $post_id, $post_type, $ctx ) {
	switch ( $post_type ) {
		case 'outstand_grad':
			$schema = array(
				'@context' => 'https://schema.org',
				'@type'    => 'Person',
				'name'     => get_the_title( $post_id ),
				'url'      => $ctx['url'] ?? '',
			);
			if ( ! empty( $ctx['image']['url'] ) ) {
				$schema['image'] = $ctx['image']['url'];
			}
			return $schema;

		case 'faculty':
			$schema = array(
				'@context' => 'https://schema.org',
				'@type'    => 'Person',
				'name'     => get_the_title( $post_id ),
				'url'      => $ctx['url'] ?? '',
			);
			if ( ! empty( $ctx['image']['url'] ) ) {
				$schema['image'] = $ctx['image']['url'];
			}
			return $schema;
	}

	// Default: Article
	return array(
		'@context' => 'https://schema.org',
		'@type'    => 'Article',
		'headline' => get_the_title( $post_id ),
		'url'      => $ctx['url'] ?? '',
	);
}

/**
 * Helper: return first term name for the first taxonomy that has terms.
 * Helper: Avoid mid-word cutting for trimming content or exceprt text.
 */
function pf_first_term_name( $post_id, $taxonomies ) {
	foreach ( (array) $taxonomies as $tax ) {
		$terms = get_the_terms( $post_id, $tax );
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			return $terms[0]->name;
		}
	}
	return '';
}

function pf_trim_sentence( $text, $max = 160 ) {
	$text = trim( preg_replace( '/\s+/', ' ', $text ) );
	if ( strlen( $text ) <= $max ) {
		return $text;
	}
	$cut = substr( $text, 0, $max );
	// Avoid mid-word cut.
	$cut = preg_replace( '/\s+\S*$/', '', $cut );
	return rtrim( $cut, '.,;:!?' ) . '…';
}
