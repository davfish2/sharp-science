<?php
/**
 * Plugin Name: Sharp Science Core
 * Description: Store logic for Sharp Science: settings, dispatch, labels, certificates and imports.
 * Version: 1.0.0
 * Requires PHP: 8.1
 */
defined( 'ABSPATH' ) || exit;

define( 'SS_CORE_FILE', __FILE__ );
define( 'SS_CORE_DIR', __DIR__ );
define( 'SS_CORE_URL', plugin_dir_url( __FILE__ ) );

foreach ( array( 'settings', 'cleanup', 'dispatch', 'frontend', 'checkout', 'shipping', 'qr', 'label', 'render', 'product-panel', 'certificates', 'csv', 'seo', 'admin', 'setup' ) as $ss_file ) {
	require SS_CORE_DIR . "/includes/{$ss_file}.php";
}

register_activation_hook( __FILE__, 'flush_rewrite_rules' );
