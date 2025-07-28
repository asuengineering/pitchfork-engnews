<?php
/**
 * External News - Filters
 *
 * - Filters for fancy interactivity API search/filter experience.
 *
 * @package pitchfork_engnews
 */

/**
 * Set initial get_field declarations.
 */

/**
 * Set block classes
 * - Get additional classes from the 'advanced' field in the editor.
 * - Get alignment setting from toolbar if enabled in theme.json, or set default value
 * - Include any default classs for the block in the intial array.
 */

$block_attr = array( 'uds-template');
if ( ! empty( $block['className'] ) ) {
	$block_attr[] = $block['className'];
}
// if ( ! empty( $block['align'] ) ) {
// 	$block_attr[] = $block['align'];
// }

/**
 * Additional margin/padding settings
 * Returns a string for inclusion with style=""
 */
$spacing = pitchfork_blocks_acf_calculate_spacing( $block );

/**
 * Include block.json support for HTML anchor.
 */
$anchor = '';
if ( ! empty( $block['anchor'] ) ) {
	$anchor = 'id="' . $block['anchor'] . '"';
}

/**
 * Block logic here.
 */

/**
 * Create the outer wrapper for the block output.
 */
$attr  = implode( ' ', $block_attr );
$output = '<div ' . $anchor . ' class="' . $attr . '" style="' . $spacing . '">';


/**
 * Close the block, echo the output.
 */
$output .= '</div>';
// echo $output;

echo '
<div data-wp-interactive="externalFilters">
  <label>
    Search:
    <input
      type="text"
      data-wp-bind--value="state.search"
      data-wp-on--input="externalFilters::setSearch"
    />
  </label>

  <label>
    Publication:
    <select
      data-wp-bind--value="state.publication"
      data-wp-on--change="externalFilters::setPublication"
    >
      <option value="">All</option>
      <option value="pub-a">Publication A</option>
      <option value="pub-b">Publication B</option>
    </select>
  </label>

  <label>
    ASU Person:
    <select
      data-wp-bind--value="state.asuPerson"
      data-wp-on--change="externalFilters::setAsuPerson"
    >
      <option value="">All</option>
      <option value="john-doe">John Doe</option>
      <option value="jane-smith">Jane Smith</option>
    </select>
  </label>

  <button type="button" data-wp-on--click="externalFilters::applyFilters">
    Apply Filters
  </button>
</div>
';
