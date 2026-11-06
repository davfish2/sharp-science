<?php
defined( 'ABSPATH' ) || exit;

/** Every client-editable value lives here. Label, input type and help text drive the settings page. */
function ss_setting_fields(): array {
	return array(
		'Wording'      => array(
			'lab_line'       => array( 'Lab-use line', 'text', 'Research Use Only. Not for human or animal use', 'Shown in the top bar, product pages, checkout and footer.' ),
			'notice_text'    => array( 'Entry notice text', 'textarea', 'Sharp Science supplies trade and professional laboratory customers.', '' ),
			'notice_confirm' => array( 'Entry notice confirmation', 'text', 'I am a trade or professional customer.', '' ),
			'leave_url'      => array( 'Leave button address', 'url', 'https://www.google.com.au/', 'Where the Leave button sends visitors.' ),
			'checkbox_text'  => array( 'Checkout checkbox text', 'text', 'I confirm I am a trade or professional customer and accept the Terms of Sale', '' ),
			'lab_name'       => array( 'Certificate lab name', 'text', 'Janoshik', 'Optional. Added to the trust line when filled in.' ),
		),
		'Partner shop' => array(
			'partner_enabled' => array( 'Show partner note', 'checkbox', 1, 'Needs an address below to appear.' ),
			'partner_url'     => array( 'Partner shop address', 'url', 'https://researchsupplyshop.com', '' ),
			'partner_text'    => array( 'Partner note text', 'text', 'Need research supplies? Visit Research Supply Shop', '' ),
		),
		'Dispatch'     => array(
			'cutoff'            => array( 'Cut-off time', 'time', '12:00', '24-hour time.' ),
			'timezone'          => array( 'Timezone', 'text', 'Australia/Perth', '' ),
			'dispatch_weekends' => array( 'Dispatch on weekends', 'checkbox', 0, '' ),
			'holidays'          => array( 'Public holidays', 'textarea', "2026-12-25\n2026-12-28\n2027-01-01\n2027-01-26", 'One date per line, as YYYY-MM-DD. No dispatch on these days.' ),
		),
		'Shipping'     => array(
			'free_threshold' => array( 'Free shipping threshold (AUD)', 'number', 200, 'Includes GST.' ),
		),
		'Discount'     => array(
			'discount_enabled' => array( 'Show multi-item discount', 'checkbox', 0, 'Off by default.' ),
			'discount_tiers'   => array( 'Discount tiers', 'text', '3:5, 6:10, 10:15', 'Items:percent, separated by commas.' ),
		),
		'Business'     => array(
			'abn'   => array( 'ABN', 'text', '', 'Shown in the footer when filled in.' ),
			'email' => array( 'Contact email', 'text', '', '' ),
			'phone' => array( 'Contact phone', 'text', '', '' ),
		),
	);
}

function ss_setting_defaults(): array {
	$out = array();
	foreach ( ss_setting_fields() as $group ) {
		foreach ( $group as $key => $field ) {
			$out[ $key ] = $field[2];
		}
	}
	return $out;
}

function ss_get( string $key ) {
	static $values = null;
	if ( null === $values || doing_action( 'update_option_ss_settings' ) ) {
		$values = wp_parse_args( (array) get_option( 'ss_settings', array() ), ss_setting_defaults() );
	}
	return $values[ $key ] ?? null;
}
add_action( 'update_option_ss_settings', fn() => ss_get( 'lab_line' ) );

function ss_trust_line(): string {
	$lab = trim( (string) ss_get( 'lab_name' ) );
	return $lab ? 'COA verified by ' . $lab : 'COA verified';
}

function ss_partner_note(): string {
	if ( ! ss_get( 'partner_enabled' ) || ! ss_get( 'partner_url' ) ) {
		return '';
	}
	return sprintf( '<p class="partner-note"><a href="%s" target="_blank" rel="noopener">%s</a></p>', esc_url( ss_get( 'partner_url' ) ), esc_html( ss_get( 'partner_text' ) ) );
}

function ss_free_threshold(): float {
	return (float) ss_get( 'free_threshold' );
}
