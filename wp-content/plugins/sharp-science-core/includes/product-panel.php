<?php
defined( 'ABSPATH' ) || exit;

/** Label sizes offered in the panel, in millimetres. */
function ss_label_sizes(): array {
	return array( '50x22' => '50 x 22 mm (standard)', '35x15' => '35 x 15 mm (small)', '70x30' => '70 x 30 mm (large)' );
}

function ss_category_band( int $product_id ): string {
	$terms = get_the_terms( $product_id, 'product_cat' );
	foreach ( (array) $terms as $t ) {
		if ( $t instanceof WP_Term ) {
			$c = get_term_meta( $t->term_id, 'ss_band', true );
			if ( $c ) {
				return $c;
			}
		}
	}
	return '#9AA3A6';
}

function ss_product_data( int $id ): array {
	$prod  = wc_get_product( $id );
	$size  = get_post_meta( $id, '_ss_label_size', true ) ?: '50x22';
	[ $w, $h ] = array_map( 'floatval', explode( 'x', $size ) );
	$type  = get_post_meta( $id, '_ss_render_type', true ) ?: 'auto';
	$terms = get_the_terms( $id, 'product_cat' );
	return array(
		'name'         => $prod->get_name(),
		'spec'         => (string) get_post_meta( $id, '_ss_spec', true ),
		'catalogue_no' => $prod->get_sku() ?: 'SS-' . $id,
		'batch'        => ss_product_batch( $id ),
		'band'         => get_post_meta( $id, '_ss_band', true ) ?: ss_category_band( $id ),
		'category'     => $terms && ! is_wp_error( $terms ) ? $terms[0]->name : '',
		'label_w'      => $w,
		'label_h'      => $h,
		'type'         => 'auto' === $type ? ss_detect_render_type( $prod->get_name(), (string) get_post_meta( $id, '_ss_spec', true ) ) : $type,
	);
}

function ss_asset_paths( int $id ): array {
	$u = wp_upload_dir();
	return array( 'dir' => $u['basedir'] . "/sharp-science/$id", 'url' => $u['baseurl'] . "/sharp-science/$id" );
}

/** Writes the label SVG, label PDF and render SVG, then flags the image for WebP conversion if it changed. */
function ss_generate_assets( int $id ): void {
	$prod = wc_get_product( $id );
	if ( ! $prod ) {
		return;
	}
	$p    = ss_product_data( $id );
	$path = ss_asset_paths( $id );
	wp_mkdir_p( $path['dir'] );
	file_put_contents( $path['dir'] . '/label.svg', ss_label_svg( $p, $p['label_w'], $p['label_h'] ) );
	file_put_contents( $path['dir'] . '/label.pdf', ss_label_pdf( $p, $p['label_w'], $p['label_h'] ) );
	file_put_contents( $path['dir'] . '/render.svg', ss_render_svg( $p ) );

	$hash  = md5( wp_json_encode( $p ) );
	$thumb = (int) $prod->get_image_id();
	$own   = (int) get_post_meta( $id, '_ss_render_attachment', true );
	if ( $thumb && $thumb !== $own ) {
		delete_post_meta( $id, '_ss_render_pending' ); // The client chose their own photo.
		return;
	}
	if ( get_post_meta( $id, '_ss_render_hash', true ) !== $hash || ! $thumb ) {
		update_post_meta( $id, '_ss_render_hash', $hash );
		update_post_meta( $id, '_ss_render_pending', 1 );
	}
}

add_action( 'add_meta_boxes_product', function () {
	add_meta_box( 'ss-details', 'Sharp Science details', 'ss_panel', 'product', 'ss_top', 'high' );
} );
/** First thing on the screen after the product name. */
add_action( 'edit_form_after_title', function ( WP_Post $post ) {
	if ( 'product' === $post->post_type ) {
		do_meta_boxes( get_current_screen(), 'ss_top', $post );
	}
} );

function ss_panel( WP_Post $post ): void {
	$id   = $post->ID;
	$prod = wc_get_product( $id );
	wp_nonce_field( 'ss_panel', 'ss_panel_nonce' );
	$band  = get_post_meta( $id, '_ss_band', true );
	$size  = get_post_meta( $id, '_ss_label_size', true ) ?: '50x22';
	$type  = get_post_meta( $id, '_ss_render_type', true ) ?: 'auto';
	$path  = ss_asset_paths( $id );
	$ready = file_exists( $path['dir'] . '/label.svg' );
	?>
	<div class="ss-panel">
		<p><label for="ss_catalogue_no">Catalogue number</label><input id="ss_catalogue_no" name="ss_catalogue_no" value="<?php echo esc_attr( $prod ? $prod->get_sku() : '' ); ?>" placeholder="SS-V10"></p>
		<p><label for="ss_spec">Spec line</label><input id="ss_spec" name="ss_spec" value="<?php echo esc_attr( get_post_meta( $id, '_ss_spec', true ) ); ?>" placeholder="10 ml, Class A"><small>Separate specs with a comma. Each one gets its own box on the label.</small></p>
		<p><label for="ss_band">Category colour</label><input id="ss_band" name="ss_band" type="text" value="<?php echo esc_attr( $band ); ?>" placeholder="Uses the category colour"><small>Leave blank to use the category colour.</small></p>
		<p><label for="ss_label_size">Label size</label><select id="ss_label_size" name="ss_label_size"><?php foreach ( ss_label_sizes() as $k => $v ) { printf( '<option value="%s"%s>%s</option>', esc_attr( $k ), selected( $size, $k, false ), esc_html( $v ) ); } ?></select></p>
		<p><label for="ss_render_type">Image style</label><select id="ss_render_type" name="ss_render_type"><?php foreach ( ss_render_types() as $k => $v ) { printf( '<option value="%s"%s>%s</option>', esc_attr( $k ), selected( $type, $k, false ), esc_html( $v ) ); } ?></select></p>
		<p class="ss-checks">
			<label><input type="checkbox" name="ss_featured" value="1" <?php checked( $prod && $prod->get_featured() ); ?>> Featured</label>
			<label><input type="checkbox" name="ss_best_seller" value="1" <?php checked( get_post_meta( $id, '_ss_best_seller', true ) ); ?>> Best seller</label>
		</p>
		<?php if ( $ready ) : ?>
			<div class="ss-preview">
				<img src="<?php echo esc_url( $path['url'] . '/label.svg?v=' . time() ); ?>" alt="Generated label" id="ss-label-preview">
				<p id="ss-status">Label and image are up to date.</p>
				<p><a href="<?php echo esc_url( $path['url'] . '/label.svg' ); ?>" download>Label SVG</a> · <a href="<?php echo esc_url( $path['url'] . '/label.pdf' ); ?>" download>Label PDF</a></p>
			</div>
		<?php else : ?>
			<p id="ss-status">The label and image are created when you save.</p>
		<?php endif; ?>
	</div>
	<?php
}

add_action( 'woocommerce_process_product_meta', function ( int $id ) {
	if ( ! isset( $_POST['ss_panel_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['ss_panel_nonce'] ), 'ss_panel' ) ) {
		return;
	}
	$prod = wc_get_product( $id );
	$sku  = sanitize_text_field( wp_unslash( $_POST['ss_catalogue_no'] ?? '' ) );
	if ( $sku ) {
		try {
			$prod->set_sku( $sku );
		} catch ( WC_Data_Exception $e ) {
			WC_Admin_Meta_Boxes::add_error( 'That catalogue number is already in use.' );
		}
	}
	$prod->set_featured( ! empty( $_POST['ss_featured'] ) );
	$prod->save();
	update_post_meta( $id, '_ss_spec', sanitize_text_field( wp_unslash( $_POST['ss_spec'] ?? '' ) ) );
	update_post_meta( $id, '_ss_band', sanitize_hex_color( wp_unslash( $_POST['ss_band'] ?? '' ) ) ?: '' );
	$size = sanitize_text_field( wp_unslash( $_POST['ss_label_size'] ?? '50x22' ) );
	update_post_meta( $id, '_ss_label_size', isset( ss_label_sizes()[ $size ] ) ? $size : '50x22' );
	$type = sanitize_key( $_POST['ss_render_type'] ?? 'auto' );
	update_post_meta( $id, '_ss_render_type', isset( ss_render_types()[ $type ] ) ? $type : 'auto' );
	update_post_meta( $id, '_ss_best_seller', empty( $_POST['ss_best_seller'] ) ? 0 : 1 );
	ss_generate_assets( $id );
}, 99 );

/** Receives the WebP made in the admin browser and sets it as the product image. */
add_action( 'rest_api_init', function () {
	register_rest_route( 'sharp-science/v1', '/render/(?P<id>\d+)', array(
		'methods'             => 'POST',
		'permission_callback' => fn( $r ) => current_user_can( 'edit_post', (int) $r['id'] ),
		'callback'            => 'ss_receive_render',
	) );
} );

function ss_receive_render( WP_REST_Request $r ) {
	$id   = (int) $r['id'];
	$prod = wc_get_product( $id );
	$body = $r->get_body();
	$sig  = substr( $body, 0, 4 ) === 'RIFF' ? 'webp' : ( substr( $body, 1, 3 ) === 'PNG' ? 'png' : '' );
	if ( ! $prod || ! $sig || strlen( $body ) > 8 * MB_IN_BYTES ) {
		return new WP_Error( 'ss_bad_image', 'Not a valid image.', array( 'status' => 400 ) );
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$file = wp_upload_bits( sanitize_title( $prod->get_sku() ?: "product-$id" ) . "-render.$sig", null, $body );
	if ( $file['error'] ) {
		return new WP_Error( 'ss_upload', $file['error'], array( 'status' => 500 ) );
	}
	$att = wp_insert_attachment( array( 'post_mime_type' => "image/$sig", 'post_title' => $prod->get_name(), 'post_status' => 'inherit' ), $file['file'], $id );
	update_post_meta( $att, '_wp_attachment_image_alt', $prod->get_name() );
	wp_update_attachment_metadata( $att, wp_generate_attachment_metadata( $att, $file['file'] ) );
	$old = (int) get_post_meta( $id, '_ss_render_attachment', true );
	set_post_thumbnail( $id, $att );
	update_post_meta( $id, '_ss_render_attachment', $att );
	delete_post_meta( $id, '_ss_render_pending' );
	if ( $old && $old !== $att ) {
		wp_delete_attachment( $old, true );
	}
	return array( 'attachment' => $att );
}

function ss_pending_renders(): array {
	$ids = get_posts( array( 'post_type' => 'product', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids', 'meta_key' => '_ss_render_pending', 'meta_value' => 1 ) );
	return array_map( fn( $i ) => array( 'id' => $i, 'url' => ss_asset_paths( $i )['url'] . '/render.svg' ), $ids );
}
