<?php

namespace WPOrg_Learn\Redirects;

add_action( 'template_redirect', __NAMESPACE__ . '\wporg_learn_redirect_meetings' );
add_action( 'template_redirect', __NAMESPACE__ . '\wporg_learn_redirect_old_urls' );

add_filter( 'allowed_redirect_hosts', __NAMESPACE__ . '\wporg_learn_allowed_redirect_hosts' );

/**
 * Add allowed redirect hosts.
 *
 * @param array $hosts The array of allowed redirect hosts.
 * @return array The updated array of allowed redirect hosts.
 */
function wporg_learn_allowed_redirect_hosts( $hosts ) {
	return array_merge( $hosts, array( 'wordpress.tv', 'make.wordpress.org' ) );
};

/**
 * Redirect meeting posts to associated link
 *
 * @return void
 */
function wporg_learn_redirect_meetings() {
	global $post;

	if ( is_singular( array( 'meeting' ) ) ) {

		if ( ! empty( $post->ID ) ) {

			$redirect = wp_http_validate_url( get_post_meta( $post->ID, 'link', true ) );

			if ( $redirect && wp_redirect( $redirect ) ) {
				exit;
			}
		}
	}
}

/**
 * Redirect old pages to their new homes.
 *
 * @return void
 */
function wporg_learn_redirect_old_urls() {
	if ( ! is_404() ) {
		return;
	}

	// Redirection arrays use the format Source => Destination
	// Any characters after the source will be appended to the destination.

	$pages = array(
		'/handbook'                       => 'https://make.wordpress.org/training/handbook/',
		'/report-content-errors'          => '/report-content-feedback',
		'/social-learning'                => '/online-workshops',
		'/workshop/'                      => '/tutorial/',
		'/workshop-presenter-application' => '/tutorial-presenter-application',
		'/workshops'                      => '/tutorials',
	);

	$tutorials = array(

		// Tutorial -> Lesson migration Nov 2024
		'/tutorial/testing-your-products-for-php-version-compatibility/'                          => '/lesson/testing-your-products-for-php-version-compatibility/',
		'/tutorial/common-apis-internationalization/'                                             => '/lesson/common-apis-internationalization/',
		'/tutorial/applying-duotone-filters-to-change-color-effects/'                             => '/lesson/applying-duotone-filters-to-change-color-effects/',
		'/tutorial/custom-database-tables/'                                                       => '/lesson/custom-database-tables/',
		'/tutorial/common-apis-responsive-images/'                                                => '/lesson/common-apis-responsive-images/',
		'/tutorial/common-apis-dashboard-widgets/'                                                => '/lesson/common-apis-dashboard-widgets/',
		'/tutorial/improving-website-performance-with-caching/'                                   => '/lesson/improving-website-performance-with-caching/',
		'/tutorial/adding-a-sticky-header-or-banner/'                                             => '/lesson/adding-a-sticky-header-or-banner/',
		'/tutorial/managing-updates/'                                                             => '/lesson/managing-updates/',
		'/tutorial/building-sidebars-with-the-site-editor/'                                       => '/lesson/building-sidebars-with-the-site-editor/',
		'/tutorial/wordpress-under-the-hood/'                                                     => '/lesson/wordpress-under-the-hood/',
		'/tutorial/hi-adding-a-sticky-header-or-banner/'                                          => '/lesson/hi-adding-a-sticky-header-or-banner/',
		'/tutorial/hi-applying-duotone-filters-to-change-color-effects/'                          => '/lesson/hi-applying-duotone-filters-to-change-color-effects/',
		'/tutorial/hi-scheduling-posts-and-pages/'                                                => '/lesson/hi-scheduling-posts-and-pages/',
		'/tutorial/bn-adding-a-sticky-header-or-banner/'                                          => '/lesson/bn-adding-a-sticky-header-or-banner/',
		'/tutorial/how-to-test-wordpress-beta-release/'                                           => '/lesson/how-to-test-wordpress-beta-release/',
		'/tutorial/how-to-create-a-menu-with-the-navigation-block-3/'                             => '/lesson/how-to-create-a-menu-with-the-navigation-block-3/',
		'/tutorial/introducing-the-twenty-twenty-four-theme/'                                     => '/lesson/introducing-the-twenty-twenty-four-theme/',
		'/tutorial/working-faster-with-the-command-palette/'                                      => '/lesson/working-faster-with-the-command-palette/',
		'/tutorial/how-to-create-a-video-tutorial-for-learn-wordpress-org/'                       => '/lesson/how-to-create-a-video-tutorial-for-learn-wordpress-org/',
		'/tutorial/how-to-use-the-spacer-block/'                                                  => '/lesson/how-to-use-the-spacer-block/',
		'/tutorial/how-to-add-tables-to-your-site/'                                               => '/lesson/how-to-add-tables-to-your-site/',
		'/tutorial/wpphotos-moderator-training/'                                                  => '/lesson/wpphotos-moderator-training/',
		'/tutorial/using-the-block-editor-tips-and-shortcuts-for-efficiency/'                     => '/lesson/using-the-block-editor-tips-and-shortcuts-for-efficiency/',
		'/tutorial/wordpress-editor-modes-for-streamlining-content-creation/'                     => '/lesson/wordpress-editor-modes-for-streamlining-content-creation/',
		'/tutorial/rapid-website-recreation/'                                                     => '/lesson/rapid-website-recreation/',
		'/tutorial/add-media-and-openverse-images-to-your-content-directly-from-the-inserter/'    => '/lesson/add-media-and-openverse-images-to-your-content-directly-from-the-inserter/',
		'/tutorial/how-to-use-the-wordpress-stylebook-with-your-block-theme/'                     => '/lesson/how-to-use-the-wordpress-stylebook-with-your-block-theme/',
		'/tutorial/displaying-testimonials-on-your-website/'                                      => '/lesson/displaying-testimonials-on-your-website/',
		'/tutorial/creating-a-call-to-action/'                                                    => '/lesson/creating-a-call-to-action/',
		'/tutorial/the-key-to-locking-blocks/'                                                    => '/lesson/the-key-to-locking-blocks/',
		'/tutorial/padding-versus-margin/'                                                        => '/lesson/padding-versus-margin/',
		'/tutorial/block-spacing/'                                                                => '/lesson/block-spacing/',
		'/tutorial/adding-a-contact-form-to-your-site/'                                           => '/lesson/adding-a-contact-form-to-your-site/',
		'/tutorial/introduction-to-block-theme-development-for-beginners/'                        => '/lesson/introduction-to-block-theme-development-for-beginners/',
		'/tutorial/horizontal-pyramid-gallery/'                                                   => '/lesson/horizontal-pyramid-gallery/',
		'/tutorial/using-schema-with-wordpress-theme-json/'                                       => '/lesson/using-schema-with-wordpress-theme-json/',
		'/tutorial/creating-custom-post-types-without-code/'                                      => '/lesson/creating-custom-post-types-without-code/',
		'/tutorial/finding-images-using-the-wordpress-photo-directory/'                           => '/lesson/finding-images-using-the-wordpress-photo-directory/',
		'/tutorial/finding-images-using-openverse/'                                               => '/lesson/finding-images-using-openverse/',
		'/tutorial/how-to-create-low-code-block-patterns/'                                        => '/lesson/how-to-create-low-code-block-patterns/',
		'/tutorial/making-a-plugin-inside-the-dashboard/'                                         => '/lesson/making-a-plugin-inside-the-dashboard/',
		'/tutorial/local-wordpress-installations-for-beginners/'                                  => '/lesson/local-wordpress-installations-for-beginners/',
		'/tutorial/what-is-the-difference-between-wordpress-org-and-com/'                         => '/lesson/what-is-the-difference-between-wordpress-org-and-com/',
		'/tutorial/how-to-use-github-for-gutenberg/'                                              => '/lesson/how-to-use-github-for-gutenberg/',
		'/tutorial/creating-a-welcoming-and-diverse-space-part-2/'                                => '/lesson/creating-a-welcoming-and-diverse-space-part-2/',
		'/tutorial/creating-a-welcoming-and-diverse-space-part-1/'                                => '/lesson/creating-a-welcoming-and-diverse-space-part-1/',
		'/tutorial/how-to-do-triage-on-github/'                                                   => '/lesson/how-to-do-triage-on-github/',
		'/tutorial/how-to-use-trac/'                                                              => '/lesson/how-to-use-trac/',
		'/tutorial/storytelling-for-tech-talks/'                                                  => '/lesson/storytelling-for-tech-talks/',
		'/tutorial/advanced-layouts-with-the-block-editor/'                                       => '/lesson/advanced-layouts-with-the-block-editor/',
		'/tutorial/using-wordpress-in-other-languages/'                                           => '/lesson/using-wordpress-in-other-languages/',
		'/tutorial/organizing-wordpress-meetups-supporting-an-online-meetup/'                     => '/lesson/organizing-wordpress-meetups-supporting-an-online-meetup/',
		'/tutorial/organizing-wordpress-meetups-getting-started/'                                 => '/lesson/organizing-wordpress-meetups-getting-started/',
		'/tutorial/contributing-on-the-wordpress-community-team/'                                 => '/lesson/contributing-on-the-wordpress-community-team/',
		'/tutorial/diverse-speaker-training-workshop-part-4/'                                     => '/lesson/diverse-speaker-training-workshop-part-4/',
		'/tutorial/diverse-speaker-training-workshop-part-3/'                                     => '/lesson/diverse-speaker-training-workshop-part-3/',
		'/tutorial/online-stage-presence/'                                                        => '/lesson/online-stage-presence/',
		'/tutorial/diverse-speaker-training-workshop-part-2/'                                     => '/lesson/diverse-speaker-training-workshop-part-2/',
		'/tutorial/diverse-speaker-training-workshop-part-1/'                                     => '/lesson/diverse-speaker-training-workshop-part-1/',
		'/tutorial/an-introduction-to-contributing/'                                              => '/lesson/an-introduction-to-contributing/',
		'/tutorial/introduction-to-open-source/'                                                  => '/lesson/introduction-to-open-source/',
		'/tutorial/hi-how-to-test-wordpress-beta-release/'                                        => '/lesson/hi-how-to-test-wordpress-beta-release/',
		'/tutorial/fr-how-to-test-wordpress-beta-release/'                                        => '/lesson/fr-how-to-test-wordpress-beta-release/',
		'/tutorial/bn-working-faster-with-the-command-palette/'                                   => '/lesson/bn-working-faster-with-the-command-palette/',
		'/tutorial/hi-block-spacing/'                                                             => '/lesson/hi-block-spacing/',
		'/tutorial/hi-add-media-and-openverse-images-to-your-content-directly-from-the-inserter/' => '/lesson/hi-add-media-and-openverse-images-to-your-content-directly-from-the-inserter/',
		'/tutorial/bn-finding-images-using-the-wordpress-photo-directory/'                        => '/lesson/bn-finding-images-using-the-wordpress-photo-directory/',
		'/tutorial/gu-how-to-use-github-for-gutenberg/'                                           => '/lesson/gu-how-to-use-github-for-gutenberg/',
		'/tutorial/gu-how-to-do-triage-on-github/'                                                => '/lesson/gu-how-to-do-triage-on-github/',
		'/tutorial/bn-introducing-the-twenty-twenty-four-theme/'                                  => '/lesson/bn-introducing-the-twenty-twenty-four-theme/',
		'/tutorial/gu-how-to-start-using-wordpress-playground/'                                   => '/lesson/gu-how-to-start-using-wordpress-playground/',
		'/tutorial/gerenciando-atualizacoes-no-wordpress/'                                        => '/lesson/gerenciando-atualizacoes-no-wordpress/',
		'/tutorial/introducao-ao-editor-de-site-e-ao-editor-de-modelos/'                          => '/lesson/introducao-ao-editor-de-site-e-ao-editor-de-modelos/',
		'/tutorial/como-criar-um-post-ou-pagina-com-o-editor-de-blocos-wordpress/'                => '/lesson/como-criar-um-post-ou-pagina-com-o-editor-de-blocos-wordpress/',
		'/tutorial/introducao-a-visualizacao-em-lista/'                                           => '/lesson/introducao-a-visualizacao-em-lista/',
		'/tutorial/como-escolher-e-instalar-plugins-wordpress/'                                   => '/lesson/como-escolher-e-instalar-plugins-wordpress/',
		'/tutorial/block-editor-02-text-blocks/'                                                  => '/lesson/block-editor-02-text-blocks/',
		'/tutorial/block-editor-01-basics/'                                                       => '/lesson/block-editor-01-basics/',
		'/tutorial/conociendo-el-escritorio-de-wordpress/'                                        => '/lesson/conociendo-el-escritorio-de-wordpress/',
		'/tutorial/editor-de-bloques-puesta-en-marcha-de-un-entorno-de-desarrollo/'               => '/lesson/editor-de-bloques-puesta-en-marcha-de-un-entorno-de-desarrollo/',
		'/tutorial/contribuir-con-el-equipo-de-tv/'                                               => '/lesson/contribuir-con-el-equipo-de-tv/',
		'/tutorial/seguridad-formularios-acceso-y-contrasena/'                                    => '/lesson/seguridad-formularios-acceso-y-contrasena/',
		'/tutorial/editor-de-bloques-crear-un-bloque/'                                            => '/lesson/editor-de-bloques-crear-un-bloque/',
		'/tutorial/ja-login-password-reset/'                                                      => '/lesson/ja-login-password-reset/',
		'/tutorial/wordpress-plugin-translation-instruction-in-japanese/'                         => '/lesson/wordpress-plugin-translation-instruction-in-japanese/',
		'/tutorial/bn-how-to-test-wordpress-beta-release/'                                        => '/lesson/bn-how-to-test-wordpress-beta-release/',
		'/tutorial/bn-how-to-create-low-code-block-patterns/'                                     => '/lesson/bn-how-to-create-low-code-block-patterns/',
		'/tutorial/bn-wordpress-editor-modes-for-streamlining-content-creation/'                  => '/lesson/bn-wordpress-editor-modes-for-streamlining-content-creation/',
	);

	$courses = array(
		'/course/simple-site-design-with-full-site-editing/'                                                   => '/learning-pathway/user/',
		'/course/part-2-personalized-site-design-with-full-site-editing-and-theme-blocks/'                     => '/learning-pathway/user/',
		'/course/part-3-advanced-site-design-with-full-site-editing-site-editor-templates-and-template-parts/' => '/learning-pathway/user/',
		'/course/developing-with-the-wordpress-rest-api/'                                                      => '/course/beginner-wordpress-developer/',
		'/course/converting-a-shortcode-to-a-block/'                                                           => '/course/beginner-wordpress-developer/',
		'/course/a-developers-guide-to-block-themes-part-1/'                                                   => '/course/intermediate-theme-developer/',
		'/course/a-developers-guide-to-block-themes-part-2/'                                                   => '/course/intermediate-theme-developer/',
	);

	$redirects = array_merge( $pages, $tutorials, $courses );

	// Use `REQUEST_URI` rather than `$wp->request`, to get the entire source URI including url parameters.
	$request = $_SERVER['REQUEST_URI'] ?? '';

	foreach ( $redirects as $source => $destination ) {
		if ( str_starts_with( $request, $source ) ) {
			$redirect = $destination;
			$code = 301;

			// Append any extra request parameters.
			if ( strlen( $request ) > strlen( $source ) ) {
				$redirect .= substr( $request, strlen( $source ) );
			}

			wp_safe_redirect( $redirect, $code, 'Learn WordPress' );
			exit;
		}
	}
}
