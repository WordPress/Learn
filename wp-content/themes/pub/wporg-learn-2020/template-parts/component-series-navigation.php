<?php
$series_term = wporg_learn_series_get_term( $post );
$previous    = wporg_learn_series_get_adjacent( 'previous', $post );
$next        = wporg_learn_series_get_adjacent( 'next', $post );
?>
<nav class="series-navigation">
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
	<ul aria-hidden="true" class="row video-grid">
		<li class="col-6 video-grid_item previous-series-item">
			<?php if ( $previous ) : ?>
				<?php esc_html_e( '&laquo; Previous', 'wporg-learn' ); ?>
				<a class="video-grid_item_link" href="<?php echo esc_url( get_the_permalink( $previous ) ); ?>">
					<?php echo wp_kses_post( get_the_title( $previous ) ); ?>
				</a>
			<?php endif; ?>
		</li>
		<li class="col-6 video-grid_item next-series-item">
			<?php if ( $next ) : ?>
				<?php esc_html_e( 'Next &raquo;', 'wporg-learn' ); ?>
				<a class="video-grid_item_link" href="<?php echo esc_url( get_the_permalink( $next ) ); ?>">
					<?php echo wp_kses_post( get_the_title( $next ) ); ?>
				</a>
			<?php endif; ?>
		</li>
	</ul>
	<ul class="row video-grid screen-reader-text">
		<li class="col-6 video-grid_item previous-series-item">
			<?php if ( $previous ) : ?>
				<a class="video-grid_item_link" href="<?php echo esc_url( get_the_permalink( $previous ) ); ?>">
					<?php echo wp_kses_post( 'Previous: ' . get_the_title( $previous ) ); ?>
				</a>
			<?php endif; ?>
		</li>
		<li class="col-6 video-grid_item next-series-item">
			<?php if ( $next ) : ?>
				<a class="video-grid_item_link" href="<?php echo esc_url( get_the_permalink( $next ) ); ?>">
					<?php echo wp_kses_post( 'Next: ' . get_the_title( $next ) ); ?>
				</a>
			<?php endif; ?>
		</li>
	</ul>
</nav>
