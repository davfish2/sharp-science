<?php
defined( 'ABSPATH' ) || exit;

/**
 * Compact QR encoder: byte mode, error correction level M, versions 1 to 9.
 * Self-contained so label generation has no dependency on host extensions.
 */
function ss_qr_gf( int $x, int $y ): int {
	$z = 0;
	for ( $i = 7; $i >= 0; $i-- ) {
		$z  = ( $z << 1 ) ^ ( ( $z >> 7 ) * 0x11D );
		$z ^= ( ( $y >> $i ) & 1 ) * $x;
	}
	return $z & 0xFF;
}

function ss_qr_raw_modules( int $v ): int {
	$r = ( 16 * $v + 128 ) * $v + 64;
	if ( $v >= 2 ) {
		$n  = intdiv( $v, 7 ) + 2;
		$r -= ( 25 * $n - 10 ) * $n - 55;
		if ( $v >= 7 ) {
			$r -= 36;
		}
	}
	return $r;
}

function ss_qr_codewords( array $data, int $v, int $ecc_len, int $blocks ): array {
	$div = array_fill( 0, $ecc_len, 0 );
	$div[ $ecc_len - 1 ] = 1;
	$root = 1;
	for ( $i = 0; $i < $ecc_len; $i++ ) {
		for ( $j = 0; $j < $ecc_len; $j++ ) {
			$div[ $j ] = ss_qr_gf( $div[ $j ], $root );
			if ( $j + 1 < $ecc_len ) {
				$div[ $j ] ^= $div[ $j + 1 ];
			}
		}
		$root = ss_qr_gf( $root, 2 );
	}
	$raw       = intdiv( ss_qr_raw_modules( $v ), 8 );
	$short_n   = $blocks - $raw % $blocks;
	$short_len = intdiv( $raw, $blocks );
	$out_blocks = array();
	$k = 0;
	for ( $i = 0; $i < $blocks; $i++ ) {
		$len = $short_len - $ecc_len + ( $i < $short_n ? 0 : 1 );
		$dat = array_slice( $data, $k, $len );
		$k  += $len;
		$rem = array_fill( 0, $ecc_len, 0 );
		foreach ( $dat as $b ) {
			$f = $b ^ array_shift( $rem );
			$rem[] = 0;
			foreach ( $div as $idx => $coef ) {
				$rem[ $idx ] ^= ss_qr_gf( $coef, $f );
			}
		}
		if ( $i < $short_n ) {
			$dat[] = 0;
		}
		$out_blocks[] = array_merge( $dat, $rem );
	}
	$res = array();
	$n   = count( $out_blocks[0] );
	for ( $i = 0; $i < $n; $i++ ) {
		foreach ( $out_blocks as $j => $blk ) {
			if ( $i !== $short_len - $ecc_len || $j >= $short_n ) {
				$res[] = $blk[ $i ];
			}
		}
	}
	return $res;
}

/** Returns a square array of booleans, true for dark modules. */
function ss_qr_matrix( string $text ): array {
	$ECC = array( 0, 10, 16, 26, 18, 24, 16, 18, 22, 22 );
	$NB  = array( 0, 1, 1, 1, 2, 2, 4, 4, 4, 5 );
	$bytes = array_values( unpack( 'C*', $text ) );

	for ( $v = 1; $v <= 9; $v++ ) {
		$cap = ( intdiv( ss_qr_raw_modules( $v ), 8 ) - $ECC[ $v ] * $NB[ $v ] );
		if ( 4 + 8 + 8 * count( $bytes ) <= $cap * 8 ) {
			break;
		}
	}
	if ( $v > 9 ) {
		throw new RuntimeException( 'QR payload too long' );
	}

	$bits = array();
	$put  = function ( int $val, int $n ) use ( &$bits ) {
		for ( $i = $n - 1; $i >= 0; $i-- ) {
			$bits[] = ( $val >> $i ) & 1;
		}
	};
	$put( 4, 4 );
	$put( count( $bytes ), 8 );
	foreach ( $bytes as $b ) {
		$put( $b, 8 );
	}
	$put( 0, min( 4, $cap * 8 - count( $bits ) ) );
	$put( 0, ( 8 - count( $bits ) % 8 ) % 8 );
	for ( $pad = 0xEC; count( $bits ) < $cap * 8; $pad ^= 0xEC ^ 0x11 ) {
		$put( $pad, 8 );
	}
	$data = array_map( fn( $c ) => bindec( implode( '', $c ) ), array_chunk( $bits, 8 ) );
	$cw   = ss_qr_codewords( $data, $v, $ECC[ $v ], $NB[ $v ] );

	$size = $v * 4 + 17;
	$m    = array_fill( 0, $size, array_fill( 0, $size, false ) );
	$fn   = $m;
	$set  = function ( int $x, int $y, bool $d ) use ( &$m, &$fn ) {
		$m[ $y ][ $x ]  = $d;
		$fn[ $y ][ $x ] = true;
	};

	for ( $i = 0; $i < $size; $i++ ) {
		$set( 6, $i, 0 === $i % 2 );
		$set( $i, 6, 0 === $i % 2 );
	}
	foreach ( array( array( 3, 3 ), array( $size - 4, 3 ), array( 3, $size - 4 ) ) as [ $cx, $cy ] ) {
		for ( $dy = -4; $dy <= 4; $dy++ ) {
			for ( $dx = -4; $dx <= 4; $dx++ ) {
				$d = max( abs( $dx ), abs( $dy ) );
				$x = $cx + $dx;
				$y = $cy + $dy;
				if ( $x >= 0 && $x < $size && $y >= 0 && $y < $size ) {
					$set( $x, $y, 2 !== $d && 4 !== $d );
				}
			}
		}
	}
	if ( $v > 1 ) {
		$n    = intdiv( $v, 7 ) + 2;
		$step = intdiv( $v * 8 + $n * 3 + 5, $n * 4 - 4 ) * 2;
		$pos  = array( 6 );
		for ( $p = $size - 7; count( $pos ) < $n; $p -= $step ) {
			array_splice( $pos, 1, 0, array( $p ) );
		}
		foreach ( $pos as $i => $px ) {
			foreach ( $pos as $j => $py ) {
				if ( ( 0 === $i && 0 === $j ) || ( 0 === $i && $n - 1 === $j ) || ( $n - 1 === $i && 0 === $j ) ) {
					continue;
				}
				for ( $dy = -2; $dy <= 2; $dy++ ) {
					for ( $dx = -2; $dx <= 2; $dx++ ) {
						$set( $px + $dx, $py + $dy, 1 !== max( abs( $dx ), abs( $dy ) ) );
					}
				}
			}
		}
	}
	$format = function ( int $mask ) use ( &$set, $size ) {
		$d = $mask;
		$r = $d;
		for ( $i = 0; $i < 10; $i++ ) {
			$r = ( $r << 1 ) ^ ( ( $r >> 9 ) * 0x537 );
		}
		$b   = ( ( $d << 10 ) | ( $r & 0x3FF ) ) ^ 0x5412;
		$bit = fn( $i ) => 1 === ( ( $b >> $i ) & 1 );
		for ( $i = 0; $i <= 5; $i++ ) {
			$set( 8, $i, $bit( $i ) );
		}
		$set( 8, 7, $bit( 6 ) );
		$set( 8, 8, $bit( 7 ) );
		$set( 7, 8, $bit( 8 ) );
		for ( $i = 9; $i < 15; $i++ ) {
			$set( 14 - $i, 8, $bit( $i ) );
		}
		for ( $i = 0; $i < 8; $i++ ) {
			$set( $size - 1 - $i, 8, $bit( $i ) );
		}
		for ( $i = 8; $i < 15; $i++ ) {
			$set( 8, $size - 15 + $i, $bit( $i ) );
		}
		$set( 8, $size - 8, true );
	};
	$format( 0 );
	if ( $v >= 7 ) {
		$r = $v;
		for ( $i = 0; $i < 12; $i++ ) {
			$r = ( $r << 1 ) ^ ( ( $r >> 11 ) * 0x1F25 );
		}
		$b = ( $v << 12 ) | ( $r & 0xFFF );
		for ( $i = 0; $i < 18; $i++ ) {
			$a = $size - 11 + $i % 3;
			$c = intdiv( $i, 3 );
			$d = 1 === ( ( $b >> $i ) & 1 );
			$set( $a, $c, $d );
			$set( $c, $a, $d );
		}
	}

	$i = 0;
	for ( $right = $size - 1; $right >= 1; $right -= 2 ) {
		if ( 6 === $right ) {
			$right = 5;
		}
		for ( $vert = 0; $vert < $size; $vert++ ) {
			for ( $j = 0; $j < 2; $j++ ) {
				$x  = $right - $j;
				$up = 0 === ( ( $right + 1 ) & 2 );
				$y  = $up ? $size - 1 - $vert : $vert;
				if ( ! $fn[ $y ][ $x ] && $i < count( $cw ) * 8 ) {
					$m[ $y ][ $x ] = 1 === ( ( $cw[ $i >> 3 ] >> ( 7 - ( $i & 7 ) ) ) & 1 );
					$i++;
				}
			}
		}
	}

	$masked = function ( int $mask ) use ( &$m, $fn, $size ) {
		$o = $m;
		for ( $y = 0; $y < $size; $y++ ) {
			for ( $x = 0; $x < $size; $x++ ) {
				$inv = match ( $mask ) {
					0 => 0 === ( $x + $y ) % 2,
					1 => 0 === $y % 2,
					2 => 0 === $x % 3,
					3 => 0 === ( $x + $y ) % 3,
					4 => 0 === ( intdiv( $x, 3 ) + intdiv( $y, 2 ) ) % 2,
					5 => 0 === $x * $y % 2 + $x * $y % 3,
					6 => 0 === ( $x * $y % 2 + $x * $y % 3 ) % 2,
					7 => 0 === ( ( $x + $y ) % 2 + $x * $y % 3 ) % 2,
				};
				if ( $inv && ! $fn[ $y ][ $x ] ) {
					$o[ $y ][ $x ] = ! $o[ $y ][ $x ];
				}
			}
		}
		return $o;
	};
	// Penalty on runs and dark balance. Any mask decodes, so this only improves scan robustness.
	$penalty = function ( array $g ) use ( $size ) {
		$p    = 0;
		$dark = 0;
		for ( $a = 0; $a < $size; $a++ ) {
			$rx = 1;
			$ry = 1;
			for ( $b = 1; $b < $size; $b++ ) {
				$rx = $g[ $a ][ $b ] === $g[ $a ][ $b - 1 ] ? $rx + 1 : 1;
				$ry = $g[ $b ][ $a ] === $g[ $b - 1 ][ $a ] ? $ry + 1 : 1;
				$p += ( 5 === $rx ? 3 : ( $rx > 5 ? 1 : 0 ) ) + ( 5 === $ry ? 3 : ( $ry > 5 ? 1 : 0 ) );
			}
			$dark += count( array_filter( $g[ $a ] ) );
		}
		return $p + intdiv( abs( $dark * 20 - $size * $size * 10 ), $size * $size ) * 10;
	};
	$best = null;
	$low  = PHP_INT_MAX;
	for ( $k = 0; $k < 8; $k++ ) {
		$format( $k );
		$g      = $masked( $k );
		$score  = $penalty( $g );
		if ( $score < $low ) {
			$low  = $score;
			$best = $k;
		}
	}
	$format( $best );
	return $masked( $best );
}
