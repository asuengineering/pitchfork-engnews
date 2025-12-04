<?php
/**
 * News Header
 * - A single block providing the markup for all of the elements in the header of a standard news post.
 *
 * @package pitchfork_engnews
 */

$spacing = pitchfork_blocks_acf_calculate_spacing( $block );
$post = get_post();

/**
 * Retrieve additional classes from the 'advanced' field in the editor for inline styles.
 */
$block_classes = array( 'post-header' );
if ( ! empty( $block['className'] ) ) {
	$block_classes[] = $block['className'];
}

/**
 * Get the category list, create markup for tag-categories
 * TODO: Currently not displayed, may need to be output again elsewhere.
 */
$categories = get_the_category();
$categorylist = '';
if ( ! empty( $categories ) ) {
	$categorylist = '<ul class="tag-categories">';
	foreach( $categories as $category ) {
		$categorylist .= '<li><a class="category" href="' . esc_url( get_category_link( $category->term_id ) ) . '">' . esc_html( $category->name ) . '</a></li>';
	}
	$categorylist .= '</ul>';
}

/**
 * Get the school list.
 */
$schoollist = get_the_term_list($post->ID, 'school_unit', '' , ', ' , '')

/**
 * Echo the output directly
 *
 * Edge cases:
 * Unset variables - category, school, excerpt, post title
 *
 */

?>
<div class="<?php echo implode( ' ', $block_classes );?>" style="<?php echo $spacing; ?>">

	<?php the_title( '<h1 class="post-title"><span class="highlight-gold">', '</span></h1>' ); ?>
	<h2 class="excerpt"><?php echo get_the_excerpt(); ?></h2>

	<div class="attribution">
		<p class="entry-byline">by <?php pitchfork_posted_by(); ?></p>
		<p class="entry-date"><span class="fa-regular fa-calendar-lines"></span><?php pitchfork_posted_originally(); ?></p>
	</div>
	<div class="tags">
		<span class="fa-light fa-school"></span>
		<?php echo $schoollist; ?>
	</div>

</div>

