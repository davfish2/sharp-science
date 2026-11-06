<?php
defined( 'ABSPATH' ) || exit;
if ( ! $notices ) { return; }
foreach ( $notices as $n ) {
	printf( '<div class="alert alert--success" role="status">%s</div>', wc_kses_notice( $n['notice'] ) );
}
