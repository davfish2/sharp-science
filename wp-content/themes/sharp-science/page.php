<?php
defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) :
	the_post();
	$wide = function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() );
	?>
	<article class="page <?php echo $wide ? 'page--wide' : ''; ?>">
		<div class="wrap">
			<header class="page__head"><h1><?php the_title(); ?></h1></header>
			<div class="<?php echo $wide ? '' : 'prose'; ?>"><?php the_content(); ?></div>
		</div>
	</article>
	<?php
endwhile;
get_footer();
