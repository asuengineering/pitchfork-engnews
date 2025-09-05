<?php
/**
 * De-dupe posts across multiple Query Loop blocks on specific pages.
 * Enable per page with ACF checkbox: 'page_prevent_duplicate_posts' (true/1 to enable).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', function () {
	// Front-end only.
	if ( is_admin() ) return;

	// Shared per-request store.
	$GLOBALS['engnews_seen_post_ids'] = [];

	/**
	 * Helper: should we apply de-dupe on this request?
	 */
	function engnews_dedupe_enabled(): bool {
		// Only pages.
		if ( ! is_page() ) return false;

		$page_id = get_queried_object_id();
		if ( ! $page_id ) return false;

		// Read the ACF boolean (ACF stores '1' for true by default).
		$val = get_post_meta( $page_id, 'page_prevent_duplicate_posts', true );
		return (bool) ( $val === '1' || $val === 1 || $val === true );
	}

	/**
	 * Inject post__not_in into Query Loop args before each loop runs.
	 * Use the modern filter where available, and fall back for older core.
	 */
	$inject_exclusions = function( $args ) {
		if ( ! engnews_dedupe_enabled() ) return $args;

		$seen = $GLOBALS['engnews_seen_post_ids'] ?? [];
		if ( ! empty( $seen ) ) {
			$args['post__not_in'] = array_values( array_unique( array_merge(
				$args['post__not_in'] ?? [],
				array_map( 'intval', $seen )
			) ) );
		}
		return $args;
	};

	// WP 6.2+: fired when building the query for core/query.
	add_filter( 'build_query_vars_from_query_block', function( $args, $block ) use ( $inject_exclusions ) {
		return $inject_exclusions( $args );
	}, 10, 2 );

	// Fallback for older cores.
	add_filter( 'query_loop_block_query_vars', function( $args, $block, $page ) use ( $inject_exclusions ) {
		return $inject_exclusions( $args );
	}, 10, 3 );

	/**
	 * As each query runs, record the IDs it fetched so later loops can exclude them.
	 * We do this in 'the_posts' which receives the full array of posts returned for that query.
	 */
	add_filter( 'the_posts', function( $posts, $query ) {
		if ( ! engnews_dedupe_enabled() ) return $posts;

		// Only collect for front-end queries within the main page request.
		// (Avoid admin, feed, search REST etc. as extra safety.)
		if ( is_feed() || is_search() ) return $posts;

		if ( ! empty( $posts ) ) {
			$ids =& $GLOBALS['engnews_seen_post_ids'];
			foreach ( $posts as $p ) {
				if ( isset( $p->ID ) ) {
					$ids[] = (int) $p->ID;
				}
			}
			$ids = array_values( array_unique( $ids ) );
		}
		return $posts;
	}, 10, 2 );
});
