<?php
defined( 'ABSPATH' ) || exit;

/** Label artwork as flat vector primitives, so one layout feeds both the SVG and the PDF. */
const SS_INK   = '#16181D';
const SS_BRASS = '#A8844E';

function ss_glyphs(): array {
	static $g = null;
	return $g ??= json_decode( file_get_contents( SS_CORE_DIR . '/assets/glyphs.json' ), true );
}

function ss_text_width( string $s, string $font, float $size, float $tracking = 0 ): float {
	$f = ss_glyphs()['fonts'][ $font ];
	$w = 0.0;
	$chars = mb_str_split( $s );
	foreach ( $chars as $i => $ch ) {
		$w += ( $f['g'][ $ch ]['a'] ?? $f['g']['?']['a'] ) * $size / $f['upm'];
		if ( $i < count( $chars ) - 1 ) {
			$w += $tracking * $size;
		}
	}
	return $w;
}

/** Outlined text as absolute commands. $rotate turns the line 90 degrees anticlockwise about (x, y). */
function ss_text( string $s, string $font, float $size, float $x, float $y, float $tracking = 0, string $anchor = 'start', bool $rotate = false ): array {
	$f  = ss_glyphs()['fonts'][ $font ];
	$sc = $size / $f['upm'];
	$w  = ss_text_width( $s, $font, $size, $tracking );
	$pen = 'middle' === $anchor ? -$w / 2 : ( 'end' === $anchor ? -$w : 0 );
	$out = array();
	foreach ( mb_str_split( $s ) as $ch ) {
		$g = $f['g'][ $ch ] ?? $f['g']['?'];
		foreach ( $g['c'] as $c ) {
			$n = array( $c[0] );
			for ( $i = 1; $i < count( $c ); $i += 2 ) {
				$px = $pen + $c[ $i ] * $sc;
				$py = $c[ $i + 1 ] * $sc;
				$n[] = $rotate ? $x + $py : $x + $px;
				$n[] = $rotate ? $y - $px : $y + $py;
			}
			$out[] = $n;
		}
		$pen += $g['a'] * $sc + $tracking * $size;
	}
	return $out;
}

function ss_rect_cmds( float $x, float $y, float $w, float $h ): array {
	return array( array( 'M', $x, $y ), array( 'L', $x + $w, $y ), array( 'L', $x + $w, $y + $h ), array( 'L', $x, $y + $h ), array( 'Z' ) );
}

function ss_svg_path_to_cmds( string $d, float $ox, float $oy, float $s ): array {
	$out = array();
	preg_match_all( '/([MLZ])([^MLZ]*)/', $d, $m, PREG_SET_ORDER );
	foreach ( $m as $p ) {
		if ( 'Z' === $p[1] ) {
			$out[] = array( 'Z' );
			continue;
		}
		$n     = array_map( 'floatval', preg_split( '/\s+/', trim( $p[2] ) ) );
		$out[] = array( $p[1], $ox + $n[0] * $s, $oy + $n[1] * $s );
	}
	return $out;
}

function ss_wrap_text( string $s, string $font, float $size, float $max ): array {
	$lines = array();
	$cur   = '';
	foreach ( explode( ' ', $s ) as $word ) {
		$t = '' === $cur ? $word : "$cur $word";
		if ( ss_text_width( $t, $font, $size ) <= $max || '' === $cur ) {
			$cur = $t;
		} else {
			$lines[] = $cur;
			$cur     = $word;
		}
	}
	$lines[] = $cur;
	return $lines;
}

function ss_qr_base(): string {
	$base = 'local' === wp_get_environment_type() ? 'https://sharpscience.example' : home_url();
	return untrailingslashit( (string) apply_filters( 'ss_qr_base', $base ) );
}

/**
 * @param array $p name, spec, catalogue_no, batch, band.
 * @return array list of array( fill, cmds ).
 */
function ss_label_items( array $p, float $W = 50, float $H = 22, float $bleed = 0 ): array {
	$k      = $H / 22;
	$left   = 8.5 * $k;
	$right  = 12.6 * $k;
	$band_w = 2;
	$pad    = 1.5 * $k;
	$band_x = $W - $right - $band_w;
	$cx     = $left + 1.6 * $k;
	$cw     = $band_x - $cx - 1.4 * $k;
	$specs  = array_map( 'trim', explode( ',', $p['spec'] ) );
	$first  = $specs[0] ?? '';
	$name   = trim( preg_replace( '/\s*' . preg_quote( $first, '/' ) . '$/u', '', $p['name'] ) ) ?: $p['name'];

	$items   = array();
	$items[] = array( '#FFFFFF', ss_rect_cmds( -$bleed, -$bleed, $W + 2 * $bleed, $H + 2 * $bleed ) );

	$mark = ss_glyphs()['mark'];
	$ms   = 5.4 * $k / 64;
	$mx   = ( $left - 5.4 * $k ) / 2;
	$items[] = array( SS_INK, ss_svg_path_to_cmds( $mark['strokes'], $mx, $pad, $ms ) );
	$items[] = array( SS_BRASS, ss_svg_path_to_cmds( $mark['cut'], $mx, $pad, $ms ) );

	$vs   = 1.14 * $k;
	$vx   = $left / 2 + $vs * .36;
	$vy   = $H - $pad;
	$word = ss_text_width( 'SHARP', 'Geist-600', $vs, .2 ) + $vs * .6;
	$items[] = array( SS_INK, array_merge( ss_text( 'SHARP', 'Geist-600', $vs, $vx, $vy, .2, 'start', true ), ss_text( 'SCIENCE', 'Geist-500', $vs, $vx, $vy - $word, .2, 'start', true ) ) );
	$items[] = array( SS_BRASS, ss_rect_cmds( $left, $pad, .12, $H - 2 * $pad ) );

	$size  = 2.7 * $k;
	$lines = ss_wrap_text( $name, 'Geist-500', $size, $cw );
	$too_big = function () use ( &$lines, &$size, $cw ) {
		if ( count( $lines ) > 2 ) {
			return true;
		}
		foreach ( $lines as $l ) {
			if ( ss_text_width( $l, 'Geist-500', $size ) > $cw ) {
				return true;
			}
		}
		return false;
	};
	while ( $too_big() && $size > 1.2 * $k ) {
		$size -= .1 * $k;
		$lines = ss_wrap_text( $name, 'Geist-500', $size, $cw );
	}
	$y = 3.1 * $k + $size * .8;
	foreach ( $lines as $l ) {
		$items[] = array( SS_INK, ss_text( $l, 'Geist-500', $size, $cx, $y ) );
		$y      += $size * 1.18;
	}

	// The spec row shrinks until every box fits, so no spec is dropped.
	$bs  = 1.75 * $k;
	$row = function ( float $z ) use ( $specs, $k ) {
		$w = -.9 * $k;
		foreach ( $specs as $s ) {
			$w += ss_text_width( $s, 'GeistMono-500', $z ) + 1.7 * $z / 1.75 + .9 * $k;
		}
		return $w;
	};
	while ( $row( $bs ) > $band_x - $cx - .6 * $k && $bs > $k ) {
		$bs -= .03 * $k;
	}
	$bh = 3.5 * $k;
	$by = $H - $pad - $bh - .2 * $k;
	$bp = .85 * $bs / 1.75;
	$bx = $cx;
	$t  = .14;
	foreach ( $specs as $s ) {
		if ( '' === $s ) {
			continue;
		}
		$bw      = ss_text_width( $s, 'GeistMono-500', $bs ) + 2 * $bp;
		$frame   = array_merge( ss_rect_cmds( $bx, $by, $bw, $t ), ss_rect_cmds( $bx, $by + $bh - $t, $bw, $t ), ss_rect_cmds( $bx, $by, $t, $bh ), ss_rect_cmds( $bx + $bw - $t, $by, $t, $bh ) );
		$items[] = array( SS_INK, $frame );
		$items[] = array( SS_INK, ss_text( $s, 'GeistMono-500', $bs, $bx + $bp, $by + $bh / 2 + $bs * .35 ) );
		$bx     += $bw + .9 * $k;
	}

	$items[] = array( $p['band'], ss_rect_cmds( $band_x, -$bleed, $band_w, $H + 2 * $bleed ) );

	$rx  = $band_x + $band_w + 1.15 * $k;
	$qs  = 9.6 * $k;
	$qy  = 1.05 * $k;
	$qr  = ss_qr_matrix( ss_qr_base() . '/certificates/?batch=' . rawurlencode( $p['batch'] ) );
	$n   = count( $qr );
	$u   = $qs / $n;
	$cmd = array();
	foreach ( $qr as $r => $cells ) {
		for ( $c = 0; $c < $n; $c++ ) {
			if ( ! $cells[ $c ] ) {
				continue;
			}
			$run = 1;
			while ( $c + $run < $n && $cells[ $c + $run ] ) {
				$run++;
			}
			array_push( $cmd, ...ss_rect_cmds( $rx + $c * $u, $qy + $r * $u, $run * $u, $u ) );
			$c += $run - 1;
		}
	}
	$items[] = array( SS_INK, $cmd );
	$ty      = $qy + $qs;
	$items[] = array( SS_INK, ss_text( $p['catalogue_no'], 'GeistMono-500', 1.5 * $k, $rx, $ty + 2.5 * $k ) );
	$items[] = array( SS_INK, ss_text( 'Batch ' . $p['batch'], 'GeistMono-400', 1.16 * $k, $rx, $ty + 4.5 * $k ) );
	$items[] = array( SS_INK, ss_text( 'For laboratory use', 'Geist-400', 1.06 * $k, $rx, $H - $pad + .35 * $k ) );
	return $items;
}

function ss_cmds_to_d( array $cmds ): string {
	$d = '';
	foreach ( $cmds as $c ) {
		$d .= $c[0];
		for ( $i = 1; $i < count( $c ); $i++ ) {
			$d .= ( $i > 1 ? ' ' : '' ) . round( $c[ $i ], 3 );
		}
	}
	return $d;
}

function ss_label_inner( array $p, float $W = 50, float $H = 22 ): string {
	$o = '';
	foreach ( ss_label_items( $p, $W, $H ) as [ $fill, $cmds ] ) {
		$o .= sprintf( '<path fill="%s" d="%s"/>', esc_attr( $fill ), ss_cmds_to_d( $cmds ) );
	}
	return $o;
}

function ss_label_svg( array $p, float $W = 50, float $H = 22 ): string {
	return sprintf( '<svg xmlns="http://www.w3.org/2000/svg" width="%smm" height="%smm" viewBox="0 0 %s %s" role="img" aria-label="%s label">%s</svg>', $W, $H, $W, $H, esc_attr( $p['name'] ), ss_label_inner( $p, $W, $H ) );
}

function ss_hex_rgb( string $hex ): string {
	$n = hexdec( ltrim( $hex, '#' ) );
	return sprintf( '%.3F %.3F %.3F', ( $n >> 16 & 255 ) / 255, ( $n >> 8 & 255 ) / 255, ( $n & 255 ) / 255 );
}

/** Vector PDF with 3 mm bleed, crop marks, and TrimBox and BleedBox. */
function ss_label_pdf( array $p, float $W = 50, float $H = 22 ): string {
	$bleed = 3;
	$m     = 8;
	$pt    = 72 / 25.4;
	$PW    = ( $W + 2 * $m ) * $pt;
	$PH    = ( $H + 2 * $m ) * $pt;
	$fx    = fn( $x ) => sprintf( '%.3F', ( $x + $m ) * $pt );
	$fy    = fn( $y ) => sprintf( '%.3F', $PH - ( $y + $m ) * $pt );
	$s     = '';
	$s    .= sprintf( "q %s %s %s %s re W n\n", $fx( -$bleed ), $fy( $H + $bleed ), sprintf( '%.3F', ( $W + 6 ) * $pt ), sprintf( '%.3F', ( $H + 6 ) * $pt ) );
	$s    .= ss_pdf_paths( ss_label_items( $p, $W, $H, $bleed ), $fx, $fy );
	$s .= "Q\n0 0 0 RG 0.25 w\n";
	$g  = $bleed + .5;
	$L  = 3.5;
	foreach ( array( 0, $W ) as $x ) {
		foreach ( array( array( -$g - $L, -$g ), array( $H + $g, $H + $g + $L ) ) as [ $a, $b ] ) {
			$s .= "{$fx($x)} {$fy($a)} m {$fx($x)} {$fy($b)} l S\n";
		}
	}
	foreach ( array( 0, $H ) as $y ) {
		foreach ( array( array( -$g - $L, -$g ), array( $W + $g, $W + $g + $L ) ) as [ $a, $b ] ) {
			$s .= "{$fx($a)} {$fy($y)} m {$fx($b)} {$fy($y)} l S\n";
		}
	}
	$box = fn( $inset, $w, $h ) => sprintf( '[%.3F %.3F %.3F %.3F]', $inset * $pt, $inset * $pt, ( $inset + $w ) * $pt, ( $inset + $h ) * $pt );
	$objs = array(
		'<< /Type /Catalog /Pages 2 0 R >>',
		'<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
		sprintf( '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %.3F %.3F] /TrimBox %s /BleedBox %s /Contents 4 0 R /Resources << >> >>', $PW, $PH, $box( $m, $W, $H ), $box( $m - $bleed, $W + 6, $H + 6 ) ),
		'<< /Length ' . strlen( $s ) . " >>\nstream\n{$s}endstream",
	);
	return ss_pdf_assemble( $objs );
}

/** Filled vector paths as PDF operators. $fx and $fy map millimetres to page points. */
function ss_pdf_paths( array $items, callable $fx, callable $fy ): string {
	$s = '';
	foreach ( $items as [ $fill, $cmds ] ) {
		$s .= ss_hex_rgb( $fill ) . " rg\n";
		$cx = 0;
		$cy = 0;
		foreach ( $cmds as $c ) {
			switch ( $c[0] ) {
				case 'M':
				case 'L':
					$s .= $fx( $c[1] ) . ' ' . $fy( $c[2] ) . ( 'M' === $c[0] ? " m\n" : " l\n" );
					$cx = $c[1];
					$cy = $c[2];
					break;
				case 'Q': // Quadratic promoted to cubic, because PDF has no quadratic operator.
					$a  = $cx + 2 / 3 * ( $c[1] - $cx );
					$b  = $cy + 2 / 3 * ( $c[2] - $cy );
					$d  = $c[3] + 2 / 3 * ( $c[1] - $c[3] );
					$e  = $c[4] + 2 / 3 * ( $c[2] - $c[4] );
					$s .= implode( ' ', array( $fx( $a ), $fy( $b ), $fx( $d ), $fy( $e ), $fx( $c[3] ), $fy( $c[4] ) ) ) . " c\n";
					$cx = $c[3];
					$cy = $c[4];
					break;
				case 'C':
					$s .= implode( ' ', array( $fx( $c[1] ), $fy( $c[2] ), $fx( $c[3] ), $fy( $c[4] ), $fx( $c[5] ), $fy( $c[6] ) ) ) . " c\n";
					$cx = $c[5];
					$cy = $c[6];
					break;
				case 'Z':
					$s .= "h\n";
			}
		}
		$s .= "f\n";
	}
	return $s;
}

/** One-page A4 sample certificate. Demo records say so on the page. */
function ss_cert_pdf( array $r ): string {
	$pt    = 72 / 25.4;
	$fx    = fn( $x ) => sprintf( '%.3F', $x * $pt );
	$fy    = fn( $y ) => sprintf( '%.3F', ( 297 - $y ) * $pt );
	$mark  = ss_glyphs()['mark'];
	$items = array(
		array( SS_INK, ss_svg_path_to_cmds( $mark['strokes'], 20, 20, .22 ) ),
		array( SS_BRASS, ss_svg_path_to_cmds( $mark['cut'], 20, 20, .22 ) ),
		array( SS_INK, ss_text( 'SHARP SCIENCE', 'Geist-600', 4, 38, 29, .2 ) ),
		array( '#8C2F2F', ss_text( 'SAMPLE ONLY. DEMO DATA. NOT A REAL TEST RECORD.', 'Geist-600', 4, 20, 52 ) ),
		array( SS_INK, ss_text( 'Batch certificate', 'Geist-500', 9, 20, 68 ) ),
		array( '#D9D3C8', ss_rect_cmds( 20, 74, 170, .25 ) ),
	);
	$y = 86;
	foreach ( array( 'Product' => $r['product'], 'Batch' => $r['batch'], 'Issue date' => $r['date'], 'Standard' => $r['standard'], 'Result' => $r['result'], 'Laboratory' => $r['lab'] ) as $k => $v ) {
		$items[] = array( '#5E646E', ss_text( $k, 'Geist-400', 4, 20, $y ) );
		$items[] = array( SS_INK, ss_text( $v, 'GeistMono-400', 4, 70, $y ) );
		$items[] = array( '#D9D3C8', ss_rect_cmds( 20, $y + 4, 170, .25 ) );
		$y      += 12;
	}
	$s    = ss_pdf_paths( $items, $fx, $fy );
	$objs = array(
		'<< /Type /Catalog /Pages 2 0 R >>',
		'<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
		sprintf( '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %.3F %.3F] /Contents 4 0 R /Resources << >> >>', 210 * $pt, 297 * $pt ),
		'<< /Length ' . strlen( $s ) . " >>\nstream\n{$s}endstream",
	);
	return ss_pdf_assemble( $objs );
}

function ss_pdf_assemble( array $objs ): string {
	$pdf = "%PDF-1.4\n";
	$off = array();
	foreach ( $objs as $i => $o ) {
		$off[] = strlen( $pdf );
		$pdf  .= ( $i + 1 ) . " 0 obj\n$o\nendobj\n";
	}
	$x    = strlen( $pdf );
	$pdf .= 'xref' . "\n0 " . ( count( $objs ) + 1 ) . "\n0000000000 65535 f \n";
	foreach ( $off as $o ) {
		$pdf .= sprintf( "%010d 00000 n \n", $o );
	}
	return $pdf . 'trailer << /Size ' . ( count( $objs ) + 1 ) . " /Root 1 0 R >>\nstartxref\n$x\n%%EOF\n";
}
