<?php
/**
 * Declare custom post types for the theme.
 * Yes, this is "supposed" to be in a plugin. ¯\_(ツ)_/¯
 *
 * @package pitchfork-engnews
 */

add_theme_support('post-thumbnails', array('external_news', 'outstand_grad', 'faculty'));

// ===============================================
// Register "external_news" CPT. Used for In The News items. external_news
// ===============================================
function pf_engnews_inthenews_cpt() {

	$labels = array(
		'name'                  => _x( 'News Items', 'Post Type General Name', 'text_domain' ),
		'singular_name'         => _x( 'News Item', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'             => __( 'News Items', 'text_domain' ),
		'name_admin_bar'        => __( 'News Item', 'text_domain' ),
		'archives'              => __( 'News Archives', 'text_domain' ),
		'attributes'            => __( 'News Item Attributes', 'text_domain' ),
		'parent_item_colon'     => __( 'Parent Item:', 'text_domain' ),
		'all_items'             => __( 'All Items', 'text_domain' ),
		'add_new_item'          => __( 'Add New Item', 'text_domain' ),
		'add_new'               => __( 'Add New', 'text_domain' ),
		'new_item'              => __( 'New Item', 'text_domain' ),
		'edit_item'             => __( 'Edit Item', 'text_domain' ),
		'update_item'           => __( 'Update Item', 'text_domain' ),
		'view_item'             => __( 'View Item', 'text_domain' ),
		'view_items'            => __( 'View Items', 'text_domain' ),
		'search_items'          => __( 'Search Item', 'text_domain' ),
		'not_found'             => __( 'Not found', 'text_domain' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
		'featured_image'        => __( 'Featured Image', 'text_domain' ),
		'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
		'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
		'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
		'insert_into_item'      => __( 'Insert into item', 'text_domain' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'text_domain' ),
		'items_list'            => __( 'Items list', 'text_domain' ),
		'items_list_navigation' => __( 'Items list navigation', 'text_domain' ),
		'filter_items_list'     => __( 'Filter items list', 'text_domain' ),
	);
	$rewrite = array(
		'slug'                  => 'external',
		'with_front'            => true,
		'pages'                 => true,
		'feeds'                 => true,
	);
	$args = array(
		'label'                 => __( 'News Item', 'text_domain' ),
		'description'           => __( 'External News items from the Full Circle Blog', 'text_domain' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor', 'thumbnail', 'revisions' ),
		'taxonomies'            => array( 'external_tag', 'publications' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 6,
		'menu_icon'             => 'dashicons-megaphone',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => false,
		'exclude_from_search'   => false,
		'rewrite'               => $rewrite,
		'capability_type'       => 'post',
		'show_in_rest'          => true,
		'publicly_queryable'  	=> true,
	);
	register_post_type( 'external_news', $args );

}
add_action( 'init', 'pf_engnews_inthenews_cpt', 0 );

// ===============================================
// Additional Taxonomy for Story Publication Source, publication
// ===============================================
function inthenews_publication() {

	$labels = array(
		'name'                       => _x( 'Publication Names', 'Taxonomy General Name', 'text_domain' ),
		'singular_name'              => _x( 'Publication', 'Taxonomy Singular Name', 'text_domain' ),
		'menu_name'                  => __( 'Publications', 'text_domain' ),
		'all_items'                  => __( 'All Publications', 'text_domain' ),
		'parent_item'                => __( 'Parent Publication', 'text_domain' ),
		'parent_item_colon'          => __( 'Parent Publication:', 'text_domain' ),
		'new_item_name'              => __( 'New Publication', 'text_domain' ),
		'add_new_item'               => __( 'Add New Publication', 'text_domain' ),
		'edit_item'                  => __( 'Edit Publication', 'text_domain' ),
		'update_item'                => __( 'Update Publication', 'text_domain' ),
		'view_item'                  => __( 'View Publication', 'text_domain' ),
		'separate_items_with_commas' => __( 'Separate items with commas', 'text_domain' ),
		'add_or_remove_items'        => __( 'Add or remove publication', 'text_domain' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'text_domain' ),
		'popular_items'              => __( 'Popular Publications', 'text_domain' ),
		'search_items'               => __( 'Search Publications', 'text_domain' ),
		'not_found'                  => __( 'Not Found', 'text_domain' ),
		'no_terms'                   => __( 'No publications', 'text_domain' ),
		'items_list'                 => __( 'Publications list', 'text_domain' ),
		'items_list_navigation'      => __( 'Publications list navigation', 'text_domain' ),
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => false,
		'show_tagcloud'              => false,
		'show_in_rest'               => false,
	);
	register_taxonomy( 'publication', array( 'external_news' ), $args );

}
add_action( 'init', 'inthenews_publication', 0 );


// ===============================================
// Register "outstand_grad" CPT.
// Used for Outstanding Grad items. outstand_grad
// ===============================================
if ( ! function_exists('pf_engnews_inthenews_grads_cpt') ) {

	// Register Custom Post Type
	function pf_engnews_inthenews_grads_cpt() {

		$labels = array(
			'name'                  => _x( 'Graduates', 'Post Type General Name', 'text_domain' ),
			'singular_name'         => _x( 'Graduate', 'Post Type Singular Name', 'text_domain' ),
			'menu_name'             => __( 'Outstanding Grads', 'text_domain' ),
			'name_admin_bar'        => __( 'Grads', 'text_domain' ),
			'archives'              => __( 'Grad Archives', 'text_domain' ),
			'attributes'            => __( 'Grad Attributes', 'text_domain' ),
			'parent_item_colon'     => __( 'Parent Grad:', 'text_domain' ),
			'all_items'             => __( 'All Grads', 'text_domain' ),
			'add_new_item'          => __( 'Add New Grad', 'text_domain' ),
			'add_new'               => __( 'Add New', 'text_domain' ),
			'new_item'              => __( 'New Grad', 'text_domain' ),
			'edit_item'             => __( 'Edit Grad', 'text_domain' ),
			'update_item'           => __( 'Update Grad', 'text_domain' ),
			'view_item'             => __( 'View Grad', 'text_domain' ),
			'view_items'            => __( 'View Grads', 'text_domain' ),
			'search_items'          => __( 'Search Grad', 'text_domain' ),
			'not_found'             => __( 'Not found', 'text_domain' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
			'featured_image'        => __( 'Featured Image', 'text_domain' ),
			'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
			'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
			'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
			'insert_into_item'      => __( 'Insert into Grad', 'text_domain' ),
			'uploaded_to_this_item' => __( 'Uploaded to this grad', 'text_domain' ),
			'items_list'            => __( 'Grads list', 'text_domain' ),
			'items_list_navigation' => __( 'Grads list navigation', 'text_domain' ),
			'filter_items_list'     => __( 'Filter grads list', 'text_domain' ),
		);
		$args = array(
			'label'                 => __( 'Graduate', 'text_domain' ),
			'description'           => __( 'FSE Outstanding graduates', 'text_domain' ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'editor', 'thumbnail', 'revisions', 'excerpt', 'page-attributes' ),
			'hierarchical'          => true,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 20,
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'page',
			'show_in_rest'          => true,
			'rewrite' 				=> array( 'slug' => 'graduate' ),
			'has_archive'           => false,
		);
		register_post_type( 'outstand_grad', $args );

	}
	add_action( 'init', 'pf_engnews_inthenews_grads_cpt', 0 );

}

// ===============================================
// Taxonomy for outstand_grad: graduate_type.
// ===============================================
function pf_engnews_inthenews_register_graduate_type_taxonomy() {

	$labels = array(
		'name'                       => _x( 'Award Types', 'Taxonomy General Name', 'text_domain' ),
		'singular_name'              => _x( 'Award Type', 'Taxonomy Singular Name', 'text_domain' ),
		'menu_name'                  => __( 'Award Type', 'text_domain' ),
		'all_items'                  => __( 'Award Types', 'text_domain' ),
		'parent_item'                => __( 'Parent Type', 'text_domain' ),
		'parent_item_colon'          => __( 'Parent Type:', 'text_domain' ),
		'new_item_name'              => __( 'New Type', 'text_domain' ),
		'add_new_item'               => __( 'Add New Type', 'text_domain' ),
		'edit_item'                  => __( 'Edit Type', 'text_domain' ),
		'upType_item'                => __( 'UpType Type', 'text_domain' ),
		'view_item'                  => __( 'View Type', 'text_domain' ),
		'separate_items_with_commas' => __( 'Separate types with commas', 'text_domain' ),
		'add_or_remove_items'        => __( 'Add or remove types', 'text_domain' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'text_domain' ),
		'popular_items'              => __( 'Popular Types', 'text_domain' ),
		'search_items'               => __( 'Search Types', 'text_domain' ),
		'not_found'                  => __( 'Not Found', 'text_domain' ),
		'no_terms'                   => __( 'No types', 'text_domain' ),
		'items_list'                 => __( 'Types list', 'text_domain' ),
		'items_list_navigation'      => __( 'Types list navigation', 'text_domain' ),
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => false,
		'show_in_rest'               => true,
		'rewrite' 					 => array( 'slug' => 'graduate-award' ),
	);
	register_taxonomy( 'graduate_type', array( 'outstand_grad' ), $args );

}
add_action( 'init', 'pf_engnews_inthenews_register_graduate_type_taxonomy', 0 );

 // ===============================================
// Register "faculty" CPT. Used for new faculty profiles
// ===============================================

// Register Custom Post Type
function pf_engnews_faculty_register_cpt() {

	$labels = array(
		'name'                  => 'Faculty',
		'singular_name'         => 'Faculty',
		'menu_name'             => 'New Faculty',
		'name_admin_bar'        => 'Faculty',
		'archives'              => 'Faculty Archives',
		'attributes'            => 'Faculty Attributes',
		'parent_item_colon'     => 'Parent Faculty',
		'all_items'             => 'All Faculty',
		'add_new_item'          => 'Add New Faculty',
		'add_new'               => 'Add New',
		'new_item'              => 'New Faculty',
		'edit_item'             => 'Edit Faculty',
		'update_item'           => 'Update Faculty',
		'view_item'             => 'View Faculty',
		'view_items'            => 'View Faculty',
		'search_items'          => 'Search Faculty',
		'not_found'             => 'Not found',
		'not_found_in_trash'    => 'Not found in Trash',
		'featured_image'        => 'Featured Image',
		'set_featured_image'    => 'Set featured image',
		'remove_featured_image' => 'Remove featured image',
		'use_featured_image'    => 'Use as featured image',
		'insert_into_item'      => 'Insert into faculty',
		'uploaded_to_this_item' => 'Uploaded to this faculty',
		'items_list'            => 'Faculty list',
		'items_list_navigation' => 'Faculty list navigation',
		'filter_items_list'     => 'Filter faculty list',
	);
	$args = array(
		'label'                 => 'Faculty',
		'description'           => 'New Faculty',
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor', 'thumbnail', 'revisions' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 24,
		'menu_icon'             => 'dashicons-id',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => false,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'page',
		'rewrite' 				=> array( 'slug' => 'welcome' ),
		'show_in_rest'          => false,  // Enable for Gutenberg support.
	);
	register_post_type( 'faculty', $args );

}
add_action( 'init', 'pf_engnews_faculty_register_cpt', 0 );


/**
 * Taxonomy for academic term.
 * - Connected to all four post types within engineering news
 * - No taxonomy archive pages, no taxonomy term pages.
 */
function pf_engnews_register_academic_year_taxonomy() {

	$labels = array(
		'name'                       => _x( 'Academic Terms', 'Taxonomy General Name', 'text_domain' ),
		'singular_name'              => _x( 'Academic Term', 'Taxonomy Singular Name', 'text_domain' ),
		'menu_name'                  => __( 'Academic Term', 'text_domain' ),
		'all_items'                  => __( 'Academic Terms', 'text_domain' ),
		'parent_item'                => __( 'Parent Term', 'text_domain' ),
		'parent_item_colon'          => __( 'Parent Term:', 'text_domain' ),
		'new_item_name'              => __( 'New Term', 'text_domain' ),
		'add_new_item'               => __( 'Add New Term', 'text_domain' ),
		'edit_item'                  => __( 'Edit Term', 'text_domain' ),
		'upyear_item'                => __( 'Upyear Term', 'text_domain' ),
		'view_item'                  => __( 'View Term', 'text_domain' ),
		'separate_items_with_commas' => __( 'Separate terms with commas', 'text_domain' ),
		'add_or_remove_items'        => __( 'Add or remove terms', 'text_domain' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'text_domain' ),
		'popular_items'              => __( 'Popular Terms', 'text_domain' ),
		'search_items'               => __( 'Search Terms', 'text_domain' ),
		'not_found'                  => __( 'Not Found', 'text_domain' ),
		'no_terms'                   => __( 'No terms', 'text_domain' ),
		'items_list'                 => __( 'Terms list', 'text_domain' ),
		'items_list_navigation'      => __( 'Terms list navigation', 'text_domain' ),
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => false,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => false,
		'show_in_rest'               => true,
	);
	register_taxonomy( 'academic_year', array( 'faculty', 'post', 'external_news', 'outstand_grad' ), $args );

}
add_action( 'init', 'pf_engnews_register_academic_year_taxonomy', 0 );

// ===============================================
// Taxonomy "School or Unit"
// Connected with posts and new faculty.
// ===============================================
function pf_engnews_register_school_unit_taxonomy() {

	$labels = array(
		'name'                       => 'Schools or Units',
		'singular_name'              => 'School or Unit',
		'menu_name'                  => 'School or Unit',
		'all_items'                  => 'All Units',
		'parent_item'                => 'Parent Unit',
		'parent_item_colon'          => 'Parent Unit:',
		'new_item_name'              => 'New Unit Name',
		'add_new_item'               => 'Add New Unit',
		'edit_item'                  => 'Edit Unit',
		'update_item'                => 'Update Unit',
		'view_item'                  => 'View Unit',
		'separate_items_with_commas' => 'Separate units with commas',
		'add_or_remove_items'        => 'Add or remove units',
		'choose_from_most_used'      => 'Choose from the most used',
		'popular_items'              => 'Popular Units',
		'search_items'               => 'Search Units',
		'not_found'                  => 'Not Found',
		'no_terms'                   => 'No units',
		'items_list'                 => 'Units list',
		'items_list_navigation'      => 'Units list navigation',
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => false,
		'show_in_rest'               => true,
	);
	register_taxonomy( 'school_unit', array( 'faculty', 'post' ), $args );

}
add_action( 'init', 'pf_engnews_register_school_unit_taxonomy', 0 );

/**
 * Taxonomy: Person, modeled after faculty memtors within forge.engineering
 * Connections: posts, external_news
 *
 */
function pf_engnews_asu_person_taxonomy() {

	$labels = array(
		'name'                       => _x( 'Faculty/Staff', 'Taxonomy General Name', 'text_domain' ),
		'singular_name'              => _x( 'Faculty/Staff', 'Taxonomy Singular Name', 'text_domain' ),
		'menu_name'                  => __( 'Faculty/Staff', 'text_domain' ),
		'all_items'                  => __( 'All Faculty/Staff', 'text_domain' ),
		'parent_item'                => __( 'Parent Faculty/Staff', 'text_domain' ),
		'parent_item_colon'          => __( 'Parent Faculty/Staff:', 'text_domain' ),
		'new_item_name'              => __( 'New Faculty/Staff Name', 'text_domain' ),
		'add_new_item'               => __( 'Add New Faculty/Staff', 'text_domain' ),
		'edit_item'                  => __( 'Edit Faculty/Staff', 'text_domain' ),
		'update_item'                => __( 'Update Faculty/Staff', 'text_domain' ),
		'view_item'                  => __( 'View Faculty/Staff', 'text_domain' ),
		'separate_items_with_commas' => __( 'Separate people with commas', 'text_domain' ),
		'add_or_remove_items'        => __( 'Add or remove people', 'text_domain' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'text_domain' ),
		'popular_items'              => __( 'Popular Faculty/Staff', 'text_domain' ),
		'search_items'               => __( 'Search Faculty/Staff', 'text_domain' ),
		'not_found'                  => __( 'Not Found', 'text_domain' ),
		'no_terms'                   => __( 'No people', 'text_domain' ),
		'items_list'                 => __( 'Faculty/Staff list', 'text_domain' ),
		'items_list_navigation'      => __( 'Faculty/Staff list navigation', 'text_domain' ),
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => false,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => false,
		'show_tagcloud'              => false,
		'show_in_rest'               => true,
	);
	register_taxonomy( 'asu_person', array( 'post', 'external_news' ), $args );

}
add_action( 'init', 'pf_engnews_asu_person_taxonomy', 0 );


/**
 * Taxonomy: Topics
 * Adds "trending topic" tag organization to posts + external_news
 */
function pf_asunews_tax_topics() {

	$labels = array(
		'name'                       => _x( 'Topics', 'Taxonomy General Name', 'pitchfork-engnews' ),
		'singular_name'              => _x( 'Topic', 'Taxonomy Singular Name', 'pitchfork-engnews' ),
		'menu_name'                  => __( 'Topics', 'pitchfork-engnews' ),
		'all_items'                  => __( 'All Topics', 'pitchfork-engnews' ),
		'parent_item'                => __( 'Parent Topic', 'pitchfork-engnews' ),
		'parent_item_colon'          => __( 'Parent Topic:', 'pitchfork-engnews' ),
		'new_item_name'              => __( 'New Topic Name', 'pitchfork-engnews' ),
		'add_new_item'               => __( 'Add New Topic', 'pitchfork-engnews' ),
		'edit_item'                  => __( 'Edit Topic', 'pitchfork-engnews' ),
		'update_item'                => __( 'Update Topic', 'pitchfork-engnews' ),
		'view_item'                  => __( 'View Topic', 'pitchfork-engnews' ),
		'separate_items_with_commas' => __( 'Separate topics with commas', 'pitchfork-engnews' ),
		'add_or_remove_items'        => __( 'Add or remove topics', 'pitchfork-engnews' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'pitchfork-engnews' ),
		'popular_items'              => __( 'Popular Topics', 'pitchfork-engnews' ),
		'search_items'               => __( 'Search Topics', 'pitchfork-engnews' ),
		'not_found'                  => __( 'Not Found', 'pitchfork-engnews' ),
		'no_terms'                   => __( 'No items', 'pitchfork-engnews' ),
		'items_list'                 => __( 'Topics list', 'pitchfork-engnews' ),
		'items_list_navigation'      => __( 'Topics list navigation', 'pitchfork-engnews' ),
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => false,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => false,
		'show_in_rest'               => true,
	);
	register_taxonomy( 'topic', array( 'post', 'external_news' ), $args );

}
add_action( 'init', 'pf_asunews_tax_topics', 0 );
