<?php
defined( 'ABSPATH' ) || exit;

const SS_CSV_COLUMNS = array( 'name', 'catalogue_no', 'slug', 'category', 'spec', 'format', 'price_aud', 'stock_qty', 'short_description', 'label_band_colour', 'featured', 'best_seller', 'private_full_name' );

/** Maps a product format/spec to a 2.5D packaging render type. */
function ss_format_to_render_type( string $format, string $name, string $spec ): string {
	$f = strtolower( trim( $format ) );
	return match ( true ) {
		str_contains( $f, 'vial' ) => 'vial',
		str_contains( $f, 'spray' ), str_contains( $f, 'nasal' ) => 'spray',
		str_contains( $f, 'capsule' ), str_contains( $f, 'tablet' ), str_contains( $f, 'oral' ) => 'bottle',
		str_contains( $f, 'swab' ), str_contains( $f, 'pack' ) => 'box',
		default => ss_detect_render_type( $name, $spec ),
	};
}

/** Returns a sterile 13-15 word RUO short description for a given format. */
function ss_ruo_short_description( string $format ): string {
	$f = strtolower( trim( $format ) );
	return match ( true ) {
		str_contains( $f, 'spray' ), str_contains( $f, 'nasal' ) => 'Metered laboratory solution for in-vitro research use only. Not for human or animal use.',
		str_contains( $f, 'capsule' ), str_contains( $f, 'tablet' ), str_contains( $f, 'oral' ) => 'Measured reference compound for laboratory research use only. Not for human or animal use.',
		str_contains( $f, 'swab' ) => 'Sterile laboratory preparation swabs for research use only. Not for human or animal use.',
		default => 'Lyophilised powder for laboratory research use only. Not for human or animal use.',
	};
}

/** Checks if a product already exists by SKU, slug, title, or base codename. */
function ss_find_existing_product( string $sku, string $slug, string $name ): int {
	if ( '' !== $sku ) {
		$id = wc_get_product_id_by_sku( $sku );
		if ( $id ) {
			return (int) $id;
		}
	}
	$clean_slug = sanitize_title( $slug ?: $name );
	if ( '' !== $clean_slug ) {
		$post = get_page_by_path( $clean_slug, OBJECT, 'product' );
		if ( $post ) {
			return (int) $post->ID;
		}
	}
	global $wpdb;
	$by_title = (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT ID FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status = 'publish' AND LOWER(post_title) = LOWER(%s) LIMIT 1",
		trim( $name )
	) );
	if ( $by_title ) {
		return $by_title;
	}
	if ( stripos( $name, 'Bacteriostatic Water' ) !== false ) {
		$bac = (int) $wpdb->get_var( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status = 'publish' AND post_title LIKE 'Bacteriostatic Water%' LIMIT 1" );
		if ( $bac ) {
			return $bac;
		}
	}
	return 0;
}

/** Creates or updates products from CSV. When $skip_existing is true, existing products are not overwritten. */
function ss_import_csv( string $path, bool $skip_existing = false ): array {
	$fh = fopen( $path, 'r' );
	if ( ! $fh ) {
		return array( 'created' => 0, 'updated' => 0, 'skipped' => 0, 'excluded' => 0, 'errors' => array( 'The file could not be read.' ) );
	}
	$head = array_map( fn( $h ) => trim( $h, " \t\n\r\0\x0B\xEF\xBB\xBF" ), (array) fgetcsv( $fh, 0, ',', '"', '' ) );
	$res  = array( 'created' => 0, 'updated' => 0, 'skipped' => 0, 'excluded' => 0, 'errors' => array() );
	if ( array_diff( array( 'name', 'catalogue_no', 'category', 'price_aud' ), $head ) ) {
		fclose( $fh );
		$res['errors'][] = 'The file is missing required columns. Export a file first and use it as your template.';
		return $res;
	}
	$line = 1;
	while ( ( $row = fgetcsv( $fh, 0, ',', '"', '' ) ) !== false ) {
		$line++;
		if ( count( $row ) !== count( $head ) ) {
			$res['errors'][] = "Row $line has the wrong number of columns and was skipped.";
			continue;
		}
		$d = array_combine( $head, $row );
		if ( '' === trim( $d['catalogue_no'] ) || '' === trim( $d['name'] ) ) {
			$res['errors'][] = "Row $line needs a name and catalogue number.";
			continue;
		}
		$format = trim( $d['format'] ?? '' );
		$haystack = strtolower( $format . ' ' . $d['name'] . ' ' . ( $d['spec'] ?? '' ) );
		if ( str_contains( $haystack, 'pen cartridge' ) || str_contains( $haystack, 'cartridge refill' ) ) {
			$res['excluded']++;
			continue;
		}
		$id = ss_find_existing_product( trim( $d['catalogue_no'] ), trim( $d['slug'] ?? '' ), trim( $d['name'] ) );
		if ( $id && $skip_existing ) {
			$res['skipped']++;
			continue;
		}
		$prod = $id ? wc_get_product( $id ) : new WC_Product_Simple();
		$prod->set_name( $d['name'] );
		$prod->set_sku( $d['catalogue_no'] );
		if ( ! empty( $d['slug'] ) ) {
			$prod->set_slug( sanitize_title( $d['slug'] ) );
		}
		$prod->set_regular_price( wc_format_decimal( $d['price_aud'] ) );
		$prod->set_manage_stock( true );
		$prod->set_stock_quantity( (int) ( $d['stock_qty'] ?? 0 ) );
		$desc = trim( $d['short_description'] ?? '' ) ?: ss_ruo_short_description( $format );
		$prod->set_short_description( $desc );
		$prod->set_featured( ! empty( $d['featured'] ) && '0' !== $d['featured'] );
		$prod->set_status( 'publish' );
		$prod->set_category_ids( array( ss_ensure_category( $d['category'], $d['label_band_colour'] ?? '' ) ) );
		$pid = $prod->save();
		update_post_meta( $pid, '_ss_spec', $d['spec'] ?? '' );
		update_post_meta( $pid, '_ss_format', $format ?: 'Lyophilised vial' );
		update_post_meta( $pid, '_ss_best_seller', ! empty( $d['best_seller'] ) && '0' !== $d['best_seller'] ? 1 : 0 );
		if ( ! empty( $d['private_full_name'] ) ) {
			update_post_meta( $pid, 'private_full_name', sanitize_text_field( $d['private_full_name'] ) );
		}
		update_post_meta( $pid, '_ss_render_type', ss_format_to_render_type( $format, $d['name'], $d['spec'] ?? '' ) );
		ss_generate_assets( $pid );
		$res[ $id ? 'updated' : 'created' ]++;
	}
	fclose( $fh );
	return $res;
}

function ss_ensure_category( string $name, string $band ): int {
	$name = trim( $name );
	$term = get_term_by( 'name', $name, 'product_cat' );
	if ( ! $term ) {
		$new = wp_insert_term( $name, 'product_cat' );
		$tid = is_wp_error( $new ) ? (int) $new->get_error_data() : (int) $new['term_id'];
		update_term_meta( $tid, 'ss_order', (int) wp_count_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) ) );
	} else {
		$tid = (int) $term->term_id;
	}
	$hex = sanitize_hex_color( $band );
	if ( $hex ) {
		update_term_meta( $tid, 'ss_band', $hex );
	}
	return $tid;
}

function ss_export_rows(): array {
	$rows = array( SS_CSV_COLUMNS );
	foreach ( wc_get_products( array( 'limit' => -1, 'status' => 'publish', 'orderby' => 'date', 'order' => 'ASC' ) ) as $p ) {
		$id    = $p->get_id();
		$terms = get_the_terms( $id, 'product_cat' );
		$rows[] = array(
			$p->get_name(), $p->get_sku(), $p->get_slug(), $terms ? html_entity_decode( $terms[0]->name ) : '',
			get_post_meta( $id, '_ss_spec', true ), get_post_meta( $id, '_ss_format', true ) ?: 'Lyophilised vial',
			$p->get_regular_price(), $p->get_stock_quantity(), $p->get_short_description(),
			get_post_meta( $id, '_ss_band', true ) ?: ss_category_band( $id ), $p->get_featured() ? 1 : 0, (int) get_post_meta( $id, '_ss_best_seller', true ),
			(string) get_post_meta( $id, 'private_full_name', true ),
		);
	}
	return $rows;
}

add_action( 'admin_post_ss_export', function () {
	check_admin_referer( 'ss_export' );
	if ( ! current_user_can( 'edit_products' ) ) {
		wp_die( 'Not allowed.' );
	}
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=sharp-science-products.csv' );
	$out = fopen( 'php://output', 'w' );
	foreach ( ss_export_rows() as $r ) {
		fputcsv( $out, $r, ',', '"', '' );
	}
	exit;
} );

add_action( 'admin_post_ss_import', function () {
	check_admin_referer( 'ss_import' );
	if ( ! current_user_can( 'edit_products' ) ) {
		wp_die( 'Not allowed.' );
	}
	$tmp = $_FILES['ss_csv']['tmp_name'] ?? ''; // phpcs:ignore
	$res = $tmp && is_uploaded_file( $tmp ) ? ss_import_csv( $tmp ) : array( 'created' => 0, 'updated' => 0, 'errors' => array( 'Choose a CSV file first.' ) );
	set_transient( 'ss_import_result', $res, 120 );
	wp_safe_redirect( admin_url( 'admin.php?page=sharp-science' ) );
	exit;
} );
