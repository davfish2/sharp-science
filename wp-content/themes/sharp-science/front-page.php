<?php
defined( 'ABSPATH' ) || exit;
get_header();
$cats    = ss_categories();
$sellers = ss_best_sellers( 8 );
$sellers = array_slice( $sellers, 0, max( 4, intdiv( count( $sellers ), 4 ) * 4 ) ); // Full rows only.
$free    = ss_money( ss_free_threshold() );
$phrases = array( ss_trust_line(), 'Express Post, dispatched daily', 'Class A volumetric glassware', "Free shipping over $free", 'Prices include GST' );
?>
<section class="hero">
	<div class="wrap hero__grid">
		<div class="hero__copy">
			<h1>Precision glassware, dispatched daily.</h1>
			<p class="lead">Laboratory glassware, vials and instruments for Australian trade and professional customers.</p>
			<div class="hero__cta">
				<a class="btn" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">Shop the range</a>
				<a class="btn btn--ghost" href="<?php echo esc_url( ss_page_url( 'certificates' ) ); ?>">View certificates</a>
			</div>
		</div>
		<div class="hero__media"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/hero.webp' ) ); ?>" alt="Sharp Science flask, vial and beaker" width="1400" height="1000" fetchpriority="high"></div>
	</div>
	<ul class="wrap trust">
		<li><?php echo ss_icon( 'certificate' ); // phpcs:ignore ?><span><?php echo esc_html( ss_trust_line() ); ?></span></li>
		<li><?php echo ss_icon( 'express' ); // phpcs:ignore ?><span>Express Post, dispatched daily</span></li>
		<li><?php echo ss_icon( 'free' ); // phpcs:ignore ?><span>Free shipping over <?php echo esc_html( $free ); ?></span></li>
		<li><?php echo ss_icon( 'flask' ); // phpcs:ignore ?><span>Lab-grade supplies</span></li>
	</ul>
</section>

<section class="section">
	<div class="wrap">
		<header class="section__head"><?php echo ss_section_label( '01', 'Categories' ); // phpcs:ignore ?><h2>The range</h2></header>
		<div class="tiles">
			<?php foreach ( $cats as $c ) : ?>
				<a class="tile" href="<?php echo esc_url( $c['url'] ); ?>" style="--band:<?php echo esc_attr( $c['band'] ); ?>">
					<?php echo $c['image'] ? wp_get_attachment_image( $c['image'], 'woocommerce_thumbnail', false, array( 'alt' => '', 'loading' => 'lazy', 'sizes' => '(max-width: 720px) 100vw, 33vw' ) ) : ''; // phpcs:ignore ?>
					<span class="tile__meta"><span class="tile__name"><?php echo esc_html( $c['name'] ); ?></span><span class="mono" data-tile-count><?php echo esc_html( sprintf( '%02d', $c['count'] ) ); ?></span></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<div class="marquee" aria-hidden="true"><div class="marquee__track">
	<?php for ( $i = 0; $i < 4; $i++ ) : foreach ( $phrases as $t ) : ?><span><?php echo esc_html( $t ); ?></span><?php endforeach; endfor; ?>
</div></div>

<section class="section">
	<div class="wrap">
		<header class="section__head section__head--row">
			<div><?php echo ss_section_label( '02', 'Best sellers' ); // phpcs:ignore ?><h2>Most ordered</h2></div>
			<a class="link" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">All products <?php echo ss_icon( 'arrow' ); // phpcs:ignore ?></a>
		</header>
		<div class="grid">
			<?php foreach ( $sellers as $p ) { get_template_part( 'template-parts/product-card', null, array( 'product' => $p ) ); } ?>
		</div>
	</div>
</section>

<section class="section section--tint">
	<div class="wrap">
		<header class="section__head"><?php echo ss_section_label( '03', 'Assurance' ); // phpcs:ignore ?><h2>Verified, not promised</h2></header>
		<ol class="steps">
			<li><span class="mono">A</span><h3>Sourced</h3><p>Selected to published glassware and instrument standards.</p></li>
			<li><span class="mono">B</span><h3>Certified</h3><p><?php echo esc_html( ss_trust_line() ); ?>.</p></li>
			<li><span class="mono">C</span><h3>Dispatched</h3><p>Express Post every dispatch day.</p></li>
		</ol>
		<form class="batch" action="<?php echo esc_url( ss_page_url( 'certificates' ) ); ?>">
			<div><h3>Check your batch</h3><p>The batch number is printed on your label.</p></div>
			<label class="sr" for="batch-q">Batch number</label>
			<input id="batch-q" name="batch" placeholder="B26-1015" required>
			<button class="btn" type="submit">Check</button>
		</form>
	</div>
</section>

<?php if ( ss_get( 'discount_enabled' ) ) : ?>
<section class="section">
	<div class="wrap">
		<header class="section__head"><?php echo ss_section_label( '04', 'Volume' ); // phpcs:ignore ?><h2>Order more, pay less</h2></header>
		<ul class="tiers">
			<?php foreach ( ss_discount_tiers() as $qty => $pct ) : ?><li><strong class="mono"><?php echo esc_html( $pct ); ?>%</strong><span><?php echo esc_html( "$qty or more items" ); ?></span></li><?php endforeach; ?>
		</ul>
	</div>
</section>
<?php endif; ?>

<section class="section">
	<div class="wrap faq">
		<header class="section__head"><?php echo ss_section_label( ss_get( 'discount_enabled' ) ? '05' : '04', 'FAQ' ); // phpcs:ignore ?><h2>Questions</h2></header>
		<div class="faq__list">
			<?php foreach ( ss_faqs() as [ $q, $a ] ) : ?>
				<details><summary><?php echo esc_html( $q ); ?><?php echo ss_icon( 'plus' ); // phpcs:ignore ?></summary><p><?php echo esc_html( $a ); ?></p></details>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php
get_footer();
