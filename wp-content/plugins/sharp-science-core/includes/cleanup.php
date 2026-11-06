<?php
defined( 'ABSPATH' ) || exit;

/** Removes platform fingerprints and default styling from the public site. */
add_action( 'init', function () {
	foreach ( array( 'wp_generator', 'rsd_link', 'wlwmanifest_link', 'wp_shortlink_wp_head', 'rest_output_link_wp_head', 'wp_oembed_add_discovery_links', 'feed_links', 'feed_links_extra', 'adjacent_posts_rel_link_wp_head', 'wp_resource_hints' ) as $fn ) {
		remove_action( 'wp_head', $fn, 10 );
	}
	remove_action( 'wp_head', 'feed_links', 2 );
	remove_action( 'wp_head', 'feed_links_extra', 3 );
	remove_action( 'wp_head', 'wp_resource_hints', 2 );
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
	remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
	remove_action( 'template_redirect', 'rest_output_link_header', 11 );
	remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
	remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
	remove_action( 'wp_footer', 'wc_no_js' );
	remove_action( 'wp_head', 'wc_generator_tag' );
	add_filter( 'the_generator', '__return_empty_string' );
	add_filter( 'emoji_svg_url', '__return_false' );
	add_filter( 'wp_img_tag_add_auto_sizes', '__return_false' );
	add_filter( 'wp_sitemaps_add_provider', fn( $p, $name ) => 'users' === $name ? false : $p, 10, 2 );
} );

add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'show_admin_bar', '__return_false' );
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );
add_filter( 'woocommerce_demo_store', '__return_empty_string' );
add_filter( 'wp_speculation_rules_configuration', '__return_null' );

/** Default styles and scripts go. Checkout keeps only what the block checkout needs. */
add_action( 'wp_enqueue_scripts', function () {
	foreach ( array( 'wp-block-library', 'wp-block-library-theme', 'classic-theme-styles', 'global-styles', 'wc-blocks-style' ) as $h ) {
		wp_dequeue_style( $h );
		wp_deregister_style( $h );
	}
	foreach ( array( 'select2', 'woocommerce-general', 'woocommerce-layout', 'woocommerce-smallscreen' ) as $h ) {
		wp_dequeue_style( $h );
	}
	foreach ( array( 'woocommerce', 'wc-add-to-cart', 'wc-cart-fragments', 'wc-cart', 'wc-single-product', 'selectWoo', 'select2', 'sourcebuster-js', 'wc-order-attribution', 'wc-country-select', 'wc-address-i18n', 'zoom', 'flexslider', 'photoswipe-ui-default', 'jquery-blockui', 'js-cookie', 'wc-jquery-blockui', 'wc-js-cookie' ) as $h ) {
		wp_dequeue_script( $h );
	}
	wp_deregister_script( 'jquery' );
	wp_deregister_script( 'wp-embed' );
}, 999 );

add_filter( 'body_class', function ( $classes ) {
	$keep = array();
	if ( is_front_page() ) {
		$keep[] = 'is-home';
	}
	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		$keep[] = 'is-checkout';
	}
	return $keep;
}, 99 );
add_filter( 'post_class', fn() => array(), 99 );
add_filter( 'woocommerce_post_class', fn() => array(), 99 );

/** Stops anonymous discovery through author archives and the users endpoint. */
add_filter( 'rest_endpoints', function ( $e ) {
	if ( ! current_user_can( 'list_users' ) ) {
		unset( $e['/wp/v2/users'], $e['/wp/v2/users/(?P<id>[\d]+)'] );
	}
	return $e;
} );
add_action( 'template_redirect', function () {
	if ( is_author() ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
} );
