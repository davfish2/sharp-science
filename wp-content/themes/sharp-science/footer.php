<?php defined( 'ABSPATH' ) || exit; ?>
</main>
<footer class="site-footer">
	<div class="wrap site-footer__grid">
		<div class="site-footer__brand">
			<?php echo ss_logo( 'stacked-dark' ); // phpcs:ignore ?>
			<p>Laboratory glassware, sample vials and instruments, supplied across Australia.</p>
		</div>
		<nav aria-label="Shop"><h2>Shop</h2><ul>
			<?php foreach ( ss_categories() as $c ) : ?>
				<li><a href="<?php echo esc_url( $c['url'] ); ?>"><?php echo esc_html( $c['name'] ); ?></a></li>
			<?php endforeach; ?>
		</ul></nav>
		<nav aria-label="Information"><h2>Information</h2><ul>
			<?php foreach ( array( 'certificates' => 'Certificates', 'shipping' => 'Shipping', 'returns' => 'Returns', 'terms-of-sale' => 'Terms of Sale', 'privacy' => 'Privacy', 'faq' => 'FAQ' ) as $slug => $label ) : ?>
				<li><a href="<?php echo esc_url( ss_page_url( $slug ) ); ?>"><?php echo esc_html( $label ); ?></a></li>
			<?php endforeach; ?>
		</ul></nav>
		<div><h2>Contact</h2><ul>
			<li><a href="<?php echo esc_url( ss_page_url( 'contact' ) ); ?>">Contact us</a></li>
			<?php if ( ss_get( 'email' ) ) : ?><li><a href="mailto:<?php echo esc_attr( ss_get( 'email' ) ); ?>"><?php echo esc_html( ss_get( 'email' ) ); ?></a></li><?php endif; ?>
			<?php if ( ss_get( 'phone' ) ) : ?><li><?php echo esc_html( ss_get( 'phone' ) ); ?></li><?php endif; ?>
		</ul><?php echo ss_partner_note(); // phpcs:ignore ?></div>
	</div>
	<div class="wrap site-footer__base">
		<p data-lab-line><?php echo esc_html( ss_get( 'lab_line' ) ); ?></p>
		<p><?php echo esc_html( ss_trust_line() ); ?></p>
		<p>© <?php echo esc_html( gmdate( 'Y' ) ); ?> Sharp Science<?php echo ss_get( 'abn' ) ? ' · ABN ' . esc_html( ss_get( 'abn' ) ) : ''; ?> · Prices in AUD including GST</p>
		<?php do_action( 'ss_payment_icons' ); ?>
	</div>
</footer>
<div class="scrim" data-scrim hidden></div>
<div class="drawer" id="cart-drawer" role="dialog" aria-modal="true" aria-label="Cart" hidden>
	<div class="drawer__head"><h2>Cart</h2><button class="icon-btn" type="button" data-cart-close aria-label="Close cart"><?php echo ss_icon( 'close' ); // phpcs:ignore ?></button></div>
	<?php echo ss_free_ship_bar(); // phpcs:ignore ?>
	<div class="drawer__items" data-cart-items aria-live="polite"></div>
	<div class="drawer__foot">
		<p class="drawer__total"><span>Subtotal incl. GST</span><strong data-cart-total>$0.00</strong></p>
		<a class="btn btn--block" href="<?php echo esc_url( wc_get_checkout_url() ); ?>">Checkout</a>
		<a class="btn btn--ghost btn--block" href="<?php echo esc_url( wc_get_cart_url() ); ?>">View cart</a>
	</div>
</div>
<?php wp_footer(); ?>
</body>
</html>
