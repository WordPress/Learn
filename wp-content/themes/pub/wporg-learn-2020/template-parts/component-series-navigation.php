<?php
$series_term = wporg_workshop_series_get_term( $post );
$previous    = wporg_workshop_series_get_adjacent( 'previous', $post );
$next        = wporg_workshop_series_get_adjacent( 'next', $post );
?>
<nav class="workshop-series">
	<h2>
		<?php
		printf(
			wp_kses_post( __( 'Series: %s', 'wporg-learn' ) ),
			sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_html( get_term_link( $series_term ) ),
				esc_html( $series_term->name )
			)
		);
		?>
	</h2>
	<?php echo wp_kses_post( wpautop( term_description( $series_term->term_id ) ) ); ?>
	<ul class="row video-grid">
		<li class="col-6 video-grid_item">
			<?php if ( $previous ) : ?>
				<?php esc_html_e( 'Previous workshop:', 'wporg-learn' ); ?>
				<a class="video-grid_item_link" href="<?php echo esc_url( get_the_permalink( $previous ) ); ?>">
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo wporg_get_post_thumbnail( $previous, 'medium' );
					?>
					<?php echo wp_kses_post( get_the_title( $previous ) ); ?>
				</a>
			<?php endif; ?>
		</li>
		<li class="col-6 video-grid_item">
			<?php if ( $next ) : ?>
				<?php esc_html_e( 'Next workshop:', 'wporg-learn' ); ?>
				<a class="video-grid_item_link" href="<?php echo esc_url( get_the_permalink( $next ) ); ?>">
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo wporg_get_post_thumbnail( $next, 'medium' );
					?>
					<?php echo wp_kses_post( get_the_title( $next ) ); ?>
				</a>
			<?php endif; ?>
		</li>
	</ul>
</nav>
