<?php
defined( 'ABSPATH' ) || exit;

const SS_CONFIRM_FIELD = 'sharp-science/customer-confirmation';

/** Required checkbox in the block checkout. WooCommerce validates it on the server too. */
add_action( 'woocommerce_init', function () {
	if ( ! function_exists( 'woocommerce_register_additional_checkout_field' ) ) {
		return;
	}
	woocommerce_register_additional_checkout_field( array(
		'id'         => SS_CONFIRM_FIELD,
		'label'      => ss_get( 'checkbox_text' ),
		'location'   => 'order',
		'type'       => 'checkbox',
		'required'   => true,
		'error_message' => 'Please confirm to place your order.',
	) );
} );

/** Stores what the customer agreed to, and when, beside the field value. */
add_action( 'woocommerce_store_api_checkout_order_processed', function ( WC_Order $order ) {
	$fields = \Automattic\WooCommerce\Blocks\Package::container()->get( \Automattic\WooCommerce\Blocks\Domain\Services\CheckoutFields::class );
	if ( ! $fields->get_field_from_object( SS_CONFIRM_FIELD, $order, 'other' ) ) {
		return;
	}
	$order->update_meta_data( '_ss_confirmed_at', gmdate( 'c' ) );
	$order->update_meta_data( '_ss_confirmed_text', ss_get( 'checkbox_text' ) );
	$order->save();
} );

add_action( 'woocommerce_admin_order_data_after_billing_address', function ( WC_Order $order ) {
	$at = $order->get_meta( '_ss_confirmed_at' );
	if ( $at ) {
		printf( '<p><strong>Customer confirmation</strong><br>%s<br>%s UTC</p>', esc_html( $order->get_meta( '_ss_confirmed_text' ) ), esc_html( $at ) );
	}
} );

/** Our header strip above the checkout block: shipping progress, lab-use line and terms link. */
add_filter( 'render_block_woocommerce/checkout', function ( $html ) {
	if ( is_wc_endpoint_url( 'order-received' ) ) {
		return $html;
	}
	$terms = get_page_by_path( 'terms-of-sale' );
	$pre   = function_exists( 'ss_free_ship_bar' ) ? ss_free_ship_bar() : '';
	$note  = '<p class="checkout-note">' . esc_html( ss_get( 'lab_line' ) ) . ( $terms ? ' · <a href="' . esc_url( get_permalink( $terms ) ) . '">Read the Terms of Sale</a>' : '' ) . '</p>';
	return $pre . $note . $html;
} );

add_filter( 'woocommerce_countries_tax_or_vat', fn() => 'GST' );
add_filter( 'woocommerce_countries_inc_tax_or_vat', fn() => '(incl. GST)' );
add_filter( 'woocommerce_get_order_item_totals', function ( $rows ) {
	if ( isset( $rows['order_total']['value'] ) ) {
		$rows['order_total']['value'] = str_replace( array( ' Tax)', ' tax)' ), ' GST)', $rows['order_total']['value'] );
	}
	return $rows;
} );
