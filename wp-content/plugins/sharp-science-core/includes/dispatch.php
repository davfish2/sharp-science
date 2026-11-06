<?php
defined( 'ABSPATH' ) || exit;

function ss_holidays(): array {
	return array_values( array_filter( array_map( 'trim', preg_split( '/\R/', (string) ss_get( 'holidays' ) ) ) ) );
}

function ss_is_dispatch_day( DateTimeImmutable $d ): bool {
	if ( ! ss_get( 'dispatch_weekends' ) && (int) $d->format( 'N' ) >= 6 ) {
		return false;
	}
	return ! in_array( $d->format( 'Y-m-d' ), ss_holidays(), true );
}

/** The server-rendered message. assets/core.js repeats this logic so it stays live on cached pages. */
function ss_dispatch_message( ?DateTimeImmutable $now = null ): string {
	$tz  = new DateTimeZone( (string) ss_get( 'timezone' ) );
	$now = ( $now ?? new DateTimeImmutable( 'now' ) )->setTimezone( $tz );
	[ $h, $m ] = array_map( 'intval', explode( ':', (string) ss_get( 'cutoff' ) ) );
	$cut = $now->setTime( $h, $m );

	if ( ss_is_dispatch_day( $now ) && $now < $cut ) {
		$mins = (int) ceil( ( $cut->getTimestamp() - $now->getTimestamp() ) / 60 );
		return sprintf( 'Order in the next %dh %dm for same-day dispatch', intdiv( $mins, 60 ), $mins % 60 );
	}
	$next = $now->modify( '+1 day' );
	for ( $i = 0; $i < 14 && ! ss_is_dispatch_day( $next ); $i++ ) {
		$next = $next->modify( '+1 day' );
	}
	$days = (int) $now->setTime( 0, 0 )->diff( $next->setTime( 0, 0 ) )->days;
	return 'Order now, dispatched ' . ( 1 === $days ? 'tomorrow' : $next->format( 'l' ) );
}
