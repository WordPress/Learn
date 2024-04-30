<?php
/**
 * File containing the Course_Showcase_CPT_List class.
 *
 * @package sensei-pro
 * @since   1.12.0
 */

namespace Sensei_Pro\Course_Showcase;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class responsible for changing the CPT listing page.
 *
 * @since 1.12.0
 */
class Course_Showcase_CPT_List {
	/**
	 * Singleton instance.
	 *
	 * @var Course_Showcase_CPT_List
	 */
	private static $instance;

	/**
	 * The mapper for SenseiLMS.com.
	 *
	 * @var Course_Showcase_SenseiLMSCom_Mapper
	 */
	private $mapper;

	/**
	 * The course showcase listing CPT.
	 *
	 * @var Course_Showcase_Listing
	 */
	private $listing_cpt;

	/**
	 * Course_Showcase_CPT_List constructor.
	 *
	 * @param Course_Showcase_SenseiLMSCom_Mapper $mapper The SenseiLMSCom mapper.
	 * @param Course_Showcase_Listing             $listing_cpt The listing CPT.
	 */
	private function __construct( Course_Showcase_SenseiLMSCom_Mapper $mapper, Course_Showcase_Listing $listing_cpt ) {
		$this->mapper      = $mapper;
		$this->listing_cpt = $listing_cpt;
	}

	/**
	 * Fetch an instance of the class.
	 *
	 * @return Course_Showcase_CPT_List
	 */
	public static function instance(): Course_Showcase_CPT_List {
		if ( ! self::$instance ) {
			self::$instance = new self(
				new Course_Showcase_SenseiLMSCom_Mapper(),
				Course_Showcase_Listing::instance()
			);
		}

		return self::$instance;
	}

	/**
	 * Initialize the class.
	 */
	public static function init(): void {
		$instance = self::instance();

		$post_type = Course_Showcase_Listing::POST_TYPE;
		add_filter( 'display_post_states', [ $instance, 'hide_pending_state' ], 10, 2 );
		add_filter( "manage_{$post_type}_posts_columns", [ $instance, 'add_status_column' ] );
		add_action( "manage_{$post_type}_posts_custom_column", [ $instance, 'render_status_column' ], 10, 2 );
		add_action( 'wp', [ $instance, 'hydrate_status_cache' ] );

		add_filter( 'bulk_actions-edit-sensei_showcase', '__return_empty_array' );
		add_filter( 'months_dropdown_results', [ $instance, 'hide_months_dropdown' ], 10, 2 );
		add_filter( 'post_row_actions', [ $instance, 'change_action_names' ], 10, 2 );
	}

	/**
	 * Add columns to the CPT listing.
	 *
	 * @param array $columns The columns.
	 *
	 * @return array
	 */
	public function add_status_column( $columns ) {
		$columns['sensei_showcase_status'] = __( 'Status', 'sensei-pro' );
		unset( $columns['date'] );

		return $columns;
	}

	/**
	 * Render the columns in the CPT listing.
	 *
	 * @param string $column  The column name.
	 * @param int    $post_id The post ID.
	 */
	public function render_status_column( $column, $post_id ) {
		if ( 'sensei_showcase_status' !== $column ) {
			return;
		}

		$status = $this->listing_cpt->get_listing_status( $post_id );
		if ( $status instanceof \WP_Error ) {
			echo esc_html__( 'Unknown', 'sensei-pro' );
			return;
		}

		$status_label = $this->mapper->get_status_code_label( $status['status_code'], false );

		echo esc_html( $this->mapper->get_listing_status_label( $status['status'] ) );

		if ( in_array( $status['status'], [ 'rejected', 'inaccessible' ], true ) ) {
			echo "<div class='notice inline notice-warning notice-alt'>";
			echo '<p style="margin-bottom: 0;">' . esc_html( $status_label ) . '</p>';
			echo '</div>';
		}
	}

	/**
	 * Hide the pending state from the post states.
	 *
	 * @internal
	 *
	 * @param array    $post_states The post states.
	 * @param \WP_Post $post The post object.
	 *
	 * @return array
	 */
	public function hide_pending_state( $post_states, $post ): array {
		if ( is_null( $post ) ) {
			return $post_states;
		}

		if ( Course_Showcase_Listing::POST_TYPE !== $post->post_type ) {
			return $post_states;
		}

		if ( isset( $post_states['pending'] ) ) {
			unset( $post_states['pending'] );
		}

		return $post_states;
	}

	/**
	 * Hydrate the status cache.
	 *
	 * @internal
	 */
	public function hydrate_status_cache(): void {
		global $wp_query;

		if ( ! is_admin() || ! function_exists( 'get_current_screen' ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || Course_Showcase_Listing::POST_TYPE !== $screen->post_type ) {
			return;
		}

		$ids = wp_list_pluck( $wp_query->posts, 'ID' );

		// Hydrate the cache for the first 5 listings.
		$this->listing_cpt->get_listing_statuses( array_slice( $ids, 0, 5 ) );
	}

	/**
	 * Hide the months dropdown.
	 *
	 * @param array  $months    The months.
	 * @param string $post_type The post type.
	 *
	 * @return array
	 */
	public function hide_months_dropdown( $months, $post_type ) {
		if ( Course_Showcase_Listing::POST_TYPE === $post_type ) {
			return [];
		}

		return $months;
	}

	/**
	 * Change the action names.
	 *
	 * @param array    $actions The actions.
	 * @param \WP_Post $post    The post object.
	 *
	 * @return array
	 */
	public function change_action_names( $actions, $post ) {
		if ( Course_Showcase_Listing::POST_TYPE !== $post->post_type ) {
			return $actions;
		}

		// Quick Edit.
		unset( $actions['inline hide-if-no-js'] );

		if ( isset( $actions['trash'] ) && $this->listing_cpt->has_been_submitted( $post->ID ) ) {
			$title            = _draft_or_post_title();
			$actions['trash'] = sprintf(
				'<a href="%s" class="submitdelete" aria-label="%s">%s</a>',
				get_delete_post_link( $post->ID ),
				/* translators: %s: Post title. */
				esc_attr( sprintf( __( 'Deactivate', 'sensei-pro' ), $title ) ),
				_x( 'Deactivate', 'verb', 'sensei-pro' )
			);

		}

		return $actions;
	}

}
