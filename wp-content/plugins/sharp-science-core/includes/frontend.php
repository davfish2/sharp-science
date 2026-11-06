<?php
defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_script( 'ss-core', SS_CORE_URL . 'assets/core.js', array(), '1.0.3', array( 'strategy' => 'defer', 'in_footer' => true ) );
	wp_add_inline_script( 'ss-core', 'window.SS_CORE=' . wp_json_encode( array(
		'cutoff'   => ss_get( 'cutoff' ),
		'timezone' => ss_get( 'timezone' ),
		'weekends' => (bool) ss_get( 'dispatch_weekends' ),
		'holidays' => ss_holidays(),
	) ) . ';', 'before' );
} );

/** Rendered hidden and revealed by script, so crawlers and no-JS visitors reach the content. */
add_action( 'wp_footer', function () {
	$terms = get_page_by_path( 'terms-of-sale' );
	?>
	<div id="entry-notice" class="notice-gate" role="dialog" aria-modal="true" aria-labelledby="entry-title" hidden>
		<div class="notice-gate__panel">
			<?php if ( function_exists( 'ss_logo' ) ) { echo ss_logo( 'stacked-light' ); } // phpcs:ignore ?>
			<p id="entry-title" class="notice-gate__text"><?php echo esc_html( ss_get( 'notice_text' ) ); ?></p>
			<p class="notice-gate__confirm"><?php echo esc_html( ss_get( 'notice_confirm' ) ); ?></p>
			<div class="notice-gate__actions">
				<button type="button" class="btn" data-enter>Enter</button>
				<a class="btn btn--ghost" href="<?php echo esc_url( ss_get( 'leave_url' ) ); ?>" rel="nofollow">Leave</a>
			</div>
			<?php if ( $terms ) : ?>
				<a class="notice-gate__terms" href="<?php echo esc_url( get_permalink( $terms ) ); ?>">Terms of Sale</a>
			<?php endif; ?>
		</div>
	</div>
	<?php
}, 5 );
