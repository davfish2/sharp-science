<?php
defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'woocommerce', array( 'thumbnail_image_width' => 600, 'single_image_width' => 1000 ) );
} );

add_action( 'wp_enqueue_scripts', function () {
	$v = wp_get_theme()->get( 'Version' );
	wp_enqueue_style( 'ss-site', get_theme_file_uri( 'assets/css/site.css' ), array(), $v );
	wp_enqueue_script( 'ss-site', get_theme_file_uri( 'assets/js/site.js' ), array(), $v, array( 'strategy' => 'defer', 'in_footer' => true ) );
	wp_add_inline_script( 'ss-site', 'window.SS=' . wp_json_encode( array(
		'store'     => esc_url_raw( rest_url( 'wc/store/v1/' ) ),
		'cart'      => wc_get_cart_url(),
		'checkout'  => wc_get_checkout_url(),
		'threshold' => ss_free_threshold(),
	) ) . ';', 'before' );
}, 20 );

add_action( 'wp_head', function () {
	printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( get_theme_file_uri( 'assets/fonts/instrument-serif-latin-400-normal.woff2' ) ) );
	printf( '<link rel="icon" href="%s" type="image/svg+xml"><link rel="icon" href="%s" sizes="32x32"><link rel="apple-touch-icon" href="%s">' . "\n", esc_url( get_theme_file_uri( 'assets/img/favicon.svg' ) ), esc_url( get_theme_file_uri( 'assets/img/favicon-32.png' ) ), esc_url( get_theme_file_uri( 'assets/img/favicon-180.png' ) ) );
	echo '<meta name="theme-color" content="#FFFFFF">' . "\n";
}, 2 );

function ss_logo( string $name = 'horizontal-light' ): string {
	static $cache = array();
	return $cache[ $name ] ??= (string) file_get_contents( get_theme_file_path( "assets/img/logo-$name.svg" ) );
}

/** 1.5px line icons on a 24 grid. */
function ss_icon( string $name ): string {
	$p = array(
		'certificate' => '<path d="M6 3h12v18l-6-3-6 3z"/><path d="m9 10 2 2 4-4"/>',
		'express'     => '<path d="M2 7h12v10H2zM14 10h4l4 4v3h-8z"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/>',
		'free'        => '<path d="M3 8h18v12H3zM3 8l2-4h14l2 4M12 4v16"/>',
		'flask'       => '<path d="M9 3h6M10 3v6l-5.5 9.5A1.7 1.7 0 0 0 6 21h12a1.7 1.7 0 0 0 1.5-2.5L14 9V3M7.5 15h9"/>',
		'search'      => '<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>',
		'cart'        => '<path d="M5 8h14l-1 13H6zM9 8V6a3 3 0 0 1 6 0v2"/>',
		'menu'        => '<path d="M3 7h18M3 12h18M3 17h18"/>',
		'close'       => '<path d="m5 5 14 14M19 5 5 19"/>',
		'arrow'       => '<path d="M4 12h16m-6-6 6 6-6 6"/>',
		'plus'        => '<path d="M12 5v14M5 12h14"/>',
		'chevron'     => '<path d="m6 9 6 6 6-6"/>',
	);
	return '<svg class="icon" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" aria-hidden="true">' . ( $p[ $name ] ?? '' ) . '</svg>';
}

function ss_money( float $n ): string {
	return '$' . ( floor( $n ) === $n ? number_format( $n ) : number_format( $n, 2 ) );
}

function ss_page_url( string $slug ): string {
	$p = get_page_by_path( $slug );
	return $p ? get_permalink( $p ) : home_url( "/$slug/" );
}

/** Categories in catalogue order, with band colour, count and a representative image. */
function ss_categories(): array {
	$terms = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true, 'exclude' => array( (int) get_option( 'default_product_cat' ) ) ) );
	if ( is_wp_error( $terms ) ) {
		return array();
	}
	$out = array();
	foreach ( $terms as $t ) {
		$img   = (int) get_term_meta( $t->term_id, 'thumbnail_id', true );
		if ( ! $img ) {
			$ids = wc_get_products( array( 'limit' => 1, 'return' => 'ids', 'status' => 'publish', 'category' => array( $t->slug ), 'featured' => true ) ) ?: wc_get_products( array( 'limit' => 1, 'return' => 'ids', 'status' => 'publish', 'category' => array( $t->slug ) ) );
			$img = $ids ? (int) get_post_thumbnail_id( $ids[0] ) : 0;
		}
		$out[] = array(
			'name'  => html_entity_decode( $t->name ),
			'slug'  => $t->slug,
			'url'   => get_term_link( $t ),
			'count' => (int) $t->count,
			'band'  => get_term_meta( $t->term_id, 'ss_band', true ) ?: '#9AA3A6',
			'image' => $img,
			'order' => (int) get_term_meta( $t->term_id, 'ss_order', true ),
		);
	}
	usort( $out, fn( $a, $b ) => $a['order'] <=> $b['order'] ?: strcmp( $a['name'], $b['name'] ) );
	return $out;
}

function ss_best_sellers( int $limit = 8 ): array {
	$ids = get_posts( array( 'post_type' => 'product', 'post_status' => 'publish', 'numberposts' => $limit, 'fields' => 'ids', 'meta_key' => '_ss_best_seller', 'meta_value' => 1, 'orderby' => 'menu_order title', 'order' => 'ASC' ) );
	return array_filter( array_map( 'wc_get_product', $ids ) );
}

function ss_product_image( WC_Product $p, string $size = 'woocommerce_thumbnail', array $attr = array() ): string {
	$id = (int) $p->get_image_id();
	if ( $id ) {
		return wp_get_attachment_image( $id, $size, false, $attr + array( 'alt' => $p->get_name() ) );
	}
	$svg = ss_asset_paths( $p->get_id() )['url'] . '/render.svg';
	return sprintf( '<img src="%s" alt="%s" width="600" height="600" loading="lazy">', esc_url( $svg ), esc_attr( $p->get_name() ) );
}

function ss_free_ship_bar(): string {
	$value = function_exists( 'WC' ) && WC()->cart ? ss_cart_value() : 0.0;
	$goal  = ss_free_threshold();
	$pct   = $goal > 0 ? min( 100, round( $value / $goal * 100 ) ) : 100;
	$left  = max( 0, $goal - $value );
	$text  = $left > 0 ? sprintf( 'Add %s for free Express shipping', '$' . number_format( $left, 2 ) ) : 'Free Express shipping unlocked';
	return sprintf( '<div class="ship-bar" data-ship-bar><p data-ship-text>%s</p><div class="ship-bar__track" role="progressbar" aria-label="Progress to free shipping" aria-valuemin="0" aria-valuemax="100" aria-valuenow="%2$d"><span style="width:%2$d%%"></span></div></div>', esc_html( $text ), (int) $pct );
}

function ss_section_label( string $n, string $text ): string {
	return sprintf( '<p class="eyebrow"><span>%s</span>%s</p>', esc_html( $n ), esc_html( $text ) );
}

function ss_faqs(): array {
	$cut  = DateTime::createFromFormat( 'H:i', (string) ss_get( 'cutoff' ) );
	$time = $cut ? strtolower( $cut->format( 'g' . ( '00' === $cut->format( 'i' ) ? '' : ':i' ) . 'a' ) ) : '';
	$faqs = array(
		array( 'When will my order ship?', sprintf( 'Orders placed before %s AWST on a dispatch day leave the same day by Express Post.', $time ) ),
		array( 'How do certificates work?', 'Search your product or batch number on the Certificates page. Certificates are listed for the batches shown.' ),
		array( 'Do you supply institutions?', 'Yes. Contact us for bulk orders, quotes and purchase orders.' ),
		array( 'What is your returns policy?', 'Faulty or damaged goods are covered by the Australian Consumer Law. See our Returns page.' ),
		array( 'Who can order?', 'We supply trade and professional customers. You confirm this at checkout under our Terms of Sale.' ),
	);
	if ( ss_partner_note() ) {
		array_splice( $faqs, 4, 0, array( array( 'Can you source other items?', (string) ss_get( 'partner_text' ) ) ) );
	}
	return $faqs;
}
