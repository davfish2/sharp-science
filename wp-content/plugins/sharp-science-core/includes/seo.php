<?php
defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	add_rewrite_rule( '^sitemap\.xml$', 'index.php?sitemap=index', 'top' );
	add_rewrite_rule( '^shop/page/([0-9]+)/?$', 'index.php?post_type=product&paged=$matches[1]', 'top' );
} );

add_filter( 'redirect_canonical', fn( $to, $from ) => str_contains( $from, 'sitemap.xml' ) ? false : $to, 10, 2 );

add_filter( 'document_title_separator', fn() => '|' );
add_filter( 'document_title_parts', function ( $t ) {
	unset( $t['tagline'] );
	return $t;
} );

function ss_meta_description(): string {
	if ( function_exists( 'is_product' ) && is_product() ) {
		$p = wc_get_product( get_queried_object_id() );
		return wp_strip_all_tags( $p->get_short_description() ) . ' Catalogue number ' . $p->get_sku() . '.';
	}
	if ( function_exists( 'is_product_category' ) && is_product_category() ) {
		return single_term_title( '', false ) . ' from Sharp Science. Express Post dispatch across Australia.';
	}
	if ( is_singular( 'page' ) && ! is_front_page() ) {
		return get_the_title() . ' at Sharp Science, Australian supplier of laboratory glassware, vials and instruments.';
	}
	return 'Laboratory glassware, sample vials and instruments for Australian trade and professional customers.';
}

add_action( 'wp_head', function () {
	$desc  = ss_meta_description();
	$title = wp_get_document_title();
	$url   = is_singular() ? get_permalink() : home_url( add_query_arg( array(), wp_parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ) ) ); // phpcs:ignore
	$img   = get_theme_file_uri( 'assets/img/social.png' );
	if ( function_exists( 'is_product' ) && is_product() && has_post_thumbnail() ) {
		$img = get_the_post_thumbnail_url( null, 'large' );
	}
	printf( '<meta name="description" content="%s">' . "\n", esc_attr( $desc ) );
	if ( ! is_singular() ) {
		printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $url ) );
	}
	foreach ( array( 'og:site_name' => 'Sharp Science', 'og:type' => is_product() ? 'product' : 'website', 'og:title' => $title, 'og:description' => $desc, 'og:url' => $url, 'og:image' => $img, 'og:locale' => 'en_AU' ) as $k => $v ) {
		printf( '<meta property="%s" content="%s">' . "\n", esc_attr( $k ), esc_attr( $v ) );
	}
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";

	if ( function_exists( 'is_product' ) && is_product() ) {
		$p = wc_get_product( get_queried_object_id() );
		$schema = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'Product',
			'name'        => $p->get_name(),
			'sku'         => $p->get_sku(),
			'mpn'         => $p->get_sku(),
			'description' => wp_strip_all_tags( $p->get_short_description() ),
			'image'       => $img,
			'brand'       => array( '@type' => 'Brand', 'name' => 'Sharp Science' ),
			'offers'      => array(
				'@type'         => 'Offer',
				'url'           => get_permalink( $p->get_id() ),
				'price'         => wc_format_decimal( $p->get_price(), 2 ),
				'priceCurrency' => 'AUD',
				'availability'  => $p->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
			),
		);
		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
	}
}, 1 );

add_filter( 'woocommerce_structured_data_product', '__return_empty_array' );
add_filter( 'woocommerce_structured_data_website', '__return_empty_array' );
