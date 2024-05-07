<?php

namespace WordPressdotorg\Theme\Learn_2024\Locale_List;

defined( 'WPINC' ) || die();

/** @var array $locales */
?>

<script type="text/javascript" id="wporg-learn-locales">
	const wporgLearnLocales = [
		<?php foreach ( $locales as $code => $label ) : ?>
			{
				label: "<?php echo esc_html( $label ); ?>",
				value: "<?php echo esc_attr( $code ); ?>",
			},
		<?php endforeach; ?>
	];
</script>
