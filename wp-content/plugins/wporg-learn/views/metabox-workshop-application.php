<?php

namespace WPOrg_Learn\View\Metabox;

defined( 'WPINC' ) || die();

/** @var array $schema */
/** @var array $application */
?>

<table class="widefat striped">
	<tbody>
	<?php foreach ( $schema['properties'] as $property => $config ) :
		if ( 'nonce' === $property ) :
			continue;
		endif;
		?>
		<tr>
			<th>
				<?php echo esc_html( $config['label'] ); ?>
			</th>
			<td>
				<?php if ( is_array( $application[ $property ] ) ) : ?>
					<?php echo wp_kses_post( implode( '<br />', $application[ $property ] ) ); ?>
				<?php else : ?>
					<?php echo wp_kses_post( wpautop( $application[ $property ] ) ); ?>
				<?php endif; ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
