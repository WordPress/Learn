<?php
/**
 * File containing the Join_Group_Blocks class.
 *
 * @package student-groups
 */

namespace Sensei_Pro_Student_Groups\Blocks;

use Sensei_Pro_Student_Groups\Student_Groups;
use function Sensei_Pro\Modules\assets_loader;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Join Group Block.
 *
 * @since 1.18.0
 */
class Join_Group_Blocks {
	/**
	 * Assets loader for JS files.
	 *
	 * @var \Sensei_Pro\Assets
	 */
	private $js_assets;

	/**
	 * Assets loader for CSS files.
	 *
	 * @var \Sensei_Pro\Assets
	 */
	private $css_assets;

	/**
	 * Group student repository.
	 *
	 * @var Group_Student_Repository
	 */
	private $group_student_repository;

	/**
	 * Group persisted after got for the first time from the URL.
	 *
	 * @var WP_Post|false
	 */
	private $group;

	/**
	 * Join_Group_Blocks constructor.
	 *
	 * @param Group_Student_Repository $group_student_repository Group student repository.
	 */
	public function __construct( $group_student_repository ) {
		$this->js_assets  = assets_loader( Student_Groups::MODULE_NAME );
		$this->css_assets = assets_loader( 'style-' . Student_Groups::MODULE_NAME );

		$this->group_student_repository = $group_student_repository;
	}

	/**
	 * Initialize hooks.
	 */
	public function init() {
		add_action( 'enqueue_block_assets', [ $this, 'enqueue_block_assets' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_assets' ] );
		add_action( 'template_redirect', [ $this, 'maybe_join_group' ] );
		add_action( 'init', [ $this, 'register_blocks' ] );
	}

	/**
	 * Enqueue block assets.
	 *
	 * @internal
	 */
	public function enqueue_block_assets() {
		$this->js_assets->register( 'sensei-join-group-block', 'join-group-block.js' );
		$this->css_assets->register( 'sensei-join-group-block-style', 'join-group-block.css' );
		$this->js_assets->register( 'sensei-group-name-block', 'group-name-block.js' );
		$this->js_assets->register( 'sensei-group-members-count-block', 'group-members-count-block.js' );
		$this->js_assets->register( 'sensei-group-members-list-block', 'group-members-list-block.js' );
		$this->css_assets->register( 'sensei-group-members-list-block-style', 'group-members-list-block.css' );
	}

	/**
	 * Enqueue block editor assets.
	 */
	public function enqueue_block_editor_assets() {
		$this->js_assets->enqueue( 'sensei-join-group-button-variation', 'join-group-button-variation.js' );
	}

	/**
	 * Register block.
	 *
	 * @internal
	 */
	public function register_blocks() {
		register_block_type_from_metadata(
			SENSEI_PRO_PLUGIN_DIR_PATH . 'modules/student-groups/assets/blocks/join-group-block/',
			[
				'render_callback' => [ $this, 'render_join_group_block' ],
				'editor_script'   => 'sensei-join-group-block',
				'style'           => 'sensei-join-group-block-style',
			]
		);

		register_block_type_from_metadata(
			SENSEI_PRO_PLUGIN_DIR_PATH . 'modules/student-groups/assets/blocks/group-name-block/',
			[
				'render_callback' => [ $this, 'render_group_name_block' ],
				'editor_script'   => 'sensei-group-name-block',
			]
		);

		register_block_type_from_metadata(
			SENSEI_PRO_PLUGIN_DIR_PATH . 'modules/student-groups/assets/blocks/group-members-count-block/',
			[
				'render_callback' => [ $this, 'render_group_members_count_block' ],
				'editor_script'   => 'sensei-group-members-count-block',
			]
		);

		register_block_type_from_metadata(
			SENSEI_PRO_PLUGIN_DIR_PATH . 'modules/student-groups/assets/blocks/group-members-list-block/',
			[
				'render_callback' => [ $this, 'render_group_members_list_block' ],
				'editor_script'   => 'sensei-group-members-list-block',
				'style'           => 'sensei-group-members-list-block-style',
			]
		);
	}

	/**
	 * Render Join Group block.
	 *
	 * @internal
	 *
	 * @param array  $attributes Block attributes.
	 * @param string $content    Block HTML.
	 *
	 * @return string Block output.
	 */
	public function render_join_group_block( $attributes, $content ) {
		$group = $this->get_group_from_url();

		if ( ! $group ) {
			return $this->render_join_group_block_with_full_error_message(
				__( 'Oops! Group not found! Please request a new link and try again.', 'sensei-pro' ),
				$content
			);
		}

		if ( ! is_user_logged_in() ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$group_code   = ! empty( $_GET['group-code'] ) ? sanitize_text_field( wp_unslash( $_GET['group-code'] ) ) : '';
			$current_link = add_query_arg( 'group-code', $group_code, get_permalink() );
			$login_url    = sensei_user_login_url( $current_link );
			$login_link   = '<a href="' . $login_url . '">' . __( 'log in', 'sensei-pro' ) . '</a>';

			return $this->render_join_group_block_message(
				sprintf(
					// translators: Placeholder is a link to log in.
					esc_html__( 'Please %1$s to join this group.', 'sensei-pro' ),
					$login_link
				),
				'alert',
				$content,
				true
			);
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Arguments used for comparison.
		if ( ! empty( $_GET['join-success'] ) && '1' === $_GET['join-success'] ) {
			return $this->render_join_group_block_message(
				esc_html__( 'You joined this group!', 'sensei-pro' ),
				'success',
				$content,
				true
			);
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Arguments used for comparison.
		if ( ! empty( $_GET['join-error'] ) && '1' === $_GET['join-error'] ) {
			return $this->render_join_group_block_message(
				esc_html__( 'Oops! Something went wrong. Please try again.', 'sensei-pro' ),
				'alert',
				$content
			);
		}

		$current_user = wp_get_current_user();

		if ( $this->group_student_repository->find_by_group_and_user( $group, $current_user ) ) {
			return $this->render_join_group_block_message(
				esc_html__( 'You are already a member of this group.', 'sensei-pro' ),
				'success',
				$content,
				true
			);
		}

		return $this->render_join_group_with_join_button( $group, $content );
	}

	/**
	 * Render block with join button.
	 *
	 * @param WP_Post $group   Group.
	 * @param string  $content Block HTML.
	 *
	 * @return string Block output.
	 */
	private function render_join_group_with_join_button( $group, $content ) {
		// Add join URL to the button.
		$action   = $this->get_join_nonce_action( $group->ID );
		$join_url = add_query_arg( 'join-group', '1' );
		$join_url = wp_nonce_url( $join_url, $action );

		return str_replace(
			'#{{joinGroupUrl}}',
			esc_html( $join_url ),
			$content
		);
	}

	/**
	 * Join group if it's defined in the URL and then redirect.
	 *
	 * @internal
	 */
	public function maybe_join_group() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Arguments used for comparison.
		if ( empty( $_GET['join-group'] ) || '1' !== $_GET['join-group'] ) {
			return;
		}

		$group        = $this->get_group_from_url();
		$current_user = wp_get_current_user();
		$raw_url      = remove_query_arg( [ 'join-group', '_wpnonce' ] );
		$error_url    = add_query_arg( 'join-error', '1', $raw_url );
		$success_url  = add_query_arg( 'join-success', '1', $raw_url );
		$action       = $this->get_join_nonce_action( $group->ID );

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Do not change the nonce.
		if ( empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( wp_unslash( $_GET['_wpnonce'] ), $action ) ) {
			$this->redirect_after_join_group( $error_url );
		}

		try {
			$this->group_student_repository->create( $group, $current_user );
		} catch ( \Exception $e ) {
			$this->redirect_after_join_group( $error_url );
		}

		$this->redirect_after_join_group( $success_url );
	}

	/**
	 * Redirect after joining group.
	 *
	 * @access private
	 *
	 * @param string $url URL to be redirected.
	 */
	protected function redirect_after_join_group( $url ) {
		wp_safe_redirect( $url );
		exit;
	}

	/**
	 * Get join nonce action.
	 *
	 * @param int $group_id Group ID.
	 *
	 * @return string The nonce action.
	 */
	private function get_join_nonce_action( $group_id ) {
		return 'join-group-' . $group_id;
	}

	/**
	 * Render block with error message in the whole block.
	 *
	 * @param string $message Error message.
	 * @param string $content Block HTML.
	 *
	 * @return string Block output.
	 */
	private function render_join_group_block_with_full_error_message( $message, $content ) {
		return preg_replace(
			'/<div(.*?)>.*<\/div>/s',
			'<div $1><div class="sensei-message alert wp-block-sensei-pro-join-group__full-message">' . esc_html( $message ) . '</div></div>',
			$content
		);
	}

	/**
	 * Render block with a message in the bottom.
	 *
	 * @param string $message        Message.
	 * @param string $type           Message type: alert or success.
	 * @param string $content        Block HTML.
	 * @param bool   $disable_button Whether button should be disabled.
	 *
	 * @return string Block output.
	 */
	private function render_join_group_block_message( $message, $type, $content, $disable_button = false ) {
		$output = preg_replace(
			'/<\/div>\s*$/s',
			'<div class="sensei-message ' . esc_attr( $type ) . ' wp-block-sensei-pro-join-group__message">' . $message . '</div></div>',
			$content,
			1
		);

		if ( $disable_button ) {
			$output = str_replace(
				'wp-block-sensei-pro-join-group__button',
				'wp-block-sensei-pro-join-group__button wp-block-sensei-pro-join-group__button-disabled',
				$output,
			);
		}

		return $output;
	}

	/**
	 * Render Group Name block.
	 *
	 * @internal
	 *
	 * @param array  $attributes Block attributes.
	 * @param string $content    Block HTML.
	 *
	 * @return string Block output.
	 */
	public function render_group_name_block( $attributes, $content ) {
		$group = $this->get_group_from_url();

		if ( ! $group ) {
			return '';
		}

		return str_replace(
			'{{groupName}}',
			esc_html( $group->post_title ),
			$content
		);
	}

	/**
	 * Render Group Members Count block.
	 *
	 * @internal
	 *
	 * @param array  $attributes Block attributes.
	 * @param string $content    Block HTML.
	 *
	 * @return string Block output.
	 */
	public function render_group_members_count_block( $attributes, $content ) {
		$group = $this->get_group_from_url();

		if ( ! $group ) {
			return '';
		}

		$members_count = $this->group_student_repository->get_count_for_group( $group->ID );

		if ( 0 === $members_count ) {
			$members_count = __( 'Be the first member of this group.', 'sensei-pro' );
		} else {
			$members_count = sprintf(
				// translators: %d: number of members.
				_n( '%d member', '%d members', $members_count, 'sensei-pro' ),
				$members_count
			);
		}

		return str_replace(
			'{{groupMembersCount}}',
			esc_html( $members_count ),
			$content
		);
	}

	/**
	 * Render Group Members List block.
	 *
	 * @internal
	 *
	 * @param array  $attributes Block attributes.
	 * @param string $content    Block HTML.
	 *
	 * @return string Block output.
	 */
	public function render_group_members_list_block( $attributes, $content ) {
		$group = $this->get_group_from_url();

		if ( ! $group ) {
			return '';
		}

		$student_ids = $this->group_student_repository->find_group_students( $group->ID );

		if ( empty( $student_ids ) ) {
			return '';
		}

		$members_list = '<ul class="wp-block-sensei-pro-group-members-list__list">';
		$maximum      = $attributes['numberOfMembers'];

		if ( $maximum > 0 ) {
			$student_ids = array_slice( $student_ids, 0, $maximum );
		}

		foreach ( $student_ids as $student_id ) {
			$user = get_userdata( $student_id );

			$members_list .= sprintf(
				'<li class="wp-block-sensei-pro-group-members-list__item">
					<img class="wp-block-sensei-pro-group-members-list__avatar" src="%s" alt="%s" />
				</li>',
				get_avatar_url( $user->ID, [ 'size' => '48' ] ),
				$user->display_name . ' avatar'
			);
		}

		$members_list .= '</ul>';

		return str_replace(
			'{{groupMembersList}}',
			$members_list,
			$content
		);
	}

	/**
	 * Get group by code.
	 *
	 * @param string $code Group code.
	 *
	 * @return WP_Post|null
	 */
	private function get_group_by_code( $code ) {
		$group = get_posts(
			[
				'post_status' => 'publish',
				'post_type'   => Student_Groups::GROUP_POST_TYPE,
				'meta_key'    => Student_Groups::GROUP_SIGNUP_CODE_META_NAME,
				'meta_value'  => $code,
			]
		);

		if ( empty( $group ) ) {
			return null;
		}

		return $group[0];
	}

	/**
	 * Get group from URL.
	 *
	 * @return WP_Post|false Group post or false if group was not found.
	 */
	private function get_group_from_url() {
		if ( isset( $this->group ) ) {
			return $this->group;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( empty( $_GET['group-code'] ) ) {
			$this->group = false;
		} else {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$code        = sanitize_text_field( wp_unslash( $_GET['group-code'] ) );
			$this->group = $this->get_group_by_code( $code );

			if ( ! $this->group ) {
				$this->group = false;
			}
		}

		return $this->group;
	}
}
