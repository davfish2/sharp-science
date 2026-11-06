<?php
defined( 'ABSPATH' ) || exit;
get_header();
global $wp_query;
?>
<section class="archive"><div class="wrap">
	<header class="page__head"><p class="eyebrow"><span><?php echo esc_html( sprintf( '%02d', $wp_query->found_posts ) ); ?></span>Results</p><h1><?php echo esc_html( get_search_query() ); ?></h1></header>
	<?php if ( have_posts() ) : ?>
		<div class="grid">
			<?php while ( have_posts() ) : the_post(); $p = wc_get_product( get_the_ID() ); if ( $p ) { get_template_part( 'template-parts/product-card', null, array( 'product' => $p ) ); } endwhile; ?>
		</div>
	<?php else : ?>
		<p class="lead">No matching products. Try a catalogue number such as SS-V10.</p>
		<p><a class="btn" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">Shop the range</a></p>
	<?php endif; ?>
</div></section>
<?php
get_footer();
