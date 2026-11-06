<?php
defined( 'ABSPATH' ) || exit;
$rows = $args['rows'];
$show = $args['product_col'] ?? true;
?>
<div class="table-scroll" tabindex="0" role="region" aria-label="Certificates">
<table class="cert-table">
	<thead><tr><?php if ( $show ) : ?><th scope="col">Product</th><?php endif; ?><th scope="col">Batch</th><th scope="col">Date</th><th scope="col">Standard</th><th scope="col">PDF</th><th scope="col">Verify</th></tr></thead>
	<tbody>
	<?php foreach ( $rows as $r ) : ?>
		<tr>
			<?php if ( $show ) : ?><td><?php if ( $r['url'] ) : ?><a href="<?php echo esc_url( $r['url'] ); ?>"><?php echo esc_html( $r['product'] ); ?></a><?php else : echo esc_html( $r['product'] ); endif; ?></td><?php endif; ?>
			<td><?php echo esc_html( $r['batch'] ); ?></td>
			<td><?php echo esc_html( $r['date'] ); ?></td>
			<td><?php echo esc_html( $r['standard'] ); ?></td>
			<td><?php if ( $r['pdf'] ) : ?><a href="<?php echo esc_url( $r['pdf'] ); ?>" aria-label="<?php echo esc_attr( 'PDF for batch ' . $r['batch'] ); ?>">PDF</a><?php else : ?>–<?php endif; ?></td>
			<td><?php if ( $r['verify'] ) : ?><a href="<?php echo esc_url( $r['verify'] ); ?>" aria-label="<?php echo esc_attr( 'Verify batch ' . $r['batch'] ); ?>">Verify</a><?php else : ?>–<?php endif; ?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
</div>
