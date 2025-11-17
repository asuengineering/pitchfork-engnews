<?php
/**
 * Engineering News — De-dupe posts across multiple Query Loop blocks on a page.
 * Controlled by ACF true/false field on pages: 'page_prevent_duplicate_posts'.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Toggle runtime debug output for this module.
 * Leave false in production unless actively troubleshooting.
 */
if ( ! defined( 'ENGNEWS_DEDUPE_DEBUG' ) ) {
	define( 'ENGNEWS_DEDUPE_DEBUG', false );
}

/**
 * Debug helper that prefers Query Monitor (qm/debug) and falls back to error_log.
 *
 * Usage: engnews_dbg( 'message' );
 *        engnews_dbg( [ 'key' => 'value', 'ids' => [1,2,3] ] );
 */
if ( ! function_exists( 'engnews_dbg' ) ) {
	function engnews_dbg( $msg ) {
		if ( ! ENGNEWS_DEDUPE_DEBUG ) {
			return;
		}

		// Normalize to a string (pretty JSON for arrays/objects).
		$out = is_scalar( $msg ) ? (string) $msg : wp_json_encode( $msg, JSON_UNESCAPED_SLASHES );

		// If Query Monitor is available, use it.
		if ( function_exists( 'do_action' ) && has_action( 'qm/debug' ) ) {
			// Prefix to make it easy to filter within QM.
			do_action( 'qm/debug', '[engnews-dedupe] ' . $out );
			return;
		}

		// Fallback to PHP error_log (honors WP_DEBUG_LOG if enabled).
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( '[engnews-dedupe] ' . $out );
		}
	}
}


/** ------------------------
 *  Core helpers
 * -------------------------*/
if ( ! function_exists( 'engnews_get_seen_ids' ) ) {
	function &engnews_get_seen_ids() : array {
		if ( ! isset( $GLOBALS['engnews_seen_post_ids'] ) || ! is_array( $GLOBALS['engnews_seen_post_ids'] ) ) {
			$GLOBALS['engnews_seen_post_ids'] = [];
		}
		return $GLOBALS['engnews_seen_post_ids'];
	}
}

if ( ! function_exists( 'engnews_dedupe_enabled' ) ) {
	function engnews_dedupe_enabled() : bool {
		// Front-end only.
		if ( is_admin() ) return false;
		if ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) return false;
		if ( function_exists( 'wp_is_json_request' ) && wp_is_json_request() ) return false;

		// Only real page renders.
		if ( ! is_singular( 'page' ) ) return false;

		$page_id = get_queried_object_id();
		if ( ! $page_id ) return false;

		// Read ACF true/false (1/0) or raw post meta.
		$val = function_exists( 'get_field' )
			? get_field( 'page_prevent_duplicate_posts', $page_id )
			: get_post_meta( $page_id, 'page_prevent_duplicate_posts', true );

		return (bool) ( $val === '1' || $val === 1 || $val === true );
	}
}

/** ------------------------
 *  Inject exclusions into Query Loop args
 * -------------------------*/
if ( ! function_exists( 'engnews_inject_exclusions' ) ) {
	function engnews_inject_exclusions( array $args ) : array {
		if ( ! engnews_dedupe_enabled() ) return $args;

		$seen = engnews_get_seen_ids();
		if ( empty( $seen ) ) {
			engnews_dbg( [ 'inject_exclusions' => 'none (no seen ids)' ] );
			return $args;
		}

		// Calculate effective offset:
		// - honor explicit 'offset' param
		// - account for pagination: (paged - 1) * posts_per_page
		$explicit_offset = isset( $args['offset'] ) ? max(0, intval( $args['offset'] ) ) : 0;

		$paged = isset( $args['paged'] ) ? max(1, intval( $args['paged'] ) ) : 1;
		$ppp   = isset( $args['posts_per_page'] ) ? intval( $args['posts_per_page'] ) : get_option( 'posts_per_page' );

		$pagination_offset = 0;
		if ( $paged > 1 && $ppp > 0 ) {
			$pagination_offset = ( $paged - 1 ) * $ppp;
		}

		$effective_offset = $explicit_offset + $pagination_offset;
		if ( $effective_offset < 0 ) $effective_offset = 0;

		// If effective_offset is larger than number of seen IDs, we will exclude nothing.
		if ( $effective_offset >= count( $seen ) ) {
			$exclude_candidates = [];
		} else {
			// Slice the seen IDs so we DO NOT exclude the first $effective_offset items,
			// assuming the editor intentionally skipped them via offset/pagination.
			$exclude_candidates = array_slice( $seen, $effective_offset );
		}

		// Merge with existing post__not_in if present.
		$existing_not_in = isset( $args['post__not_in'] ) ? (array) $args['post__not_in'] : [];

		$args['post__not_in'] = array_values( array_unique( array_merge(
			array_map( 'intval', $existing_not_in ),
			array_map( 'intval', $exclude_candidates )
		) ) );

		engnews_dbg( [
			'inject_exclusions'    => [
				'label' => $GLOBALS['engnews_collecting_label'] ?: 'query:unknown',
				'seen_total' => count( $seen ),
				'effective_offset' => $effective_offset,
				'exclude_count' => count( $exclude_candidates ),
				'exclude_ids' => $args['post__not_in'],
			],
		] );

		return $args;
	}
}


add_filter( 'build_query_vars_from_query_block', function( $args /*, $block */ ) {
	return engnews_inject_exclusions( (array) $args );
}, 10, 2 );

add_filter( 'query_loop_block_query_vars', function( $args /*, $block, $page */ ) {
	return engnews_inject_exclusions( (array) $args );
}, 10, 3 );

/** ------------------------
 *  Reliable collection of IDs during block render
 *  - Mark when a core/query block is rendering (pre_render_block / render_block)
 *  - While marked, collect IDs on 'the_post'
 * -------------------------*/
$GLOBALS['engnews_collecting_query'] = false;
$GLOBALS['engnews_collecting_label'] = '';

/**
 * Small helper to label a Query Loop for debugging:
 * prefers block anchor (#advanced > HTML anchor), then className, then queryId.
 */
if ( ! function_exists( 'engnews_label_query_block' ) ) {
	function engnews_label_query_block( array $block ) : string {
		$a = $block['attrs'] ?? [];
		if ( ! empty( $a['anchor'] ) )    return 'anchor:#' . sanitize_title( $a['anchor'] );
		if ( ! empty( $a['className'] ) ) return 'class:.' . sanitize_html_class( $a['className'] );
		if ( isset( $a['queryId'] ) )     return 'queryId:' . intval( $a['queryId'] );
		return 'query:unknown';
	}
}

add_filter( 'pre_render_block', function( $pre_render, $block ) {
	if ( ! engnews_dedupe_enabled() ) return $pre_render;

	if ( isset( $block['blockName'] ) && $block['blockName'] === 'core/query' ) {
		$GLOBALS['engnews_collecting_query'] = true;
		$GLOBALS['engnews_collecting_label'] = engnews_label_query_block( $block );
		engnews_dbg( 'BEGIN ' . $GLOBALS['engnews_collecting_label'] );
	}
	return $pre_render; // do not short-circuit render
}, 10, 2 );

add_filter( 'render_block', function( $content, $block ) {
	if ( ! engnews_dedupe_enabled() ) return $content;

	if ( isset( $block['blockName'] ) && $block['blockName'] === 'core/query' ) {
		engnews_dbg( 'END   ' . ( $GLOBALS['engnews_collecting_label'] ?: 'query:unknown' ) );
		$GLOBALS['engnews_collecting_query'] = false;
		$GLOBALS['engnews_collecting_label'] = '';
	}
	return $content;
}, 10, 2 );

/**
 * Collect IDs as each post is set up during a Query Loop render.
 */
add_action( 'the_post', function( $post ) {
	if ( ! engnews_dedupe_enabled() ) return;
	if ( empty( $GLOBALS['engnews_collecting_query'] ) ) return;

	$ids =& engnews_get_seen_ids();
	$ids[] = (int) $post->ID;
	$ids   = array_values( array_unique( $ids ) );

	engnews_dbg( [ 'collect_in' => $GLOBALS['engnews_collecting_label'], 'added' => $post->ID, 'seen_now' => $ids ] );
});
