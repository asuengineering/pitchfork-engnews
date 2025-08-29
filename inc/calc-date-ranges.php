<?php
/**
 * Compute a Month Year date range label for a set of posts.
 * - Uses full month name (AP style): "F Y" => "September 2025"
 * - Orders label newest → oldest (reverse-chronological).
 * - Collapses to a single "Month Year" if both ends are the same month.
 *
 * @param WP_Post[] $posts
 * @param array     $args {
 *   @type bool   $gmt              Use GMT times. Default false (site timezone).
 *   @type string $monthyear_fmt    Month+Year format. Default 'F Y'.
 *   @type bool   $collapse_same    If true, collapse same-month range to one value. Default true.
 *   @type string $separator        Separator between end and start. Default ' – '.
 * }
 * @return array{start_ts:int|null,end_ts:int|null,label:string}
 */
function pf_get_date_range_for_posts( array $posts, array $args = [] ): array {
	$defaults = [
		'gmt'           => false,
		'monthyear_fmt' => 'F Y',   // e.g., "September 2025"
		'collapse_same' => true,
		'separator'     => ' – ',
	];
	$args = wp_parse_args( $args, $defaults );

	if ( empty( $posts ) ) {
		return [ 'start_ts' => null, 'end_ts' => null, 'label' => '' ];
	}

	$min = PHP_INT_MAX; // oldest
	$max = 0;           // newest

	foreach ( $posts as $p ) {
		if ( ! $p instanceof WP_Post ) {
			continue;
		}
		$ts = get_post_time( 'U', $args['gmt'], $p );
		if ( $ts && $ts < $min ) $min = $ts;
		if ( $ts && $ts > $max ) $max = $ts;
	}

	if ( $min === PHP_INT_MAX || $max === 0 ) {
		return [ 'start_ts' => null, 'end_ts' => null, 'label' => '' ];
	}

	// Format both ends as Month Year.
	$start_label = wp_date( $args['monthyear_fmt'], $min ); // oldest
	$end_label   = wp_date( $args['monthyear_fmt'], $max ); // newest

	// Same month & year?
	$same_month = ( wp_date( 'Y-m', $min ) === wp_date( 'Y-m', $max ) );

	if ( $same_month && $args['collapse_same'] ) {
		$label = $end_label; // single value
	} else {
		// Newest → Oldest (reverse-chronological)
		$label = $end_label . $args['separator'] . $start_label;
	}

	return [
		'start_ts' => $min,
		'end_ts'   => $max,
		'label'    => $label,
	];
}

/**
 * Echo a <p> with the Month Year range for a set of posts, with optional icon.
 *
 * @param WP_Post[] $posts
 * @param array     $args {
 *   @type string $class         CSS classes. Default 'date-range lead'.
 *   @type bool   $gmt           Use GMT times. Default false (site timezone).
 *   @type string $before        HTML before.
 *   @type string $after         HTML after.
 *   @type string $prefix        Visually-hidden prefix. Default ''.
 *   @type string $monthyear_fmt Month+Year format. Default 'F Y'.
 *   @type bool   $collapse_same Collapse same-month range to one value. Default true.
 *   @type string $separator     Separator between end and start. Default ' – '.
 *   @type string $icon_html     Icon HTML (e.g., '<i class="fa-solid fa-calendar"></i>'). Default ''.
 *   @type string $icon_position Where to place icon: 'before' or 'after'. Default 'before'.
 * }
 * @return void
 */
function pf_the_date_range_for_posts( array $posts, array $args = [] ): void {
	$defaults = [
		'class'         => 'date-range lead',
		'gmt'           => false,
		'before'        => '',
		'after'         => '',
		'prefix'        => '',
		'monthyear_fmt' => 'F Y',
		'collapse_same' => true,
		'separator'     => ' – ',
		'icon_html'     => '',
		'icon_position' => 'before',
	];
	$args = wp_parse_args( $args, $defaults );

	$range = pf_get_date_range_for_posts( $posts, [
		'gmt'           => $args['gmt'],
		'monthyear_fmt' => $args['monthyear_fmt'],
		'collapse_same' => $args['collapse_same'],
		'separator'     => $args['separator'],
	] );

	if ( empty( $range['label'] ) ) {
		return;
	}

	$prefix_html = $args['prefix'] !== '' ? '<span class="visually-hidden">' . esc_html( $args['prefix'] ) . ' </span>' : '';

	// Assemble icon placement
	$content = esc_html( $range['label'] );
	if ( $args['icon_html'] !== '' ) {
		if ( 'after' === $args['icon_position'] ) {
			$content = $content . ' ' . $args['icon_html'];
		} else {
			$content = $args['icon_html'] . ' ' . $content;
		}
	}

	echo $args['before']
		. '<p class="' . esc_attr( $args['class'] ) . '">'
		. $prefix_html
		. $content
		. '</p>'
		. $args['after'];
}

