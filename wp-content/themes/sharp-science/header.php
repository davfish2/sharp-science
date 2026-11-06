<?php defined( 'ABSPATH' ) || exit; ?>
<!doctype html>
<html lang="en-AU">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<a class="skip" href="#main">Skip to content</a>
<div class="announce">
	<div class="wrap announce__row">
		<span data-lab-line><?php echo esc_html( ss_get( 'lab_line' ) ); ?></span>
		<span data-dispatch><?php echo esc_html( ss_dispatch_message() ); ?></span>
		<span>Free Express shipping over <?php echo esc_html( ss_money( ss_free_threshold() ) ); ?></span>
	</div>
</div>
<header class="site-header">
	<div class="wrap site-header__row">
		<a class="site-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="Sharp Science home"><?php echo ss_logo(); // phpcs:ignore ?></a>
		<nav class="site-nav" id="site-nav" aria-label="Main">
			<button class="site-nav__shop" type="button" aria-expanded="false" aria-controls="mega">Shop <?php echo ss_icon( 'chevron' ); // phpcs:ignore ?></button>
			<a href="<?php echo esc_url( ss_page_url( 'certificates' ) ); ?>">Certificates</a>
			<a href="<?php echo esc_url( ss_page_url( 'about' ) ); ?>">About</a>
			<a href="<?php echo esc_url( ss_page_url( 'faq' ) ); ?>">FAQ</a>
			<a href="<?php echo esc_url( ss_page_url( 'contact' ) ); ?>">Contact</a>
		</nav>
		<div class="site-tools">
			<button class="icon-btn" type="button" data-search-toggle aria-expanded="false" aria-controls="site-search" aria-label="Search"><?php echo ss_icon( 'search' ); // phpcs:ignore ?></button>
			<button class="icon-btn cart-btn" type="button" data-cart-open aria-label="Open cart"><?php echo ss_icon( 'cart' ); // phpcs:ignore ?><span class="cart-count" data-cart-count hidden>0</span></button>
			<button class="icon-btn menu-btn" type="button" data-menu-toggle aria-expanded="false" aria-controls="site-nav" aria-label="Menu"><?php echo ss_icon( 'menu' ); // phpcs:ignore ?></button>
		</div>
	</div>
	<div class="mega" id="mega" hidden>
		<div class="wrap mega__grid">
			<?php foreach ( ss_categories() as $c ) : ?>
				<a class="mega__tile" href="<?php echo esc_url( $c['url'] ); ?>" style="--band:<?php echo esc_attr( $c['band'] ); ?>">
					<?php echo $c['image'] ? wp_get_attachment_image( $c['image'], 'thumbnail', false, array( 'alt' => '', 'loading' => 'lazy' ) ) : ''; // phpcs:ignore ?>
					<span><?php echo esc_html( $c['name'] ); ?></span>
					<small><?php echo esc_html( sprintf( _n( '%d product', '%d products', $c['count'] ), $c['count'] ) ); ?></small>
				</a>
			<?php endforeach; ?>
			<a class="mega__all" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">All products <?php echo ss_icon( 'arrow' ); // phpcs:ignore ?></a>
		</div>
	</div>
	<form class="site-search" id="site-search" role="search" action="<?php echo esc_url( home_url( '/' ) ); ?>" hidden>
		<div class="wrap">
			<label class="sr" for="site-search-input">Search products</label>
			<input id="site-search-input" type="search" name="s" placeholder="Search by name or catalogue number" value="<?php echo esc_attr( get_search_query() ); ?>">
			<input type="hidden" name="post_type" value="product">
			<button class="btn" type="submit">Search</button>
		</div>
	</form>
</header>
<main id="main">
