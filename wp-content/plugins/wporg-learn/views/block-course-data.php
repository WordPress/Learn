<?php

namespace WPOrg_Learn\View\Blocks;

defined( 'WPINC' ) || die();

/**
 * @var array $data
 */

?>

<?php if ( get_post_type() === 'course' && ! empty( $data ) ) : ?>
	<ul class="wp-block-wporg-learn-course-data">
		<?php
		foreach ( $data as $key => $item ) { ?>
			<li>
				<strong><?php echo esc_html( $item['label'] ); ?></strong>
				<span><?php echo esc_html( $item['value'] ); ?></span>
			</li>
			<?php
		}
		?>
	</ul>
<?php endif; ?>
