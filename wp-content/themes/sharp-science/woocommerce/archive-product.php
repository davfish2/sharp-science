<?php
defined( 'ABSPATH' ) || exit;
get_header();
global $wp_query;
$current = is_product_category() ? get_queried_object() : null;
$band    = $current ? ( get_term_meta( $current->term_id, 'ss_band', true ) ?: '#9AA3A6' ) : 'var(--brass)';
?>
<section class="archive"><div class="wrap">
	<header class="page__head archive__head" style="--band:<?php echo esc_attr( $band ); ?>">
		<p class="eyebrow"><span><?php echo esc_html( sprintf( '%02d', $wp_query->found_posts ) ); ?></span><?php echo is_search() ? 'Results' : 'Products'; ?></p>
		<h1><?php echo esc_html( is_search() ? get_search_query() : ( $current ? html_entity_decode( $current->name ) : 'Shop' ) ); ?></h1>
	</header>
	<nav class="chips" aria-label="Categories">
		<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" <?php echo $current || is_search() ? '' : 'aria-current="page"'; ?>>All</a>
		<?php foreach ( ss_categories() as $c ) : ?>
			<a href="<?php echo esc_url( $c['url'] ); ?>" <?php echo $current && $current->slug === $c['slug'] ? 'aria-current="page"' : ''; ?>><?php echo esc_html( $c['name'] ); ?></a>
		<?php endforeach; ?>
	</nav>
	<?php wc_print_notices(); ?>
	<?php if ( have_posts() ) : ?>
		<div class="grid">
			<?php while ( have_posts() ) : the_post(); $p = wc_get_product( get_the_ID() ); if ( $p ) { get_template_part( 'template-parts/product-card', null, array( 'product' => $p, 'heading' => 'h2' ) ); } endwhile; ?>
		</div>
		<?php
		$links = paginate_links( array( 'type' => 'array', 'prev_text' => 'Previous', 'next_text' => 'Next' ) );
		if ( $links ) {
			echo '<nav class="pager" aria-label="Pages">' . wp_kses_post( implode( '', $links ) ) . '</nav>';
		}
		?>
	<?php else : ?>
		<p class="lead"><?php echo is_search() ? 'No matching products. Try a catalogue number such as SS-V10.' : 'No products here yet.'; ?></p>
	<?php endif; ?>
	<?php echo ss_partner_note(); // phpcs:ignore ?>
</div></section>
<?php
get_footer();
