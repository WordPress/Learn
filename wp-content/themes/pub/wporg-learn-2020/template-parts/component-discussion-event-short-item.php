<?php

namespace WordPressdotorg\Theme;

/** @var array $args */
?>

<li class="discussion-event">
	<div>
		<span class="discussion-event-date" data-date="<?php echo esc_attr( $args['date_utc'] ); ?>">
			<?php echo esc_html( gmdate( 'l, F jS, Y', strtotime( $args['date_utc'] ) ) ); ?>
		</span>
		<a class="discussion-event-link" href="<?php echo esc_attr( $args['url'] ); ?>">
			<?php echo esc_html( $args['title'] ); ?>
		</a>
	</div>
	<div class="wp-block-button is-style-primary">
		<a class="wp-block-button__link" href="<?php echo esc_attr( $args['url'] ); ?>" style="border-radius:5px">
			<?php esc_html_e( 'Join the discussion', 'wporg-learn' ); ?>
		</a>
	</div>
</li>
