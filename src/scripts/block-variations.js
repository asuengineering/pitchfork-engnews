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

});
