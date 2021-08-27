<?php
/**
 * Markup for a notice when a locale isn't fully translated.
 */

namespace WPOrg_Learn\Locale;

defined( 'WPINC' ) || die();

/** @var string $contribute_url */
?>

<div class="wporg-learn-locale-notice notice notice-warning notice-alt is-dismissible">
	<p>
		<?php
		printf(
			wp_kses_post(
				/* translators: %s is a URL. If you translate 'percent' to '%' please encode it as '%%' and discard the GlotPress warning. */
				__(
					'⚠️ The translation for this locale is incomplete. Help us get to 100 percent by <a href="%s">contributing a translation</a>.',
					'wporg-learn'
				)
			),
			esc_url( $contribute_url )
		);
		?>
	</p>
	<button type="button" class="wporg-learn-locale-notice-dismiss">
		<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'wporg-learn' ); ?></span>
	</button>
</div>
