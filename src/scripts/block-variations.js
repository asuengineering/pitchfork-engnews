/**
 * Starter JS file for child theme.
 * wp.domReady is a useful JS hook.
*/

wp.domReady(() => {

	/**
	* Register styles associated with core/group
	*/

	wp.blocks.registerBlockStyle(
		'core/group', [{
			name: 'default',
			label: 'Default',
			isDefault: true,
		}]
	);

	wp.blocks.registerBlockStyle(
		'core/group', [{
			name: 'news-aside',
			label: 'News Aside',
			isDefault: false,
		}]
	);

	/** Alt style for core/post-title */

	wp.blocks.registerBlockStyle(
		'core/post-title', [{
			name: 'default',
			label: 'Default',
			isDefault: true,
		}]
	);

	wp.blocks.registerBlockStyle(
		'core/post-title', [{
			name: 'alt-hover',
			label: 'Alt Hover',
			isDefault: false,
		}]
	);

	/** Post group w/reversed layout */

	wp.blocks.registerBlockStyle(
		'acf/post-group', [{
			name: 'default',
			label: 'Default',
			isDefault: true,
		}]
	);

	wp.blocks.registerBlockStyle(
		'acf/post-group', [{
			name: 'reversed',
			label: 'Reversed',
			isDefault: false,
		}]
	);

	/** Related People Block */

	wp.blocks.registerBlockStyle(
		'acf/news-related-people', [{
			name: 'default',
			label: 'Default',
			isDefault: true,
		}]
	);

	wp.blocks.registerBlockStyle(
		'acf/news-related-people', [{
			name: 'compact',
			label: 'Compact',
			isDefault: false,
		}]
	);

	wp.blocks.registerBlockStyle(
		'acf/news-related-people', [{
			name: 'icon-only',
			label: 'Icon only',
			isDefault: false,
		}]
	);


});
