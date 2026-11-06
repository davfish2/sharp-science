<?php
defined( 'ABSPATH' ) || exit;
get_header();
$q     = sanitize_text_field( wp_unslash( $_GET['q'] ?? '' ) ); // phpcs:ignore
$batch = sanitize_text_field( wp_unslash( $_GET['batch'] ?? '' ) ); // phpcs:ignore
$cat   = sanitize_title( wp_unslash( $_GET['category'] ?? '' ) ); // phpcs:ignore
$rows  = ss_certificates( array( 'q' => $q, 'batch' => $batch, 'category' => $cat ) );
?>
<section class="archive"><div class="wrap">
	<header class="page__head">
		<p class="eyebrow"><span><?php echo esc_html( sprintf( '%02d', count( $rows ) ) ); ?></span>Certificates</p>
		<h1>Certificates</h1>
		<p class="lead"><?php echo esc_html( ss_trust_line() ); ?>. Listed for the batches shown.</p>
	</header>
	<form class="cert-search" action="<?php echo esc_url( ss_page_url( 'certificates' ) ); ?>">
		<div><label for="cert-q">Product or batch</label><input id="cert-q" name="q" value="<?php echo esc_attr( $q ?: $batch ); ?>" placeholder="SS-V10 or B26-1015"></div>
		<div><label for="cert-cat">Category</label><select id="cert-cat" name="category"><option value="">All categories</option>
			<?php foreach ( ss_categories() as $c ) : ?><option value="<?php echo esc_attr( $c['slug'] ); ?>" <?php selected( $cat, $c['slug'] ); ?>><?php echo esc_html( $c['name'] ); ?></option><?php endforeach; ?>
		</select></div>
		<button class="btn" type="submit">Search</button>
	</form>
	<?php if ( $rows ) : ?>
		<?php get_template_part( 'template-parts/cert-table', null, array( 'rows' => $rows ) ); ?>
		<?php if ( in_array( 'Sample data', array_column( $rows, 'result' ), true ) ) : ?>
			<p class="cert-demo">Sample data for demonstration. These are not real test records.</p>
		<?php endif; ?>
	<?php else : ?>
		<p class="lead">No certificate is listed for that search. <a href="<?php echo esc_url( ss_page_url( 'contact' ) ); ?>">Contact us</a> with your batch number.</p>
	<?php endif; ?>
	<?php while ( have_posts() ) : the_post(); ?><div class="prose cert-notes"><?php the_content(); ?></div><?php endwhile; ?>
</div></section>
<?php
get_footer();
