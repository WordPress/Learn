<?php
/**
 * @package Polylang-Pro
 */

defined( 'ABSPATH' ) || exit;

/**
 * A class to work with REST routes for templates.
 *
 * @since 3.2
 */
class PLL_FSE_REST_Route {

	/**
	 * The route.
	 *
	 * @var string
	 */
	private $route = '';

	/**
	 * The route after regex matching.
	 *
	 * @var array {
	 *     @type int|null    $post_id   The post ID.
	 *     @type string|null $post_type The post type.
	 *     @type string|null $rest_base The REST base matching the supported template types.
	 * }
	 * @phpstan-var array{post_id:?int,post_type:?string,rest_base:?string}
	 */
	private $route_arr = array(
		'post_id'   => null,
		'post_type' => null,
		'rest_base' => null,
	);

	/**
	 * Used to store the state of `$this->match()`.
	 *
	 * @var bool True after `$this->match()` has been run.
	 */
	private $match_done = false;

	/**
	 * List of REST bases for the template (part) post type(s)
	 * with post type as array key.
	 *
	 * @var string[]
	 *
	 * @phpstan-var array<string,string>
	 */
	private $rest_bases = array();

	/**
	 * Constructor.
	 *
	 * @since 3.2
	 *
	 * @param  string $route A REST route.
	 * @return void
	 */
	public function __construct( $route ) {
		if ( ! is_string( $route ) ) {
			return;
		}

		$this->route = $route;
	}

	/**
	 * Returns the route.
	 *
	 * @since 3.2
	 *
	 * @return string
	 */
	public function get() {
		return $this->route;
	}

	/**
	 * Tells if the REST route is a template's route.
	 *
	 * @since 3.2
	 *
	 * @return bool
	 */
	public function is_template_route() {
		$this->match();

		return ! empty( $this->route_arr['post_type'] );
	}

	/**
	 * Returns the template post ID.
	 *
	 * @since 3.2
	 *
	 * @return int|null The template post ID, null if not found in the route.
	 */
	public function get_post_id() {
		$this->match();

		return $this->route_arr['post_id'];
	}

	/**
	 * Returns the template post type.
	 *
	 * @since 3.2
	 *
	 * @return string|null The template post type, null if the route doesn't match a template route.
	 */
	public function get_post_type() {
		$this->match();

		return $this->route_arr['post_type'];
	}

	/**
	 * Returns the REST base related to the template post type.
	 *
	 * @since 3.2
	 *
	 * @return string|null The REST base, null if the route doesn't match a template route.
	 */
	public function get_rest_base() {
		$this->match();

		return $this->route_arr['rest_base'];
	}

	/**
	 * Performs a regex match data in the route.
	 * This will fill in the property `$this->route_arr`.
	 *
	 * @since 3.2
	 *
	 * @return void
	 */
	private function match() {
		if ( $this->match_done ) {
			// Already ran.
			return;
		}

		$this->match_done = true;

		$bases_arr = $this->get_template_rest_bases();

		if ( empty( $bases_arr ) ) {
			return;
		}

		$bases = implode( '|', $bases_arr );

		$pattern = "@^(?<rest_base>$bases)(?:/(?<post_id>\d+))?(?:/|\?|#|$)@";
		$result  = preg_match( $pattern, $this->route, $matches );

		if ( empty( $result ) ) {
			return;
		}

		$post_types      = array_flip( $bases_arr );
		$this->route_arr = array(
			'post_id'   => isset( $matches['post_id'] ) ? (int) $matches['post_id'] : null,
			'post_type' => $post_types[ $matches['rest_base'] ],
			'rest_base' => $matches['rest_base'],
		);
	}

	/**
	 * Returns a list of REST "namespace + base" related to translated template types.
	 *
	 * @since 3.2
	 *
	 * @return string[] Post type as array key and REST "namespace + base" as array value,
	 *                  in the form of `/{namespace}/{base}`.
	 *
	 * @phpstan-return array<string,string>
	 */
	private function get_template_rest_bases() {
		if ( ! empty( $this->rest_bases ) ) {
			return $this->rest_bases;
		}

		foreach ( PLL_FSE_Tools::get_template_post_types() as $post_type ) {
			$obj = get_post_type_object( $post_type );

			if ( empty( $obj ) ) {
				continue;
			}

			$namespace = ! empty( $obj->rest_namespace ) && is_string( $obj->rest_namespace ) ? $obj->rest_namespace : 'wp/v2';
			$base      = ! empty( $obj->rest_base ) && is_string( $obj->rest_base ) ? $obj->rest_base : $obj->name;

			$this->rest_bases[ $post_type ] = "/{$namespace}/{$base}";
		}

		return $this->rest_bases;
	}
}
