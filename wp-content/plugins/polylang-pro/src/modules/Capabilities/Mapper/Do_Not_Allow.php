<?php
/**
 * @package Polylang-Pro
 */

namespace WP_Syntex\Polylang_Pro\Modules\Capabilities\Mapper;

/**
 * Concrete implementation of the `Abstract_Mapper` class.
 * It is used to break early the chain of mappers when a `do_not_allow` capability is present.
 *
 * @since 3.8
 */
class Do_Not_Allow extends Abstract_Mapper {}
