<?php
defined( 'ABSPATH' ) || exit;

/** 2.5D product scenes as SVG. The admin browser converts them to WebP (see assets/admin.js). */
function ss_render_types(): array {
	return array( 'auto' => 'Automatic', 'vial' => 'Clear vial', 'amber' => 'Amber vial', 'spray' => 'Nasal spray', 'bottle' => 'Capsule / Tablet bottle', 'box' => 'Clinical pack / Box', 'beaker' => 'Beaker', 'erlenmeyer' => 'Conical flask', 'volumetric' => 'Volumetric flask', 'cylinder' => 'Cylinder', 'pipette' => 'Pipette' );
}

function ss_detect_render_type( string $name, string $spec = '' ): string {
	$n = strtolower( $name . ' ' . $spec );
	return match ( true ) {
		str_contains( $n, 'amber' ) => 'amber',
		str_contains( $n, 'spray' ), str_contains( $n, 'nasal' ) => 'spray',
		str_contains( $n, 'capsule' ), str_contains( $n, 'tablet' ), str_contains( $n, 'oral' ), str_contains( $n, 'solution' ), str_contains( $n, 'bottle' ) => 'bottle',
		str_contains( $n, 'swab' ), str_contains( $n, 'pack' ), str_contains( $n, 'rack' ) => 'box',
		str_contains( $n, 'beaker' ) => 'beaker',
		str_contains( $n, 'erlenmeyer' ), str_contains( $n, 'conical' ) => 'erlenmeyer',
		str_contains( $n, 'volumetric' ) => 'volumetric',
		str_contains( $n, 'cylinder' ) => 'cylinder',
		str_contains( $n, 'pipette' ) && ! str_contains( $n, 'tip' ) => 'pipette',
		default => 'vial',
	};
}

function ss_scene_defs(): string {
	return '<linearGradient id="bg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#ECE7DE"/><stop offset="1" stop-color="#DCE6E8"/></linearGradient>
<linearGradient id="glass" x1="0" x2="1"><stop offset="0" stop-color="#7F949A" stop-opacity=".62"/><stop offset=".07" stop-color="#C9D6D9" stop-opacity=".42"/><stop offset=".22" stop-color="#FFF" stop-opacity=".30"/><stop offset=".5" stop-color="#FFF" stop-opacity=".12"/><stop offset=".8" stop-color="#FFF" stop-opacity=".24"/><stop offset=".94" stop-color="#B7C7CB" stop-opacity=".44"/><stop offset="1" stop-color="#71878E" stop-opacity=".66"/></linearGradient>
<linearGradient id="amber" x1="0" x2="1"><stop offset="0" stop-color="#3F2410" stop-opacity=".94"/><stop offset=".12" stop-color="#7A4A1E" stop-opacity=".88"/><stop offset=".3" stop-color="#A96E31" stop-opacity=".78"/><stop offset=".55" stop-color="#8C5825" stop-opacity=".82"/><stop offset=".88" stop-color="#6A3F19" stop-opacity=".9"/><stop offset="1" stop-color="#351D0C" stop-opacity=".96"/></linearGradient>
<linearGradient id="metal" x1="0" x2="1"><stop offset="0" stop-color="#0E1013"/><stop offset=".18" stop-color="#3A3E46"/><stop offset=".34" stop-color="#6A707A"/><stop offset=".5" stop-color="#2B2E35"/><stop offset=".85" stop-color="#16181D"/><stop offset="1" stop-color="#08090B"/></linearGradient>
<linearGradient id="poly" x1="0" x2="1"><stop offset="0" stop-color="#C7CBCC"/><stop offset=".16" stop-color="#F1F1EE"/><stop offset=".38" stop-color="#FFF"/><stop offset=".75" stop-color="#ECECE8"/><stop offset="1" stop-color="#B8BDBF"/></linearGradient>
<linearGradient id="wrap" x1="0" x2="1"><stop offset="0" stop-color="#16181D" stop-opacity=".30"/><stop offset=".1" stop-color="#16181D" stop-opacity=".07"/><stop offset=".3" stop-color="#FFF" stop-opacity=".10"/><stop offset=".62" stop-color="#16181D" stop-opacity="0"/><stop offset=".92" stop-color="#16181D" stop-opacity=".09"/><stop offset="1" stop-color="#16181D" stop-opacity=".32"/></linearGradient>
<linearGradient id="face" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#FFF"/><stop offset="1" stop-color="#EFEDE7"/></linearGradient>
<linearGradient id="side" x1="0" x2="1"><stop offset="0" stop-color="#D4D2CB"/><stop offset="1" stop-color="#BFBEB8"/></linearGradient>
<filter id="soft" x="-30%" y="-200%" width="160%" height="500%"><feGaussianBlur stdDeviation="34"/></filter>
<filter id="tight" x="-30%" y="-200%" width="160%" height="500%"><feGaussianBlur stdDeviation="9"/></filter>';
}

const SS_CX    = 800;
const SS_FLOOR = 1370;
const SS_GLASS = 'fill="url(#glass)" stroke="#70858C" stroke-opacity=".55" stroke-width="3"';

function ss_sc_shadow( float $w, float $x = SS_CX ): string {
	$f = SS_FLOOR;
	return sprintf( '<ellipse cx="%s" cy="%s" rx="%s" ry="46" fill="#3B4A50" opacity=".20" filter="url(#soft)"/><ellipse cx="%s" cy="%s" rx="%s" ry="13" fill="#20282C" opacity=".36" filter="url(#tight)"/>', $x + 30, $f + 6, $w * .6, $x, $f, $w * .5 );
}
function ss_sc_hl( float $x, float $y, float $w, float $h, float $o = .55 ): string {
	return sprintf( '<rect x="%s" y="%s" width="%s" height="%s" rx="%s" fill="#FFF" opacity="%s"/>', $x, $y, $w, $h, $w / 2, $o );
}
function ss_sc_label( array $p, float $x, float $y, float $w, bool $wrap = true, int $rotate = 0 ): string {
	$lw = $p['label_w'];
	$lh = $p['label_h'];
	$h  = $w * $lh / $lw;
	$o  = sprintf( '<svg x="%s" y="%s" width="%s" height="%s" viewBox="0 0 %s %s">%s</svg>', $x, $y, $w, $h, $lw, $lh, ss_label_inner( $p, $lw, $lh ) );
	if ( $wrap ) {
		$o .= sprintf( '<rect x="%s" y="%s" width="%s" height="%s" fill="url(#wrap)"/>', $x, $y, $w, $h );
	}
	return $rotate ? sprintf( '<g transform="rotate(%d %s %s)">%s</g>', $rotate, $x + $w / 2, $y + $h / 2, $o ) : $o;
}
function ss_sc_grads( float $x, float $y0, int $n, float $step ): string {
	$o = '';
	for ( $i = 0; $i < $n; $i++ ) {
		$o .= sprintf( '<rect x="%s" y="%s" width="%d" height="3" fill="#16181D" opacity=".55"/>', $x, $y0 + $i * $step, $i % 5 ? 26 : 52 );
	}
	return $o;
}

function ss_sc_vial( array $p, string $fill ): string {
	$ml = (float) $p['spec'];
	$H  = array( 2 => 560, 5 => 730, 10 => 900, 20 => 960 )[ (int) $ml ] ?? 900;
	$W  = 540; $c = SS_CX; $bot = SS_FLOOR; $x = $c - $W / 2; $top = $bot - $H; $nw = 300; $nx = $c - $nw / 2; $neck = $top - 92; $r = 44; $xr = $x + $W; $nr = $nx + $nw;
	$a = $top + 92; $b = $top + 30; $d = $top + 34; $e = $neck + 54; $br = $bot - $r; $xi = $x + $r; $xo = $xr - $r;
	$body = "M$x $a C$x $b " . ( $nx - 6 ) . " $d $nx $e L$nx $neck L$nr $neck L$nr $e C" . ( $nr + 6 ) . " $d $xr $b $xr $a L$xr $br Q$xr $bot $xo $bot L$xi $bot Q$x $bot $x $br Z";
	$cap  = 'M' . ( $nx - 6 ) . ' ' . ( $neck - 52 ) . ' L' . ( $nx + 8 ) . ' ' . ( $neck - 142 ) . ' Q' . ( $nx + 10 ) . ' ' . ( $neck - 154 ) . ' ' . ( $nx + 24 ) . ' ' . ( $neck - 154 ) . ' L' . ( $nr - 24 ) . ' ' . ( $neck - 154 ) . ' Q' . ( $nr - 10 ) . ' ' . ( $neck - 154 ) . ' ' . ( $nr - 8 ) . ' ' . ( $neck - 142 ) . ' L' . ( $nr + 6 ) . ' ' . ( $neck - 52 ) . ' Z';
	$ring = sprintf( 'x="%s" y="%s" width="%s" height="106" rx="10"', $nx - 26, $neck - 52, $nw + 52 );
	return ss_sc_shadow( $W ) . "<path d=\"$body\" fill=\"url(#$fill)\" stroke=\"#70858C\" stroke-opacity=\".5\" stroke-width=\"3\"/>"
		. sprintf( '<rect x="%s" y="%s" width="%s" height="14" rx="7" fill="#FFF" opacity=".28"/>', $x + 14, $bot - 30, $W - 28 )
		. ss_sc_hl( $x + 58, $top + 120, 24, $H - 210, 'amber' === $fill ? .28 : .6 ) . ss_sc_hl( $xr - 62, $top + 150, 10, $H - 260, .3 )
		. "<rect $ring fill=\"#A9AFB4\"/><rect $ring fill=\"url(#wrap)\"/>"
		. sprintf( '<rect x="%s" y="%s" width="%s" height="16" rx="6" fill="#81878C"/>', $nx - 26, $neck + 38, $nw + 52 )
		. "<path d=\"$cap\" fill=\"url(#metal)\"/>" . ss_sc_label( $p, $x + 2, $bot - min( $H * .62, 420 ), $W - 4 );
}

function ss_sc_beaker( array $p ): string {
	$W = 780; $H = 860; $x = SS_CX - $W / 2; $f = SS_FLOOR; $top = $f - $H; $xr = $x + $W;
	$d = 'M' . ( $x - 26 ) . " $top Q" . ( $x - 4 ) . ' ' . ( $top + 2 ) . ' ' . ( $x + 6 ) . ' ' . ( $top + 34 ) . ' L' . ( $x + 10 ) . ' ' . ( $f - 50 ) . ' Q' . ( $x + 12 ) . " $f " . ( $x + 60 ) . " $f L" . ( $xr - 60 ) . " $f Q" . ( $xr - 12 ) . " $f " . ( $xr - 10 ) . ' ' . ( $f - 50 ) . ' L' . ( $xr - 4 ) . ' ' . ( $top + 8 ) . ' Q' . ( $xr + 2 ) . " $top " . ( $xr + 14 ) . " $top Z";
	return ss_sc_shadow( $W ) . "<path d=\"$d\" " . SS_GLASS . '/>'
		. sprintf( '<path d="M%s %s L%s %s" stroke="#FFF" stroke-opacity=".9" stroke-width="7" stroke-linecap="round"/>', $x - 26, $top, $xr + 14, $top )
		. sprintf( '<rect x="%s" y="%s" width="%s" height="12" rx="6" fill="#FFF" opacity=".3"/>', $x + 22, $f - 34, $W - 44 )
		. ss_sc_hl( $x + 54, $top + 70, 22, $H - 170 ) . ss_sc_grads( $xr - 118, $top + 90, 11, 30 ) . ss_sc_label( $p, SS_CX - 330, $f - 400, 640 );
}

function ss_sc_erlenmeyer( array $p ): string {
	$B = 860; $nw = 240; $c = SS_CX; $f = SS_FLOOR; $top = $f - 1090; $sh = $f - 800; $l = $c - $nw / 2; $r = $c + $nw / 2; $bl = $c - $B / 2; $br = $c + $B / 2;
	$d = "M$l $top L$l " . ( $sh - 40 ) . ' L' . ( $bl + 8 ) . ' ' . ( $f - 70 ) . ' Q' . ( $bl - 8 ) . " $f " . ( $bl + 74 ) . " $f L" . ( $br - 74 ) . " $f Q" . ( $br + 8 ) . " $f " . ( $br - 8 ) . ' ' . ( $f - 70 ) . " L$r " . ( $sh - 40 ) . " L$r $top Z";
	return ss_sc_shadow( $B ) . "<path d=\"$d\" " . SS_GLASS . '/>'
		. sprintf( '<rect x="%s" y="%s" width="%s" height="34" rx="14" fill="#FFF" opacity=".78" stroke="#70858C" stroke-opacity=".4" stroke-width="2"/>', $l - 18, $top - 16, $nw + 36 )
		. ss_sc_hl( $l + 26, $top + 50, 16, 280 ) . ss_sc_label( $p, $c - 305, $f - 340, 610 );
}

function ss_sc_volumetric( array $p ): string {
	$R = 400; $c = SS_CX; $f = SS_FLOOR; $cy = $f - $R + 24; $nw = 128; $top = 240; $l = $c - $nw / 2; $r = $c + $nw / 2;
	$d = "M$l $top L$l " . ( $cy - $R + 50 ) . " C$l " . ( $cy - $R + 150 ) . ' ' . ( $c - $R ) . ' ' . ( $cy - 210 ) . ' ' . ( $c - $R ) . ' ' . ( $cy + 60 ) . ' C' . ( $c - $R ) . ' ' . ( $f - 40 ) . ' ' . ( $c - 280 ) . " $f " . ( $c - 200 ) . " $f L" . ( $c + 200 ) . " $f C" . ( $c + 280 ) . " $f " . ( $c + $R ) . ' ' . ( $f - 40 ) . ' ' . ( $c + $R ) . ' ' . ( $cy + 60 ) . ' C' . ( $c + $R ) . ' ' . ( $cy - 210 ) . " $r " . ( $cy - $R + 150 ) . " $r " . ( $cy - $R + 50 ) . " L$r $top Z";
	return ss_sc_shadow( $R * 1.5 ) . "<path d=\"$d\" " . SS_GLASS . '/>'
		. sprintf( '<rect x="%s" y="%s" width="%s" height="5" fill="#16181D" opacity=".7"/>', $l, $top + 230, $nw )
		. sprintf( '<rect x="%s" y="%s" width="%s" height="30" rx="12" fill="#FFF" opacity=".8" stroke="#70858C" stroke-opacity=".4" stroke-width="2"/>', $l - 16, $top - 12, $nw + 32 )
		. sprintf( '<path d="M%s %s L%s %s L%s %s L%s %s Z" fill="url(#metal)"/>', $c - 62, $top - 12, $c - 54, $top - 86, $c + 54, $top - 86, $c + 62, $top - 12 )
		. ss_sc_hl( $l + 20, $top + 40, 12, 300 ) . ss_sc_hl( $c - $R + 74, $cy - 90, 20, 250, .5 ) . ss_sc_label( $p, $c - 290, $cy + 10, 580 );
}

function ss_sc_cylinder( array $p ): string {
	$W = 330; $c = SS_CX; $f = SS_FLOOR; $top = 190; $x = $c - $W / 2; $bot = $f - 56; $xr = $x + $W;
	return ss_sc_shadow( 560 )
		. sprintf( '<path d="M%s %s L%s %s L%s %s L%s %s Z" %s/>', $c - 280, $f, $c - 250, $bot, $c + 250, $bot, $c + 280, $f, SS_GLASS )
		. sprintf( '<path d="M%s %s L%s %s L%s %s L%s %s L%s %s L%s %s Z" %s/>', $x - 16, $top, $x, $top + 24, $x, $bot, $xr, $bot, $xr, $top + 8, $xr + 6, $top, SS_GLASS )
		. sprintf( '<path d="M%s %s L%s %s" stroke="#FFF" stroke-opacity=".9" stroke-width="7" stroke-linecap="round"/>', $x - 16, $top, $xr + 6, $top )
		. ss_sc_hl( $x + 28, $top + 60, 16, $bot - $top - 130 ) . ss_sc_grads( $xr - 78, $top + 80, 34, 28 )
		. ss_sc_label( $p, $c - 330, 608, 600, false, -90 ) . sprintf( '<rect x="%s" y="440" width="264" height="600" fill="url(#wrap)"/>', $c - 162 );
}

function ss_sc_pipette( array $p ): string {
	$c = SS_CX; $f = SS_FLOOR;
	$digits = ss_cmds_to_d( ss_text( (string) ( preg_match( '/(\d+)\s*[µu]l/u', $p['name'], $m ) ? $m[1] : '1000' ), 'GeistMono-500', 50, $c, 349, 0, 'middle' ) );
	$shaft  = "M" . ( $c - 62 ) . " 1010 L" . ( $c + 62 ) . " 1010 L" . ( $c + 40 ) . " 1130 L" . ( $c - 40 ) . " 1130 Z";
	$nose   = "M" . ( $c - 26 ) . " 1130 L" . ( $c + 26 ) . " 1130 L" . ( $c + 15 ) . " 1230 L" . ( $c - 15 ) . " 1230 Z";
	$body   = "M" . ( $c - 128 ) . " 262 Q" . ( $c - 128 ) . " 228 " . ( $c - 94 ) . " 228 L" . ( $c + 94 ) . " 228 Q" . ( $c + 128 ) . " 228 " . ( $c + 128 ) . " 262 L" . ( $c + 128 ) . " 930 Q" . ( $c + 128 ) . " 960 " . ( $c + 100 ) . " 980 L" . ( $c + 62 ) . " 1010 L" . ( $c - 62 ) . " 1010 L" . ( $c - 100 ) . " 980 Q" . ( $c - 128 ) . " 960 " . ( $c - 128 ) . " 930 Z";
	$hook   = "M" . ( $c - 128 ) . " 400 C" . ( $c - 214 ) . " 412 " . ( $c - 228 ) . " 470 " . ( $c - 208 ) . " 508 C" . ( $c - 196 ) . " 528 " . ( $c - 170 ) . " 516 " . ( $c - 174 ) . " 492 C" . ( $c - 176 ) . " 474 " . ( $c - 152 ) . " 464 " . ( $c - 128 ) . " 462 Z";
	$tip    = "M" . ( $c - 21 ) . " 1200 L" . ( $c + 21 ) . " 1200 L" . ( $c + 8 ) . ' ' . ( $f - 4 ) . " Q$c " . ( $f + 2 ) . ' ' . ( $c - 8 ) . ' ' . ( $f - 4 ) . ' Z';
	$rect   = fn( $x, $y, $w, $h, $fill, $rx = 0 ) => sprintf( '<rect x="%s" y="%s" width="%s" height="%s" rx="%s" fill="%s"/>', $x, $y, $w, $h, $rx, $fill );
	return ss_sc_shadow( 200, 880 ) . "<g transform=\"rotate(7 $c $f)\">"
		. $rect( $c - 26, 118, 52, 110, '#A9AFB4' ) . $rect( $c - 26, 118, 52, 110, 'url(#wrap)' ) . $rect( $c - 62, 84, 124, 44, 'url(#metal)', 8 )
		. $rect( $c + 76, 176, 34, 70, '#A9AFB4' ) . $rect( $c + 62, 154, 62, 30, 'url(#metal)', 6 )
		. "<path d=\"$body\" fill=\"url(#poly)\" stroke=\"#9EA5A7\" stroke-opacity=\".7\" stroke-width=\"2\"/><path d=\"$hook\" fill=\"url(#metal)\"/>"
		. $rect( $c - 92, 292, 184, 76, '#16181D', 4 ) . "<path fill=\"#DCE6E8\" d=\"$digits\"/>"
		. "<path d=\"$shaft\" fill=\"url(#metal)\"/><path d=\"$nose\" fill=\"#A9AFB4\"/><path d=\"$nose\" fill=\"url(#wrap)\"/>"
		. "<path d=\"$tip\" fill=\"#E7EEF0\" fill-opacity=\".85\" stroke=\"#70858C\" stroke-opacity=\".7\" stroke-width=\"2\"/>"
		. ss_sc_label( $p, $c - 245, 556, 490, false, -90 ) . $rect( $c - 108, 419, 216, 490, 'url(#wrap)' ) . '</g>';
}

function ss_sc_bottle( array $p ): string {
	$W = 620; $c = SS_CX; $f = SS_FLOOR; $x = $c - $W / 2; $top = $f - 900; $nw = 240; $r = 50; $l = $c - $nw / 2; $n = $c + $nw / 2; $xr = $x + $W;
	$d = "M$l " . ( $top - 110 ) . " L$l " . ( $top - 46 ) . " C$l " . ( $top + 10 ) . " $x " . ( $top + 8 ) . " $x " . ( $top + 110 ) . " L$x " . ( $f - $r ) . " Q$x $f " . ( $x + $r ) . " $f L" . ( $xr - $r ) . " $f Q$xr $f $xr " . ( $f - $r ) . " L$xr " . ( $top + 110 ) . " C$xr " . ( $top + 8 ) . " $n " . ( $top + 10 ) . " $n " . ( $top - 46 ) . " L$n " . ( $top - 110 ) . ' Z';
	$ribs = '';
	for ( $i = 0; $i < 13; $i++ ) {
		$ribs .= sprintf( '<rect x="%s" y="%s" width="3" height="126" fill="#FFF" opacity=".10"/>', $l - 12 + $i * 24, $top - 238 );
	}
	return ss_sc_shadow( $W ) . "<path d=\"$d\" fill=\"url(#poly)\" stroke=\"#9EA5A7\" stroke-opacity=\".6\" stroke-width=\"2\"/>"
		. sprintf( '<rect x="%s" y="%s" width="%s" height="150" rx="10" fill="url(#metal)"/>', $l - 26, $top - 250, $nw + 52 ) . $ribs . ss_sc_label( $p, $x + 12, $top + 330, $W - 24 );
}

function ss_sc_spray( array $p ): string {
	$W = 500; $c = SS_CX; $f = SS_FLOOR; $x = $c - $W / 2; $top = $f - 680; $nw = 190; $r = 44; $l = $c - $nw / 2; $n = $c + $nw / 2; $xr = $x + $W;
	$d = "M$l " . ( $top - 36 ) . " L$l " . $top . " C$l " . ( $top + 36 ) . " $x " . ( $top + 42 ) . " $x " . ( $top + 110 ) . " L$x " . ( $f - $r ) . " Q$x $f " . ( $x + $r ) . " $f L" . ( $xr - $r ) . " $f Q$xr $f $xr " . ( $f - $r ) . " L$xr " . ( $top + 110 ) . " C$xr " . ( $top + 42 ) . " $n " . ( $top + 36 ) . " $n " . $top . " L$n " . ( $top - 36 ) . ' Z';
	$nozzle = 'M' . ( $c - 72 ) . ' ' . ( $top - 108 ) . ' L' . ( $c - 34 ) . ' ' . ( $top - 320 ) . ' Q' . ( $c - 30 ) . ' ' . ( $top - 344 ) . ' ' . $c . ' ' . ( $top - 344 ) . ' Q' . ( $c + 30 ) . ' ' . ( $top - 344 ) . ' ' . ( $c + 34 ) . ' ' . ( $top - 320 ) . ' L' . ( $c + 72 ) . ' ' . ( $top - 108 ) . ' Z';
	return ss_sc_shadow( $W )
		. "<path d=\"$d\" fill=\"url(#poly)\" stroke=\"#9EA5A7\" stroke-opacity=\".6\" stroke-width=\"2\"/>"
		. sprintf( '<rect x="%s" y="%s" width="%s" height="72" rx="8" fill="url(#metal)"/>', $l - 14, $top - 108, $nw + 28 )
		. sprintf( '<rect x="%s" y="%s" width="340" height="28" rx="14" fill="url(#poly)" stroke="#9EA5A7" stroke-width="2"/>', $c - 170, $top - 126 )
		. "<path d=\"$nozzle\" fill=\"url(#poly)\" stroke=\"#9EA5A7\" stroke-width=\"2\"/>"
		. sprintf( '<path d="%s" fill="url(#glass)" opacity=".45"/>', 'M' . ( $c - 86 ) . ' ' . ( $top - 108 ) . ' L' . ( $c - 64 ) . ' ' . ( $top - 360 ) . ' Q' . ( $c - 60 ) . ' ' . ( $top - 384 ) . ' ' . $c . ' ' . ( $top - 384 ) . ' Q' . ( $c + 60 ) . ' ' . ( $top - 384 ) . ' ' . ( $c + 64 ) . ' ' . ( $top - 360 ) . ' L' . ( $c + 86 ) . ' ' . ( $top - 108 ) . ' Z' )
		. ss_sc_label( $p, $x + 8, $top + 180, $W - 16 );
}

function ss_sc_box( array $p ): string {
	$inst = 'Instruments' === ( $p['category'] ?? '' );
	$W = $inst ? 860 : 780; $H = $inst ? 700 : 560; $D = 170; $f = SS_FLOOR; $x = SS_CX - $W / 2 - $D / 2 + 10; $y = $f - $H; $dy = 86; $xr = $x + $W;
	$lw = 700; $lh = $lw * $p['label_h'] / $p['label_w']; $lx = $x + ( $W - $lw ) / 2; $ly = $y + ( $H - 26 - $lh ) / 2;
	return ss_sc_shadow( $W + $D, SS_CX + 20 )
		. sprintf( '<path d="M%s %s L%s %s L%s %s L%s %s Z" fill="#FBFAF6" stroke="#CFCBC2" stroke-width="2"/>', $x, $y, $x + $D, $y - $dy, $xr + $D, $y - $dy, $xr, $y )
		. sprintf( '<path d="M%s %s L%s %s L%s %s L%s %s Z" fill="url(#side)"/>', $xr, $y, $xr + $D, $y - $dy, $xr + $D, $f - $dy, $xr, $f )
		. sprintf( '<rect x="%s" y="%s" width="%s" height="%s" fill="url(#face)" stroke="#CFCBC2" stroke-width="2"/>', $x, $y, $W, $H )
		. sprintf( '<rect x="%s" y="%s" width="%s" height="26" fill="%s"/>', $x, $f - 26, $W, esc_attr( $p['band'] ) )
		. ss_sc_label( $p, $lx, $ly, $lw, false ) . sprintf( '<rect x="%s" y="%s" width="%s" height="%s" fill="none" stroke="#D9D3C8" stroke-width="2"/>', $lx, $ly, $lw, $lh );
}

function ss_render_svg( array $p ): string {
	$body = match ( $p['type'] ) {
		'vial' => ss_sc_vial( $p, 'glass' ),
		'amber' => ss_sc_vial( $p, 'amber' ),
		'spray' => ss_sc_spray( $p ),
		'beaker' => ss_sc_beaker( $p ),
		'erlenmeyer' => ss_sc_erlenmeyer( $p ),
		'volumetric' => ss_sc_volumetric( $p ),
		'cylinder' => ss_sc_cylinder( $p ),
		'pipette' => ss_sc_pipette( $p ),
		'bottle' => ss_sc_bottle( $p ),
		default => ss_sc_box( $p ),
	};
	return '<svg xmlns="http://www.w3.org/2000/svg" width="1600" height="1600" viewBox="0 0 1600 1600"><defs>' . ss_scene_defs() . '</defs><rect width="1600" height="1600" fill="url(#bg)"/>' . $body . '</svg>';
}
