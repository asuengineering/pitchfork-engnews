<?php
/**
 * Additional functions for Advanced Custom Fields.
 *
 * Contents:
 *   - Load path for ACF groups from the parent.
 *   - Register custom category for new blocks
 *   - Register custom blocks for the theme.
 *
 *
 * @package pitchfork-engnews
 */

/**
 * Add additional loading point for the parent theme's ACF groups.
 *
 * @return $paths
 */
function pitchfork_load_parent_theme_field_groups( $paths ) {
	$path = get_template_directory() . '/acf-json';
	$paths[] = $path;
	return $paths;
}
add_filter( 'acf/settings/load_json', 'pitchfork_load_parent_theme_field_groups' );

/**
 * Create save point for the child theme's ACF groups.
 *
 * @return $path
 */
function pitchfork_child_theme_field_groups( $path ) {
	$path = get_stylesheet_directory() . '/acf-json';
	return $path;
}
add_filter( 'acf/settings/save_json', 'pitchfork_child_theme_field_groups' );


/**
 * Register a custom block category for our blocks.
 * @param array                   $categories The existing block categories.
 * @param WP_Block_Editor_Context $editor_context Editor context.
*/
function pitchfork_engnews_custom_category( $categories, $editor_context ) {
	return array_merge(
		$categories,
		array(
			array(
				'slug'  => 'pitchfork_engnews',
				'title' => __( 'Engineering News', 'pitchfork-engnews' ),
			),
		)
	);
}
add_filter( 'block_categories_all', 'pitchfork_engnews_custom_category', 10, 2 );

/**
 * Note: Blocks appear in the block picker IN THE ORDER THEY ARE LISTED HERE.
 * When adding a new block, please make sure to insert it an alphabetical order.
 */

function pitchfork_engnews_acf_blocks_init() {

// Icons kept in a separate file.
require_once get_stylesheet_directory() . '/acf-block-templates/icons.php';

	// News article grid
	register_block_type(
		get_stylesheet_directory() . '/acf-block-templates/news-article-grid',
		array(
			'icon'     => $block_icon->users_rectangle,
			'category' => 'pitchfork_engnews',
		)
	);

	// News author box
	register_block_type(
		get_stylesheet_directory() . '/acf-block-templates/news-authorbox',
		array(
			'icon'     => $block_icon->users_rectangle,
			'category' => 'pitchfork_engnews',
		)
	);

	// News featured image
	register_block_type(
		get_stylesheet_directory() . '/acf-block-templates/news-featured-image',
		array(
			'icon'     => $block_icon->users_rectangle,
			'category' => 'pitchfork_engnews',
		)
	);

	// News header
	register_block_type(
		get_stylesheet_directory() . '/acf-block-templates/news-header',
		array(
			'icon'     => $block_icon->users_rectangle,
			'category' => 'pitchfork_engnews',
		)
	);

	// News related people
	register_block_type(
		get_stylesheet_directory() . '/acf-block-templates/news-related-people',
		array(
			'icon'     => $block_icon->users_rectangle,
			'category' => 'pitchfork_engnews',
		)
	);

	// News related terms
	register_block_type(
		get_stylesheet_directory() . '/acf-block-templates/news-related-terms',
		array(
			'icon'     => $block_icon->users_rectangle,
			'category' => 'pitchfork_engnews',
		)
	);

	// News quote
	register_block_type(
		get_stylesheet_directory() . '/acf-block-templates/news-quote',
		array(
			'icon'     => $block_icon->users_rectangle,
			'category' => 'pitchfork_engnews',
		)
	);

	// News highlight title
	register_block_type(
		get_stylesheet_directory() . '/acf-block-templates/news-highlight-title',
		array(
			'icon'     => $block_icon->users_rectangle,
			'category' => 'pitchfork_engnews',
		)
	);

	// Post group
	register_block_type(
		get_stylesheet_directory() . '/acf-block-templates/post-group',
		array(
			'icon'     => $block_icon->users_rectangle,
			'category' => 'pitchfork_engnews',
		)
	);

	// External News - Headline
	register_block_type(
		get_stylesheet_directory() . '/acf-block-templates/external-headline',
		array(
			'icon'     => $block_icon->users_rectangle,
			'category' => 'pitchfork_engnews',
		)
	);

	// External News - Source
	register_block_type(
		get_stylesheet_directory() . '/acf-block-templates/external-source',
		array(
			'icon'     => $block_icon->users_rectangle,
			'category' => 'pitchfork_engnews',
		)
	);

	// External News - Summary
	register_block_type(
		get_stylesheet_directory() . '/acf-block-templates/external-summary',
		array(
			'icon'     => $block_icon->users_rectangle,
			'category' => 'pitchfork_engnews',
		)
	);

	// External News - Image
	register_block_type(
		get_stylesheet_directory() . '/acf-block-templates/external-og-image',
		array(
			'icon'     => $block_icon->users_rectangle,
			'category' => 'pitchfork_engnews',
		)
	);

	// Outstanding Graduates
	register_block_type(
		get_stylesheet_directory() . '/acf-block-templates/outstanding-grads',
		array(
			'icon'     => $block_icon->users_rectangle,
			'category' => 'pitchfork_engnews',
		)
	);

	// New faculty
	register_block_type(
		get_stylesheet_directory() . '/acf-block-templates/new-faculty',
		array(
			'icon'     => $block_icon->users_rectangle,
			'category' => 'pitchfork_engnews',
		)
	);

	// Story thumb
	register_block_type(
		get_stylesheet_directory() . '/acf-block-templates/story-thumb',
		array(
			'icon'     => $block_icon->users_rectangle,
			'category' => 'pitchfork_engnews',
		)
	);

	// Grid links for taxonomy terms
	register_block_type(
		get_stylesheet_directory() . '/acf-block-templates/news-tax-grid-links',
		array(
			'icon'     => $block_icon->users_rectangle,
			'category' => 'pitchfork_engnews',
		)
	);
}
add_action( 'acf/init', 'pitchfork_engnews_acf_blocks_init' );

