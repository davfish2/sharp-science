<?php
defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

/** Re-runnable store setup and demo data. */
class SS_CLI {
	/** Configures WooCommerce, pages, tax, shipping and permalinks. */
	public function setup( $args, $assoc ) {
		$pages_dir = $assoc['pages'] ?? WP_CONTENT_DIR . '/../sharp-data/pages';
		foreach ( array(
			'blogname' => 'Sharp Science', 'blogdescription' => '', 'timezone_string' => 'Australia/Perth', 'date_format' => 'j F Y', 'WPLANG' => 'en_AU',
			'permalink_structure' => '/%postname%/', 'default_comment_status' => 'closed', 'users_can_register' => 0,
			'woocommerce_default_country' => 'AU:WA', 'woocommerce_currency' => 'AUD', 'woocommerce_currency_pos' => 'left', 'woocommerce_price_thousand_sep' => ',', 'woocommerce_price_decimal_sep' => '.', 'woocommerce_price_num_decimals' => 2,
			'woocommerce_allowed_countries' => 'specific', 'woocommerce_specific_allowed_countries' => array( 'AU' ), 'woocommerce_ship_to_countries' => 'specific', 'woocommerce_specific_ship_to_countries' => array( 'AU' ), 'woocommerce_default_customer_address' => 'base',
			'woocommerce_calc_taxes' => 'yes', 'woocommerce_prices_include_tax' => 'yes', 'woocommerce_tax_display_shop' => 'incl', 'woocommerce_tax_display_cart' => 'incl', 'woocommerce_tax_total_display' => 'itemized', 'woocommerce_tax_based_on' => 'base', 'woocommerce_price_display_suffix' => '',
			'woocommerce_enable_guest_checkout' => 'yes', 'woocommerce_enable_checkout_login_reminder' => 'no', 'woocommerce_enable_signup_and_login_from_checkout' => 'no', 'woocommerce_enable_myaccount_registration' => 'no',
			'woocommerce_enable_reviews' => 'no', 'woocommerce_cart_redirect_after_add' => 'no', 'woocommerce_enable_ajax_add_to_cart' => 'no', 'woocommerce_weight_unit' => 'kg', 'woocommerce_dimension_unit' => 'mm',
			'woocommerce_coming_soon' => 'no', 'woocommerce_store_pages_only' => 'no', 'woocommerce_feature_order_attribution_enabled' => 'no', 'woocommerce_allow_tracking' => 'no', 'woocommerce_show_marketplace_suggestions' => 'no', 'woocommerce_analytics_enabled' => 'no', 'woocommerce_task_list_hidden' => 'yes', 'woocommerce_task_list_complete' => 'yes',
			'woocommerce_store_address' => '', 'woocommerce_cod_settings' => array( 'enabled' => 'yes', 'title' => 'Pay on invoice (demo)', 'description' => 'Demo payment method. No charge is made.', 'instructions' => '' ),
			'woocommerce_permalinks' => array( 'product_base' => '/product', 'category_base' => 'shop', 'tag_base' => 'product-tag', 'attribute_base' => '', 'use_verbose_page_rules' => false ),
		) as $k => $v ) {
			update_option( $k, $v );
		}

		$titles = array( 'terms-of-sale' => 'Terms of Sale', 'shipping' => 'Shipping', 'returns' => 'Returns', 'privacy' => 'Privacy', 'about' => 'About', 'contact' => 'Contact', 'faq' => 'FAQ', 'certificates' => 'Certificates' );
		foreach ( $titles as $slug => $title ) {
			$file    = "$pages_dir/$slug.html";
			$content = file_exists( $file ) ? file_get_contents( $file ) : '';
			$content = preg_replace( '#<h2>' . preg_quote( $title, '#' ) . '</h2>\s*#', '', $content, 1 );
			$page    = get_page_by_path( $slug );
			$data    = array( 'post_title' => $title, 'post_name' => $slug, 'post_type' => 'page', 'post_status' => 'publish', 'post_content' => $content );
			if ( $page ) {
				$data['ID'] = $page->ID;
			}
			wp_insert_post( $data );
		}
		update_option( 'wp_page_for_privacy_policy', get_page_by_path( 'privacy' )->ID );
		update_option( 'woocommerce_terms_page_id', get_page_by_path( 'terms-of-sale' )->ID );

		$home = get_page_by_path( 'home' ) ?: get_post( wp_insert_post( array( 'post_title' => 'Home', 'post_name' => 'home', 'post_type' => 'page', 'post_status' => 'publish' ) ) );
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home->ID );

		WC_Install::create_pages();
		$cart = wc_get_page_id( 'cart' );
		wp_update_post( array( 'ID' => $cart, 'post_content' => '[woocommerce_cart]' ) );
		// The required confirmation checkbox replaces the default terms sentence.
		$checkout = get_post( wc_get_page_id( 'checkout' ) );
		$content  = preg_replace( '#<!-- wp:woocommerce/checkout-terms-block.*?(/-->|<!-- /wp:woocommerce/checkout-terms-block -->)\s*#s', '', $checkout->post_content );
		wp_update_post( array( 'ID' => $checkout->ID, 'post_content' => wp_slash( $content ) ) );
		foreach ( array( 'sample-page', 'hello-world' ) as $slug ) {
			$p = get_page_by_path( $slug, OBJECT, array( 'page', 'post' ) );
			if ( $p ) {
				wp_delete_post( $p->ID, true );
			}
		}

		global $wpdb;
		$rate = $wpdb->get_var( "SELECT tax_rate_id FROM {$wpdb->prefix}woocommerce_tax_rates WHERE tax_rate_name='GST' LIMIT 1" );
		if ( ! $rate ) {
			WC_Tax::_insert_tax_rate( array( 'tax_rate_country' => 'AU', 'tax_rate_state' => '', 'tax_rate' => '10.0000', 'tax_rate_name' => 'GST', 'tax_rate_priority' => 1, 'tax_rate_compound' => 0, 'tax_rate_shipping' => 1, 'tax_rate_order' => 0, 'tax_rate_class' => '' ) );
		}

		$zone = null;
		foreach ( WC_Shipping_Zones::get_zones() as $z ) {
			if ( 'Australia' === $z['zone_name'] ) {
				$zone = new WC_Shipping_Zone( $z['id'] );
			}
		}
		if ( ! $zone ) {
			$zone = new WC_Shipping_Zone();
			$zone->set_zone_name( 'Australia' );
			$zone->save();
			$zone->add_location( 'AU', 'country' );
			$zone->save();
			$zone->add_shipping_method( 'flat_rate' );
			$zone->add_shipping_method( 'free_shipping' );
		}
		foreach ( $zone->get_shipping_methods() as $m ) {
			if ( 'flat_rate' === $m->id ) { // Entered excluding GST: 13.59 + 10% = $14.95 at checkout.
				update_option( "woocommerce_flat_rate_{$m->instance_id}_settings", array( 'title' => 'Express Post', 'tax_status' => 'taxable', 'cost' => '13.59' ) );
			} elseif ( 'free_shipping' === $m->id ) {
				update_option( "woocommerce_free_shipping_{$m->instance_id}_settings", array( 'title' => 'Free Express Post', 'requires' => '' ) );
			}
		}
		WC_Cache_Helper::get_transient_version( 'shipping', true );
		delete_transient( 'wc_attribute_taxonomies' );
		flush_rewrite_rules( false );
		WP_CLI::success( 'Store configured.' );
	}

	/**
	 * Imports products from a CSV.
	 *
	 * <file>
	 * : Path to the CSV.
	 */
	public function import( $args ) {
		$res = ss_import_csv( $args[0] );
		foreach ( $res['errors'] as $e ) {
			WP_CLI::warning( $e );
		}
		WP_CLI::success( sprintf( '%d created, %d updated.', $res['created'], $res['updated'] ) );
	}

	/** Creates six demo certificates. Every record is marked as sample data. */
	public function certificates() {
		$demo = array(
			array( 'SS-V10', 'B26-1015', '2026-09-14', 'ISO 3585 Borosilicate 3.3' ),
			array( 'SS-V05', 'B26-1008', '2026-09-02', 'ISO 3585 Borosilicate 3.3' ),
			array( 'SS-F100', 'B26-1029', '2026-08-21', 'ISO 1042 Class A' ),
			array( 'SS-G100', 'B26-1036', '2026-08-11', 'ISO 4788 Class A' ),
			array( 'SS-B250', 'B26-1043', '2026-07-30', 'ISO 3819' ),
			array( 'SS-P1000', 'B26-1057', '2026-07-16', 'ISO 8655' ),
		);
		$u   = wp_upload_dir();
		$dir = $u['basedir'] . '/sharp-science/certificates';
		wp_mkdir_p( $dir );
		foreach ( $demo as [ $sku, $batch, $date, $standard ] ) {
			$pid = wc_get_product_id_by_sku( $sku );
			if ( ! $pid ) {
				continue;
			}
			$found = get_posts( array( 'post_type' => 'certificate', 'numberposts' => 1, 'fields' => 'ids', 'meta_key' => '_ss_batch', 'meta_value' => $batch ) );
			$id    = wp_insert_post( array( 'ID' => $found[0] ?? 0, 'post_type' => 'certificate', 'post_status' => 'publish', 'post_title' => "$sku $batch" ) );
			$meta  = array( 'product' => $pid, 'batch' => $batch, 'issue_date' => $date, 'standard' => $standard, 'result' => 'Sample data', 'lab' => 'Demo Laboratory', 'pdf' => $u['baseurl'] . "/sharp-science/certificates/$batch.pdf", 'verify' => home_url( '/certificates/?batch=' . $batch ) );
			foreach ( $meta as $k => $v ) {
				update_post_meta( $id, "_ss_$k", $v );
			}
			file_put_contents( "$dir/$batch.pdf", ss_cert_pdf( array( 'product' => get_the_title( $pid ), 'batch' => $batch, 'date' => $date, 'standard' => $standard, 'result' => 'Sample data', 'lab' => 'Demo Laboratory' ) ) );
			ss_generate_assets( $pid );
		}
		WP_CLI::success( 'Demo certificates created.' );
	}

	/** Regenerates labels and renders for every product. */
	public function generate() {
		$ids = wc_get_products( array( 'limit' => -1, 'return' => 'ids', 'status' => 'publish' ) );
		array_walk( $ids, fn( $id ) => ss_generate_assets( $id ) );
		WP_CLI::success( count( $ids ) . ' products regenerated.' );
	}
}
WP_CLI::add_command( 'sharp', 'SS_CLI' );
