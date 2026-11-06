<?php
defined( 'ABSPATH' ) || exit;
/** @var WC_Product $product */
$product = $args['product'];
$url     = $product->get_permalink();
?>
<article class="card">
	<a class="card__image" href="<?php echo esc_url( $url ); ?>" tabindex="-1" aria-hidden="true"><?php echo ss_product_image( $product, 'woocommerce_thumbnail', array( 'sizes' => '(max-width: 720px) 50vw, 25vw' ) ); // phpcs:ignore ?></a>
	<div class="card__body">
		<h3><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h3>
		<p class="mono"><?php echo esc_html( $product->get_sku() ); ?></p>
		<p class="card__spec"><?php echo esc_html( get_post_meta( $product->get_id(), '_ss_spec', true ) ); ?></p>
		<div class="card__row">
			<strong class="price"><?php echo wp_kses_post( wc_price( wc_get_price_to_display( $product ) ) ); ?></strong>
			<?php if ( $product->is_in_stock() ) : ?>
				<button class="btn btn--sm btn--ghost" type="button" data-add="<?php echo esc_attr( $product->get_id() ); ?>" aria-label="<?php echo esc_attr( 'Add ' . $product->get_name() . ' to cart' ); ?>">Quick add</button>
			<?php else : ?>
				<span class="badge">Out of stock</span>
			<?php endif; ?>
		</div>
	</div>
</article>
