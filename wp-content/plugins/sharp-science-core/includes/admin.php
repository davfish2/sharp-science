<?php
defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', function () {
	add_menu_page( 'Sharp Science', 'Sharp Science', 'edit_products', 'sharp-science', 'ss_admin_page', 'dashicons-admin-generic', 2 );
	foreach ( array( 'edit.php', 'edit-comments.php', 'tools.php' ) as $slug ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			remove_menu_page( $slug );
		}
	}
	remove_menu_page( 'edit.php' );
	remove_menu_page( 'edit-comments.php' );
	remove_menu_page( 'woocommerce-marketing' );
	remove_menu_page( 'admin.php?page=wc-settings&tab=checkout' );
	foreach ( array( 'edit-tags.php?taxonomy=product_tag&amp;post_type=product', 'edit-tags.php?taxonomy=product_brand&amp;post_type=product', 'product_attributes', 'product-reviews' ) as $sub ) {
		remove_submenu_page( 'edit.php?post_type=product', $sub );
	}
}, 99 );

add_action( 'admin_init', function () {
	register_setting( 'ss_settings', 'ss_settings', array( 'sanitize_callback' => 'ss_sanitize_settings' ) );
} );

function ss_sanitize_settings( $in ): array {
	$out = array();
	foreach ( ss_setting_fields() as $group ) {
		foreach ( $group as $key => [ , $type, $default ] ) {
			$v = $in[ $key ] ?? '';
			$out[ $key ] = match ( $type ) {
				'checkbox' => empty( $v ) ? 0 : 1,
				'number'   => max( 0, (float) $v ),
				'url'      => esc_url_raw( $v ),
				'textarea' => sanitize_textarea_field( $v ),
				default    => sanitize_text_field( $v ),
			};
		}
	}
	if ( ! in_array( $out['timezone'], DateTimeZone::listIdentifiers(), true ) ) {
		$out['timezone'] = 'Australia/Perth';
	}
	return $out;
}

function ss_admin_page(): void {
	$res = get_transient( 'ss_import_result' );
	delete_transient( 'ss_import_result' );
	?>
	<div class="wrap ss-page">
		<h1>Sharp Science</h1>
		<?php if ( $res ) : ?>
			<div class="notice notice-<?php echo $res['errors'] ? 'warning' : 'success'; ?>"><p><?php echo esc_html( sprintf( 'Import finished: %d created, %d updated.', $res['created'], $res['updated'] ) ); ?></p>
			<?php foreach ( $res['errors'] as $e ) { printf( '<p>%s</p>', esc_html( $e ) ); } ?></div>
		<?php endif; ?>
		<div class="ss-tools">
			<div><h3>Export products</h3><p>Download every product as a CSV file.</p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><input type="hidden" name="action" value="ss_export"><?php wp_nonce_field( 'ss_export' ); ?><button class="button button-primary">Export CSV</button></form></div>
			<div><h3>Import products</h3><p>Rows update by catalogue number. New numbers create products.</p>
				<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><input type="hidden" name="action" value="ss_import"><?php wp_nonce_field( 'ss_import' ); ?><input type="file" name="ss_csv" accept=".csv" required><p><button class="button button-primary">Import CSV</button></p></form></div>
			<div><h3>Product images</h3><p id="ss-status"><?php $n = count( ss_pending_renders() ); echo esc_html( $n ? "$n image(s) are being prepared. Keep this page open." : 'All product images are up to date.' ); ?></p></div>
		</div>
		<form method="post" action="options.php">
			<?php settings_fields( 'ss_settings' ); ?>
			<?php foreach ( ss_setting_fields() as $title => $fields ) : ?>
				<h2><?php echo esc_html( $title ); ?></h2>
				<table class="form-table" role="presentation">
					<?php foreach ( $fields as $key => [ $label, $type, , $help ] ) : $v = ss_get( $key ); $name = "ss_settings[$key]"; ?>
						<tr><th scope="row"><label for="ss-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th><td>
							<?php if ( 'checkbox' === $type ) : ?>
								<input id="ss-<?php echo esc_attr( $key ); ?>" type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1" <?php checked( $v ); ?>>
							<?php elseif ( 'textarea' === $type ) : ?>
								<textarea id="ss-<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $name ); ?>" rows="4"><?php echo esc_textarea( $v ); ?></textarea>
							<?php else : ?>
								<input id="ss-<?php echo esc_attr( $key ); ?>" class="regular-text" type="<?php echo esc_attr( $type ); ?>" <?php echo 'number' === $type ? 'step="0.01" min="0"' : ''; ?> name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $v ); ?>">
							<?php endif; ?>
							<?php if ( $help ) { printf( '<p class="description">%s</p>', esc_html( $help ) ); } ?>
						</td></tr>
					<?php endforeach; ?>
				</table>
			<?php endforeach; ?>
			<?php submit_button( 'Save settings' ); ?>
		</form>
	</div>
	<?php
}

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	wp_enqueue_style( 'ss-admin', SS_CORE_URL . 'assets/admin.css', array(), '1.0.0' );
	$screen = get_current_screen();
	$is_edit = $screen && 'product' === $screen->post_type && 'post' === $screen->base;
	if ( ! $is_edit && 'toplevel_page_sharp-science' !== $hook ) {
		return;
	}
	$pending = ss_pending_renders();
	if ( $is_edit ) {
		$id      = (int) ( $_GET['post'] ?? 0 ); // phpcs:ignore
		$pending = array_values( array_filter( $pending, fn( $p ) => $p['id'] === $id ) );
	}
	wp_enqueue_script( 'ss-admin', SS_CORE_URL . 'assets/admin.js', array(), '1.0.0', true );
	wp_add_inline_script( 'ss-admin', 'window.SS_ADMIN=' . wp_json_encode( array( 'pending' => $pending, 'rest' => esc_url_raw( rest_url( 'sharp-science/v1/' ) ), 'nonce' => wp_create_nonce( 'wp_rest' ) ) ) . ';', 'before' );
} );

/** Category colour on the category screens. */
$ss_term_field = function ( $term = null ) {
	$v = $term instanceof WP_Term ? get_term_meta( $term->term_id, 'ss_band', true ) : '';
	echo '<div class="form-field"><label for="ss_band">Category colour</label><input id="ss_band" name="ss_band" type="text" placeholder="#7A8B99" value="' . esc_attr( $v ) . '"><p>Used on tiles, labels and product images.</p></div>';
};
add_action( 'product_cat_add_form_fields', $ss_term_field );
add_action( 'product_cat_edit_form_fields', $ss_term_field );
$ss_term_save = function ( $id ) {
	if ( isset( $_POST['ss_band'] ) && current_user_can( 'manage_product_terms' ) ) { // phpcs:ignore
		update_term_meta( $id, 'ss_band', sanitize_hex_color( wp_unslash( $_POST['ss_band'] ) ) ?: '' ); // phpcs:ignore
	}
};
add_action( 'created_product_cat', $ss_term_save );
add_action( 'edited_product_cat', $ss_term_save );

add_filter( 'admin_footer_text', fn() => 'Sharp Science' );
add_filter( 'update_footer', '__return_empty_string', 99 );
add_action( 'wp_dashboard_setup', function () {
	global $wp_meta_boxes;
	$wp_meta_boxes['dashboard'] = array();
	wp_add_dashboard_widget( 'ss-welcome', 'Sharp Science', function () {
		printf( '<p><a class="button button-primary" href="%s">Add a product</a> <a class="button" href="%s">Products</a> <a class="button" href="%s">Certificates</a> <a class="button" href="%s">Settings</a></p>', esc_url( admin_url( 'post-new.php?post_type=product' ) ), esc_url( admin_url( 'edit.php?post_type=product' ) ), esc_url( admin_url( 'edit.php?post_type=certificate' ) ), esc_url( admin_url( 'admin.php?page=sharp-science' ) ) );
	} );
}, 999 );

add_action( 'login_enqueue_scripts', function () {
	wp_enqueue_style( 'ss-login', SS_CORE_URL . 'assets/login.css', array(), '1.0.0' );
	$logo = get_theme_file_uri( 'assets/img/logo-horizontal-light.svg' );
	wp_add_inline_style( 'ss-login', ':root{--ss-logo:url(' . esc_url( $logo ) . ')}' );
} );
add_filter( 'login_headerurl', fn() => home_url( '/' ) );
add_filter( 'login_headertext', fn() => 'Sharp Science' );
