<?php
defined( 'ABSPATH' ) || exit;
get_header();
?>
<section class="state"><div class="wrap">
	<p class="eyebrow"><span>404</span>Not found</p>
	<h1>This page is not on the shelf.</h1>
	<p class="lead">Try the range, or search by catalogue number.</p>
	<div class="hero__cta"><a class="btn" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">Shop the range</a><a class="btn btn--ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a></div>
</div></section>
<?php
get_footer();
