<?php
defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) :
	the_post();
	$product = wc_get_product( get_the_ID() );
	$id      = $product->get_id();
	$spec    = (string) get_post_meta( $id, '_ss_spec', true );
	$terms   = get_the_terms( $id, 'product_cat' );
	$cat     = $terms && ! is_wp_error( $terms ) ? $terms[0] : null;
	$band    = get_post_meta( $id, '_ss_band', true ) ?: ss_category_band( $id );
	$certs   = ss_certificates( array( 'product' => $id ) );
	$assets  = ss_asset_paths( $id );
	$label   = file_exists( $assets['dir'] . '/label.svg' ) ? $assets['url'] . '/label.svg' : '';
	?>
	<section class="product" style="--band:<?php echo esc_attr( $band ); ?>"><div class="wrap">
		<nav class="crumbs" aria-label="Breadcrumb">
			<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">Shop</a>
			<?php if ( $cat ) : ?><span>/</span><a href="<?php echo esc_url( get_term_link( $cat ) ); ?>"><?php echo esc_html( html_entity_decode( $cat->name ) ); ?></a><?php endif; ?>
		</nav>
		<?php wc_print_notices(); ?>
		<div class="product__grid">
			<div class="gallery" data-gallery>
				<div class="gallery__main"><?php echo ss_product_image( $product, 'woocommerce_single', array( 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '(max-width: 900px) 100vw, 50vw', 'data-main' => '1' ) ); // phpcs:ignore ?></div>
				<?php if ( $label ) : ?>
					<div class="gallery__label"><img src="<?php echo esc_url( $label ); ?>" alt="<?php echo esc_attr( $product->get_name() . ' label' ); ?>" width="500" height="220" loading="lazy"></div>
				<?php endif; ?>
			</div>
			<div class="summary">
				<h1><?php echo esc_html( $product->get_name() ); ?></h1>
				<p class="mono summary__sku"><?php echo esc_html( $product->get_sku() ); ?></p>
				<?php if ( $spec ) : ?><ul class="spec-chips"><?php foreach ( array_map( 'trim', explode( ',', $spec ) ) as $s ) : ?><li><?php echo esc_html( $s ); ?></li><?php endforeach; ?></ul><?php endif; ?>
				<p class="summary__desc"><?php echo esc_html( wp_strip_all_tags( $product->get_short_description() ) ); ?></p>
				<p class="summary__price"><strong class="price"><?php echo wp_kses_post( wc_price( wc_get_price_to_display( $product ) ) ); ?></strong> <span>incl. GST</span></p>
				<p class="stock <?php echo $product->is_in_stock() ? 'stock--in' : 'stock--out'; ?>"><?php echo $product->is_in_stock() ? 'In stock' : 'Out of stock'; ?></p>
				<?php if ( $product->is_in_stock() ) : ?>
					<form class="buy" method="post" action="<?php echo esc_url( $product->get_permalink() ); ?>" data-buy="<?php echo esc_attr( $id ); ?>">
						<label class="sr" for="qty">Quantity</label>
						<input id="qty" name="quantity" type="number" min="1" <?php echo $product->get_max_purchase_quantity() > 0 ? 'max="' . esc_attr( $product->get_max_purchase_quantity() ) . '"' : ''; ?> value="1" inputmode="numeric">
						<button class="btn" type="submit" name="add-to-cart" value="<?php echo esc_attr( $id ); ?>">Add to cart</button>
					</form>
				<?php endif; ?>
				<ul class="summary__notes">
					<li><?php echo ss_icon( 'express' ); // phpcs:ignore ?><span data-dispatch><?php echo esc_html( ss_dispatch_message() ); ?></span></li>
					<li><?php echo ss_icon( 'free' ); // phpcs:ignore ?><span>Free Express shipping over <?php echo esc_html( ss_money( ss_free_threshold() ) ); ?></span></li>
					<li><?php echo ss_icon( 'certificate' ); // phpcs:ignore ?><a href="#tab-certificates" data-tab-link="certificates"><?php echo esc_html( ss_trust_line() ); ?></a></li>
					<li><?php echo ss_icon( 'flask' ); // phpcs:ignore ?><span data-lab-line><?php echo esc_html( ss_get( 'lab_line' ) ); ?></span></li>
				</ul>
				<p class="summary__terms"><a href="<?php echo esc_url( ss_page_url( 'terms-of-sale' ) ); ?>">Terms of Sale</a></p>
				<?php echo ss_partner_note(); // phpcs:ignore ?>
			</div>
		</div>
		<div class="tabs" data-tabs>
			<div class="tabs__list" role="tablist" aria-label="Product details">
				<button role="tab" id="t-spec" aria-controls="tab-specifications" aria-selected="true" type="button">Specifications</button>
				<button role="tab" id="t-cert" aria-controls="tab-certificates" aria-selected="false" tabindex="-1" type="button">Certificates</button>
				<button role="tab" id="t-ship" aria-controls="tab-shipping" aria-selected="false" tabindex="-1" type="button">Shipping</button>
			</div>
			<div class="tabs__panel" role="tabpanel" id="tab-specifications" aria-labelledby="t-spec" tabindex="0">
				<table class="spec-table"><tbody>
					<tr><th scope="row">Catalogue number</th><td><?php echo esc_html( $product->get_sku() ); ?></td></tr>
					<tr><th scope="row">Specification</th><td><?php echo esc_html( $spec ); ?></td></tr>
					<?php if ( $cat ) : ?><tr><th scope="row">Category</th><td><?php echo esc_html( html_entity_decode( $cat->name ) ); ?></td></tr><?php endif; ?>
					<tr><th scope="row">Use</th><td><?php echo esc_html( ss_get( 'lab_line' ) ); ?></td></tr>
				</tbody></table>
			</div>
			<div class="tabs__panel" role="tabpanel" id="tab-certificates" aria-labelledby="t-cert" tabindex="0" hidden>
				<?php if ( $certs ) : ?>
					<?php get_template_part( 'template-parts/cert-table', null, array( 'rows' => $certs, 'product_col' => false ) ); ?>
				<?php else : ?>
					<p>No certificates are listed for this product yet. <a href="<?php echo esc_url( ss_page_url( 'contact' ) ); ?>">Contact us</a> with your batch number.</p>
				<?php endif; ?>
			</div>
			<div class="tabs__panel" role="tabpanel" id="tab-shipping" aria-labelledby="t-ship" tabindex="0" hidden>
				<p>Australia Post Express Post, dispatched daily. Free over <?php echo esc_html( ss_money( ss_free_threshold() ) ); ?>. <a href="<?php echo esc_url( ss_page_url( 'shipping' ) ); ?>">Shipping details</a></p>
			</div>
		</div>
	</div></section>
	<?php
endwhile;
get_footer();
