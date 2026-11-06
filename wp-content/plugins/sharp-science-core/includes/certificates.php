<?php
defined( 'ABSPATH' ) || exit;

function ss_cert_fields(): array {
	return array(
		'product'    => array( 'Product', 'product' ),
		'batch'      => array( 'Batch number', 'text' ),
		'issue_date' => array( 'Issue date', 'date' ),
		'standard'   => array( 'Standard', 'text' ),
		'result'     => array( 'Result', 'text' ),
		'lab'        => array( 'Laboratory', 'text' ),
		'pdf'        => array( 'PDF address', 'url' ),
		'verify'     => array( 'Verification address', 'url' ),
	);
}

add_action( 'init', function () {
	register_post_type( 'certificate', array(
		'label'        => 'Certificates',
		'labels'       => array( 'singular_name' => 'Certificate', 'add_new_item' => 'Add certificate', 'edit_item' => 'Edit certificate' ),
		'public'       => false,
		'show_ui'      => true,
		'show_in_menu' => true,
		'menu_icon'    => 'dashicons-media-text',
		'menu_position' => 56,
		'supports'     => array( 'title' ),
	) );
} );

add_action( 'add_meta_boxes_certificate', function () {
	add_meta_box( 'ss-cert', 'Certificate details', function ( WP_Post $post ) {
		wp_nonce_field( 'ss_cert', 'ss_cert_nonce' );
		echo '<div class="ss-panel">';
		foreach ( ss_cert_fields() as $key => [ $label, $type ] ) {
			$v = get_post_meta( $post->ID, "_ss_$key", true );
			printf( '<p><label for="ss_c_%1$s">%2$s</label>', esc_attr( $key ), esc_html( $label ) );
			if ( 'product' === $type ) {
				printf( '<select id="ss_c_%s" name="ss_c[%s]"><option value="">Choose a product</option>', esc_attr( $key ), esc_attr( $key ) );
				foreach ( wc_get_products( array( 'limit' => -1, 'orderby' => 'title', 'order' => 'ASC', 'status' => 'publish' ) ) as $p ) {
					printf( '<option value="%d"%s>%s (%s)</option>', $p->get_id(), selected( (int) $v, $p->get_id(), false ), esc_html( $p->get_name() ), esc_html( $p->get_sku() ) );
				}
				echo '</select></p>';
				continue;
			}
			printf( '<input id="ss_c_%1$s" name="ss_c[%1$s]" type="%2$s" value="%3$s"></p>', esc_attr( $key ), esc_attr( $type ), esc_attr( $v ) );
		}
		echo '</div>';
	}, 'certificate', 'normal', 'high' );
} );

add_action( 'save_post_certificate', function ( int $id ) {
	if ( ! isset( $_POST['ss_cert_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['ss_cert_nonce'] ), 'ss_cert' ) || ! current_user_can( 'edit_post', $id ) ) {
		return;
	}
	$in = wp_unslash( $_POST['ss_c'] ?? array() ); // phpcs:ignore
	foreach ( ss_cert_fields() as $key => [ , $type ] ) {
		$v = $in[ $key ] ?? '';
		update_post_meta( $id, "_ss_$key", 'url' === $type ? esc_url_raw( $v ) : sanitize_text_field( $v ) );
	}
	$product = (int) ( $in['product'] ?? 0 );
	if ( $product ) {
		ss_generate_assets( $product ); // The label batch and QR follow the newest certificate.
	}
} );

add_filter( 'manage_certificate_posts_columns', fn( $c ) => array( 'cb' => $c['cb'], 'title' => 'Title', 'ss_batch' => 'Batch', 'ss_product' => 'Product', 'ss_date' => 'Issue date' ) );
add_action( 'manage_certificate_posts_custom_column', function ( $col, $id ) {
	if ( 'ss_batch' === $col ) {
		echo esc_html( get_post_meta( $id, '_ss_batch', true ) );
	} elseif ( 'ss_date' === $col ) {
		echo esc_html( get_post_meta( $id, '_ss_issue_date', true ) );
	} elseif ( 'ss_product' === $col ) {
		echo esc_html( get_the_title( (int) get_post_meta( $id, '_ss_product', true ) ) );
	}
}, 10, 2 );

/** Certificates as plain rows, newest first. Filters: q, batch, category (term slug), product. */
function ss_certificates( array $args = array() ): array {
	$posts = get_posts( array( 'post_type' => 'certificate', 'post_status' => 'publish', 'numberposts' => -1, 'meta_key' => '_ss_issue_date', 'orderby' => 'meta_value', 'order' => 'DESC' ) );
	$rows  = array();
	foreach ( $posts as $post ) {
		$m   = fn( $k ) => (string) get_post_meta( $post->ID, "_ss_$k", true );
		$pid = (int) $m( 'product' );
		$row = array(
			'product_id' => $pid,
			'product'    => $pid ? get_the_title( $pid ) : $post->post_title,
			'sku'        => $pid ? (string) get_post_meta( $pid, '_sku', true ) : '',
			'url'        => $pid ? get_permalink( $pid ) : '',
			'batch'      => $m( 'batch' ),
			'date'       => $m( 'issue_date' ),
			'standard'   => $m( 'standard' ),
			'result'     => $m( 'result' ),
			'lab'        => $m( 'lab' ),
			'pdf'        => $m( 'pdf' ),
			'verify'     => $m( 'verify' ),
		);
		if ( ! empty( $args['product'] ) && (int) $args['product'] !== $pid ) {
			continue;
		}
		if ( ! empty( $args['batch'] ) && 0 !== strcasecmp( $args['batch'], $row['batch'] ) ) {
			continue;
		}
		if ( ! empty( $args['category'] ) && ! has_term( $args['category'], 'product_cat', $pid ) ) {
			continue;
		}
		if ( ! empty( $args['q'] ) && false === stripos( $row['product'] . ' ' . $row['batch'] . ' ' . $row['sku'], $args['q'] ) ) {
			continue;
		}
		$rows[] = $row;
	}
	return $rows;
}

function ss_product_batch( int $id ): string {
	$rows = ss_certificates( array( 'product' => $id ) );
	return $rows ? $rows[0]['batch'] : 'B26-' . ( 1000 + $id );
}
