<?php
defined( 'ABSPATH' ) || exit;
$cart = WC()->cart;
$cart->calculate_totals();
?>
<?php echo ss_free_ship_bar(); // phpcs:ignore ?>
<div class="cart-grid">
	<form class="cart-lines" method="post" action="<?php echo esc_url( wc_get_cart_url() ); ?>">
		<?php foreach ( $cart->get_cart() as $key => $item ) : $p = $item['data']; ?>
			<div class="line">
				<a class="line__image" href="<?php echo esc_url( $p->get_permalink() ); ?>" tabindex="-1" aria-hidden="true"><?php echo ss_product_image( $p, 'thumbnail' ); // phpcs:ignore ?></a>
				<div class="line__info">
					<a href="<?php echo esc_url( $p->get_permalink() ); ?>"><?php echo esc_html( $p->get_name() ); ?></a>
					<span class="mono"><?php echo esc_html( $p->get_sku() ); ?></span>
					<a class="line__remove" href="<?php echo esc_url( wc_get_cart_remove_url( $key ) ); ?>" aria-label="<?php echo esc_attr( 'Remove ' . $p->get_name() ); ?>">Remove</a>
				</div>
				<label class="sr" for="q-<?php echo esc_attr( $key ); ?>">Quantity</label>
				<input id="q-<?php echo esc_attr( $key ); ?>" type="number" min="0" name="cart[<?php echo esc_attr( $key ); ?>][qty]" value="<?php echo esc_attr( $item['quantity'] ); ?>" inputmode="numeric">
				<strong class="price"><?php echo wp_kses_post( $cart->get_product_subtotal( $p, $item['quantity'] ) ); ?></strong>
			</div>
		<?php endforeach; ?>
		<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
		<button class="btn btn--ghost" type="submit" name="update_cart" value="1">Update cart</button>
	</form>
	<aside class="totals" aria-label="Order total">
		<dl>
			<div><dt>Subtotal</dt><dd><?php echo wp_kses_post( $cart->get_cart_subtotal() ); ?></dd></div>
			<?php foreach ( $cart->get_fees() as $fee ) : ?><div><dt><?php echo esc_html( $fee->name ); ?></dt><dd><?php echo wp_kses_post( wc_price( $fee->total + $fee->tax ) ); ?></dd></div><?php endforeach; ?>
			<div><dt>Express Post</dt><dd data-shipping><?php $s = (float) $cart->get_shipping_total() + (float) $cart->get_shipping_tax(); echo $s > 0 ? wp_kses_post( wc_price( $s ) ) : 'Free'; ?></dd></div>
			<div class="totals__total"><dt>Total</dt><dd><?php echo wp_kses_post( wc_price( (float) $cart->get_total( 'edit' ) ) ); ?></dd></div>
			<div class="totals__tax"><dt>Includes GST</dt><dd><?php echo wp_kses_post( wc_price( (float) $cart->get_total_tax() ) ); ?></dd></div>
		</dl>
		<a class="btn btn--block" href="<?php echo esc_url( wc_get_checkout_url() ); ?>">Checkout</a>
		<p class="totals__note" data-lab-line><?php echo esc_html( ss_get( 'lab_line' ) ); ?></p>
		<?php echo ss_partner_note(); // phpcs:ignore ?>
	</aside>
</div>
