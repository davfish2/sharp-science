<?php
defined( 'ABSPATH' ) || exit;

/** Goods value including GST, after discounts. This is the figure compared with the threshold. */
function ss_cart_value(): float {
	$cart = WC()->cart;
	return $cart ? round( (float) $cart->get_cart_contents_total() + (float) $cart->get_cart_contents_tax(), 2 ) : 0.0;
}

/** One threshold, from one setting: free replaces the flat rate at or above it and is hidden below it. */
add_filter( 'woocommerce_package_rates', function ( $rates ) {
	$qualifies = ss_cart_value() >= ss_free_threshold();
	$free      = array_filter( $rates, fn( $r ) => 'free_shipping' === $r->method_id );
	if ( $qualifies && $free ) {
		return $free;
	}
	return array_filter( $rates, fn( $r ) => 'free_shipping' !== $r->method_id );
}, 100 );

/** Optional multi-item discount. Off by default. */
function ss_discount_tiers(): array {
	$tiers = array();
	foreach ( explode( ',', (string) ss_get( 'discount_tiers' ) ) as $pair ) {
		$p = array_map( 'trim', explode( ':', $pair ) );
		if ( 2 === count( $p ) && (int) $p[0] > 0 ) {
			$tiers[ (int) $p[0] ] = (float) $p[1];
		}
	}
	ksort( $tiers );
	return $tiers;
}
add_action( 'woocommerce_cart_calculate_fees', function ( WC_Cart $cart ) {
	if ( ! ss_get( 'discount_enabled' ) ) {
		return;
	}
	$pct = 0;
	foreach ( ss_discount_tiers() as $qty => $rate ) {
		if ( $cart->get_cart_contents_count() >= $qty ) {
			$pct = $rate;
		}
	}
	if ( $pct ) {
		$cart->add_fee( sprintf( 'Multi-item discount (%s%%)', $pct ), -1 * $cart->get_subtotal() * $pct / 100, true );
	}
} );
