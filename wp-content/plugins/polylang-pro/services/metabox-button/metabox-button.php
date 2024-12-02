<?php
/**
 * @package Polylang-Pro
 */

/**
 * Abstract class for features needing a button in the language metabox.
 *
 * @since 2.1
 */
abstract class PLL_Metabox_Button {
	/**
	 * Id used for the css class.
	 *
	 * @var string
	 */
	public $id;

	/**
	 * Arguments used to create the button.
	 *
	 * @var array
	 */
	public $args;

	/**
	 * Constructor
	 *
	 * @since 2.1
	 *
	 * Parameters must be provided by the child class.
	 *
	 * @param string $id   Id used for the css class.
	 * @param array  $args {
	 *  Arguments used to create the button.
	 *
	 *  @type string $position   Defines the position of the button. Accepted values are
	 *                           'before_post_translations' and 'before_post_translation_{$language_code}'.
	 *  @type string $activate   Text displayed to activate the button.
	 *  @type string $deactivate Text displayed to deactivate the button.
	 *  @type string $class      Optional. Classes defining the icon to display.
	 *  @type string $icon       Optional. A svg icon, required only if not using Dashicons.
	 *  @type string $before     Optional. HTML markup placed before the button.
	 *  @type string $after      Optional. HTML markup placed after the button.
	 * }
	 *
	 * @phpstan-param non-empty-string $id
	 * @phpstan-param array{
	 *     position: non-falsy-string,
	 *     activate: string,
	 *     deactivate: string,
	 *     class?: string,
	 *     icon?: string,
	 *     before?: string,
	 *     after?: string
	 * } $args
	 */
	public function __construct( $id, $args ) {
		$this->id   = $id;
		$this->args = array_merge(
			array(
				'class'    => '',
				'icon'     => '',
				'before'   => '',
				'after'    => '',
				'priority' => 10,
			),
			$args
		);

		if ( 'before_post_translations' === $args['position'] ) {
			$this->args['class'] .= ' pll-before-post-translations-button';
		}

		add_action( 'pll_' . $args['position'], array( $this, 'add_icon' ), $this->args['priority'] );
		add_action( 'wp_ajax_toggle_' . $id, array( $this, 'toggle' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Tells whether the button is active or not.
	 *
	 * @since 2.1
	 *
	 * @return bool
	 */
	abstract public function is_active();

	/**
	 * Saves the button state.
	 *
	 * @since 2.1
	 *
	 * @param string $post_type Current post type.
	 * @param bool   $active    New requested button state.
	 * @return bool Whether the new button state is accepted or not.
	 */
	protected function toggle_option( $post_type, $active ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		return true;
	}

	/**
	 * Displays the button.
	 *
	 * @since 2.1
	 *
	 * @param string $post_type The current post type.
	 * @return void
	 */
	public function add_icon( $post_type ) {
		if ( 'attachment' !== $post_type ) {
			echo $this->get_html( $this->is_active() ); // phpcs:ignore WordPress.Security.EscapeOutput
		}
	}

	/**
	 * Ajax response to a clic on the button.
	 *
	 * @since 2.1
	 *
	 * @return void
	 */
	public function toggle() {
		check_ajax_referer( 'pll_language', '_pll_nonce' );

		if ( isset( $_POST['value'], $_POST['post_type'] ) ) {
			$is_active = 'false' === $_POST['value'];
			$post_type = sanitize_key( $_POST['post_type'] );

			if ( post_type_exists( $post_type ) && $this->toggle_option( $post_type, $is_active ) ) {
				$x = new WP_Ajax_Response( array( 'what' => 'icon', 'data' => $this->get_text( $is_active ) ) );
				$x->send();
			}
		}

		wp_die( 0 );
	}

	/**
	 * Get the text for the button title depending on its state.
	 *
	 * @since 2.1
	 *
	 * @param bool $is_active Whether the button is already active or not.
	 * @return string
	 */
	protected function get_text( $is_active ) {
		return $is_active ? $this->args['deactivate'] : $this->args['activate'];
	}

	/**
	 * Returns the html to display the button.
	 *
	 * @since 2.1
	 *
	 * @param bool $is_active Whether the button is already active or not.
	 * @return string
	 */
	protected function get_html( $is_active ) {
		$text = $this->get_text( $is_active );

		return sprintf(
			'%6$s<button type="button" id="%1$s" class="pll-button %2$s" title="%3$s">%8$s<span class="screen-reader-text">%4$s</span></button><input name="%1$s" type="hidden" value="%5$s" />%7$s',
			$this->id,
			esc_attr( $this->args['class'] ) . ( $is_active ? ' wp-ui-text-highlight' : '' ),
			esc_attr( $text ),
			esc_html( $text ),
			$is_active ? 'true' : 'false',
			$this->args['before'],
			$this->args['after'],
			$this->args['icon']
		);
	}

	/**
	 * Enqueues script and style.
	 *
	 * @since 2.8
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		$screen = get_current_screen();

		if ( $screen && in_array( $screen->base, array( 'post', 'media' ) ) && ! wp_script_is( 'pll_metabox_button', 'enqueued' ) ) {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_script(
				'pll_metabox_button',
				plugins_url( '/js/build/metabox-button' . $suffix . '.js', POLYLANG_ROOT_FILE ),
				array( 'jquery', 'wp-ajax-response', 'post' ),
				POLYLANG_VERSION,
				true
			);

			wp_localize_script(
				'pll_metabox_button',
				'pll_sync_post',
				array( 'confirm_text' => __( 'You are about to overwrite an existing translation. Are you sure you want to proceed?', 'polylang-pro' ) )
			);

			wp_enqueue_style(
				'pll_metabox_button',
				plugins_url( '/css/build/metabox-button' . $suffix . '.css', POLYLANG_ROOT_FILE ),
				array(),
				POLYLANG_VERSION
			);
		}
	}
}
