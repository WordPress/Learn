<?php

namespace WPOrg_Learn\Admin;

defined( 'WPINC' ) || die();

/**
 * Actions and filters.
 */
add_action( 'admin_notices', __NAMESPACE__ . '\show_term_translation_notice' );

/**
 * Show a notice on taxonomy term screens about terms being translatable.
 *
 * @return void
 */
function show_term_translation_notice() {
	global $pagenow, $taxnow, $typenow;

	if ( 'edit-tags.php' !== $pagenow ) {
		return;
	}

	$valid_post_types = array(
		'lesson-plan',
		'wporg_workshop',
		'course',
		'lesson',
	);

	if ( ! in_array( $typenow, $valid_post_types, true ) ) {
		return;
	}

	if ( empty( $taxnow ) ) {
		return;
	}

	$taxonomy = get_taxonomy( $taxnow );
	$labels   = get_taxonomy_labels( $taxonomy );

	?>
	<div class="notice notice-info is-dismissible">
		<p>
			<?php
			printf(
				wp_kses_post( __( '
					Names and descriptions of %1$s can be translated via the Learn WordPress <a href="%2$s">translation project</a>. Once you have added or changed a term\'s name or description, it may take up to 24 hours before it is available for translation.
				', 'wporg-learn' ) ),
				esc_html( $labels->name ),
				'https://translate.wordpress.org/projects/meta/learn-wordpress/'
			);
			?>
		</p>
	</div>
	<?php
}
