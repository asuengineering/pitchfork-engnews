<?php
/**
 * Template part: Related people by school for topic archives.
 * Self-contained — reads the queried object and paged var itself.
 *
 * Expects:
 *  - function get_asu_person_profile( WP_Term|int ) to exist and return array with 'photo', 'display_name', 'school_unit_term_id', 'school_unit_term_name', 'school_unit' keys as used below.
 */

if ( ! defined( 'ABSPATH' ) ) {
    return;
}

$term  = get_queried_object(); // WP_Term (should be topic)
$paged = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;

// Only show on first page
if ( 1 !== (int) $paged ) {
    return;
}

if ( ! $term || is_wp_error( $term ) ) {
    return;
}

/**
 * 1) Get ALL post IDs for this topic (IDs only; no paging)
 */
$person_posts_args = [
    'post_type'      => [ 'post', 'external_news' ], // adjust if needed
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'fields'         => 'ids',
    'tax_query'      => [
        [
            'taxonomy' => $term->taxonomy,
            'field'    => 'slug',
            'terms'    => $term->slug,
        ],
    ],
];

$post_id_query = new WP_Query( $person_posts_args );
$post_ids      = $post_id_query->posts; // IDs due to 'fields' => 'ids'
wp_reset_postdata();

/**
 * 2) Collect all unique asu_person terms attached to those posts
 */
$people_terms = [];
if ( ! empty( $post_ids ) ) {
    $people_terms = wp_get_object_terms(
        $post_ids,
        'asu_person',
        [
            'fields'     => 'all',
            'hide_empty' => true,
        ]
    );
}

if ( is_wp_error( $people_terms ) || empty( $people_terms ) ) {
    return;
}

/**
 * 3) Build school buckets
 * Bucket shape: [ $bucket_key => [ 'label' => string, 'url' => string, 'desc' => string, 'people' => WP_Term[] ] ]
 */
$school_buckets = [];

foreach ( $people_terms as $person_term ) {
    $profile = get_asu_person_profile( $person_term );

    // Derive bucket details from profile
    $school_id   = (int) ( $profile['school_unit_term_id'] ?? 0 );
    $school_name = (string) ( $profile['school_unit_term_name'] ?? '' );
    $school_url  = (string) ( $profile['school_unit']['url'] ?? '' );

    // School fullname from term description fallback
    $school_fullname = '';
    if ( $school_id > 0 ) {
        $school_term = get_term( $school_id, 'school_unit' );
        if ( $school_term instanceof WP_Term && ! is_wp_error( $school_term ) ) {
            $school_fullname = $school_term->description !== '' ? $school_term->description : $school_term->name;
        }
    }

    if ( $school_id <= 0 || $school_name === '' ) {
        $bucket_key  = 'unknown';
        $bucket_label = 'Other / Unspecified';
        $bucket_url   = '';
        $bucket_desc  = 'Other schools or units';
    } else {
        $bucket_key   = 'school_' . $school_id;
        $bucket_label = $school_name;
        $bucket_url   = $school_url;
        $bucket_desc  = $school_fullname;
    }

    if ( ! isset( $school_buckets[ $bucket_key ] ) ) {
        $school_buckets[ $bucket_key ] = [
            'label'  => $bucket_label,
            'url'    => $bucket_url,
            'desc'   => $bucket_desc,
            'people' => [],
        ];
    }

    // De-dupe by term_id
    $school_buckets[ $bucket_key ]['people'][ $person_term->term_id ] = $person_term;
}

/**
 * 4) Output if any buckets exist
 */
if ( empty( $school_buckets ) ) {
    return;
}

// Sort buckets (Fulton first, unknown last, otherwise by description)
uksort( $school_buckets, function( $a, $b ) use ( $school_buckets ) {
    if ( 'fulton-schools' === $a ) return -1;
    if ( 'fulton-schools' === $b ) return 1;
    if ( 'unknown' === $a ) return 1;
    if ( 'unknown' === $b ) return -1;
    return strcasecmp( $school_buckets[ $a ]['desc'], $school_buckets[ $b ]['desc'] );
} );

?>
<section id="related-people-wrap" class="alignfull">
    <div id="related-people">
        <h2><?php esc_html_e( 'Related People by School', 'your-textdomain' ); ?></h2>
        <p class="lead"><?php esc_html_e( 'People mentioned in stories for this topic, grouped by their home school or unit.', 'your-textdomain' ); ?></p>

        <?php foreach ( $school_buckets as $bucket_key => $bucket ) :
            $fullname = $bucket['desc'];
            $people   = $bucket['people'];

            if ( empty( $people ) ) {
                continue;
            }

            // Sort people by last name (rightmost-word heuristic)
            uasort( $people, function( $a, $b ) {
                $la = substr( strrchr( $a->name, ' ' ), 1 ) ?: $a->name;
                $lb = substr( strrchr( $b->name, ' ' ), 1 ) ?: $b->name;
                return strcasecmp( $la, $lb );
            } );
            ?>
            <div class="related-school">
                <h3 class="school-name"><?php echo esc_html( $fullname ); ?></h3>

                <div class="people-list">
                    <?php foreach ( $people as $person_term ) :
                        $person_details = get_asu_person_profile( $person_term );
                        if ( empty( $person_details['photo'] ) ) {
                            continue; // skip if no photo available
                        }

                        $person_link = get_term_link( $person_term );
                        if ( is_wp_error( $person_link ) ) {
                            $person_link = '';
                        }

                        $img_alt = ! empty( $person_details['display_name'] ) ? $person_details['display_name'] : '';
                        ?>
                        <div class="person">
                            <?php if ( $person_link ) : ?>
                                <a href="<?php echo esc_url( $person_link ); ?>" title="<?php echo esc_attr( $img_alt ); ?>">
                                    <img class="search-img img-fluid" src="<?php echo esc_url( $person_details['photo'] . '?blankImage2=1' ); ?>" alt="<?php echo esc_attr( 'Portrait of ' . $img_alt ); ?>" />
                                </a>
                            <?php else : ?>
                                <img class="search-img img-fluid" src="<?php echo esc_url( $person_details['photo'] . '?blankImage2=1' ); ?>" alt="<?php echo esc_attr( 'Portrait of ' . $img_alt ); ?>" />
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

    </div>
</section>
<?php
// end template part
