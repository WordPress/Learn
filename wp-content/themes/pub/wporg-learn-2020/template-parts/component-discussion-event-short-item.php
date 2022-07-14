<?php

namespace WordPressdotorg\Theme;

/** @var array $args */

$timestamp = strtotime( $args['date_utc'] ) - (int) $args['date_utc_offset'];
?>

<li class="discussion-event">
	<div>
		<a class="discussion-event-link" href="<?php echo esc_attr( $args['url'] ); ?>">
			<h3><?php echo esc_html( $args['title'] ); ?></h3>
		</a>
		<abbr
			class="discussion-event-date"
			title="<?php echo esc_attr( gmdate( 'c', $timestamp ) ); ?>"
			data-date-utc="<?php echo esc_attr( gmdate( 'c', $timestamp ) ); ?>"
		>
			<?php echo esc_html( gmdate( 'l, F jS, Y', $timestamp ) ); ?>
		</abbr>
	</div>
	<div class="wp-block-button is-style-primary">
		<a class="wp-block-button__link" href="<?php echo esc_attr( $args['url'] ); ?>" style="border-radius:5px">
			<?php esc_html_e( 'Join this workshop', 'wporg-learn' ); ?><span class="screen-reader-text"><?php echo sprintf( ': %s', esc_html( $args['title'] ) ); ?></span>
		</a>
	</div>
</li>
