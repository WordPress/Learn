<?php
/**
 * @package Polylang Updater
 */

namespace WP_Syntex\Polylang_Pro\Updater;

defined( 'ABSPATH' ) || exit;

/**
 * A class to easily manage licenses for Polylang Pro and addons.
 *
 * @since 1.0
 */
class License {
	/**
	 * URL to Polylang's account page.
	 *
	 * @var string
	 */
	public const ACCOUNT_URL = 'https://polylang.pro/my-account/';

	/**
	 * Sanitized plugin name.
	 *
	 * @var string
	 */
	public $id;

	/**
	 * Plugin name.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * License key.
	 *
	 * @var string
	 */
	public $license_key;

	/**
	 * License data, obtained from the API request.
	 *
	 * @var \stdClass|null
	 */
	public $license_data;

	/**
	 * Main plugin file.
	 *
	 * @var string
	 */
	private $file;

	/**
	 * Current plugin version.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Plugin author.
	 *
	 * @var string
	 */
	private $author = 'WP SYNTEX';

	/**
	 * API url.
	 *
	 * @var string.
	 */
	private $api_url = 'https://polylang.pro';

	/**
	 * Constructor.
	 *
	 * @since 1.0
	 *
	 * @param string $file      The plugin file.
	 * @param string $item_name The plugin name.
	 * @param string $version   The plugin version.
	 */
	public function __construct( string $file, string $item_name, string $version ) {
		$this->id      = sanitize_title( $item_name );
		$this->file    = $file;
		$this->name    = $item_name;
		$this->version = $version;

		$licenses          = (array) get_option( 'polylang_licenses', array() );
		$license           = isset( $licenses[ $this->id ] ) && is_array( $licenses[ $this->id ] ) ? $licenses[ $this->id ] : array();
		$this->license_key = ! empty( $license['key'] ) ? (string) $license['key'] : '';

		if ( ! empty( $license['data'] ) ) {
			$this->license_data = (object) $license['data'];
		}

		// Updater.
		$this->auto_updater();

		// Register settings.
		add_filter( 'pll_settings_modules', array( $this, 'settings_module' ) );
		add_filter( 'pll_settings_licenses', array( $this, 'settings' ) );

		// Weekly schedule.
		if ( ! wp_next_scheduled( 'polylang_check_licenses' ) ) {
			wp_schedule_event( time(), 'weekly', 'polylang_check_licenses' );
		}

		add_action( 'polylang_check_licenses', array( $this, 'check_license' ) );
	}

	/**
	 * Creates a new instance of the auto-updater.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function auto_updater(): void {
		$args = array(
			'version'   => $this->version,
			'license'   => $this->license_key,
			'author'    => $this->author,
			'item_name' => $this->name,
		);

		// Setup the updater.
		new EDD_SL_Plugin_Updater( $this->api_url, $this->file, $args );
	}

	/**
	 * Registers the settings module.
	 * Hooked to `pll_settings_modules`.
	 *
	 * @since 1.0
	 *
	 * @param string[] $modules The list of module classes.
	 * @return string[]
	 */
	public function settings_module( $modules ) {
		/*
		 * Backward compatibility with Polylang < 3.7.
		 * Remove old license settings class to avoid duplicate.
		 */
		$modules = array_diff( $modules, array( 'PLL_Settings_Licenses' ) );

		if ( empty( $modules['licenses'] ) ) {
			$modules['licenses'] = Settings::class;
		}
		return $modules;
	}

	/**
	 * Registers the licence in the Settings.
	 * Hooked to `pll_settings_licenses`.
	 *
	 * @since 1.0
	 *
	 * @param License[] $items Array of objects allowing to manage a license.
	 * @return License[]
	 */
	public function settings( $items ) {
		$items[ $this->id ] = $this;
		return $items;
	}

	/**
	 * Activates the license key.
	 *
	 * @since 1.0
	 *
	 * @param string $license_key Activation key.
	 * @return self Updated License object.
	 */
	public function activate_license(
		#[\SensitiveParameter]
		string $license_key
	): self {
		$this->license_key = $license_key;
		$this->api_request( 'activate_license' );

		// Tell WordPress to look for updates.
		delete_site_transient( 'update_plugins' );
		return $this;
	}


	/**
	 * Deactivates the license key.
	 *
	 * @since 1.0
	 *
	 * @return self Updated License object.
	 */
	public function deactivate_license(): self {
		$this->api_request( 'deactivate_license' );
		return $this;
	}

	/**
	 * Checks if the license key is valid.
	 * Hooked to `polylang_check_licenses`.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function check_license(): void {
		$this->api_request( 'check_license' );
	}

	/**
	 * Sends an API request to check, activate or deactivate the license.
	 * Updates the licenses option according to the status.
	 *
	 * @since 1.0
	 *
	 * @param string $request Type of request: check_license | activate_license | deactivate_license.
	 * @return void
	 *
	 * @phpstan-param 'check_license'|'activate_license'|'deactivate_license' $request
	 */
	private function api_request( string $request ): void {
		$licenses = get_option( 'polylang_licenses' );

		if ( is_array( $licenses ) ) {
			unset( $licenses[ $this->id ] );
		} else {
			$licenses = array();
		}
		unset( $this->license_data );

		if ( ! empty( $this->license_key ) ) {
			// Data to send in our API request.
			$api_params = array(
				'edd_action' => $request,
				'license'    => $this->license_key,
				'item_name'  => rawurlencode( $this->name ),
				'url'        => home_url(),
			);

			// Call the API.
			$response = wp_remote_post(
				$this->api_url,
				array(
					'timeout'   => 3,
					'sslverify' => false,
					'body'      => $api_params,
				)
			);

			// Update the option only if we got a response.
			if ( is_wp_error( $response ) ) {
				return;
			}

			// Save new license info.
			$licenses[ $this->id ] = array( 'key' => $this->license_key );

			$data = (object) json_decode( wp_remote_retrieve_body( $response ) );

			if ( isset( $data->license ) && 'deactivated' !== $data->license ) {
				$licenses[ $this->id ]['data'] = $data;
				$this->license_data            = $data;
			}
		}

		update_option( 'polylang_licenses', $licenses ); // FIXME called multiple times when saving all licenses.
	}

	/**
	 * Returns the HTML form field in a table row (one per license key) for display.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public function get_form_field(): string {
		if ( ! empty( $this->license_data ) ) {
			$license = $this->license_data;
		}

		$atts = array(
			'id'          => $this->id,
			'name'        => $this->name,
			'license_key' => $this->license_key,
			'row_class'   => 'license-null',
			'button_text' => '',
			'message'     => '',
		);

		if ( ! empty( $license ) && is_object( $license ) ) {
			$now        = time();
			$expiration = isset( $license->expires ) ? strtotime( $license->expires ) : false;

			// Special case: the license expired after the last check.
			if ( $license->success && $expiration && $expiration < $now ) {
				$license->success = false;
				$license->error   = 'expired';
			}

			if ( false === $license->success ) {
				$atts['row_class'] = 'notice-error notice-alt';

				if ( empty( $license->error ) && ! empty( $license->license ) ) {
					$license->error = $license->license;
				}

				switch ( $license->error ) {
					case 'expired':
						$atts['message'] = sprintf(
							/* translators: %1$s is a date, %2$s is link start tag, %3$s is link end tag. */
							__( 'Your license key expired on %1$s. Please %2$srenew your license key%3$s.', 'polylang-pro' ),
							date_i18n( get_option( 'date_format' ), $expiration ),
							sprintf( '<a href="%s" target="_blank">', self::ACCOUNT_URL ),
							'</a>'
						);
						break;

					case 'disabled':
					case 'revoked':
						$atts['message'] = __( 'Your license key has been disabled.', 'polylang-pro' );
						break;

					case 'missing':
						$atts['message'] = sprintf(
							/* translators: %1$s is link start tag, %2$s is link end tag. */
							__( 'Invalid license. Please %1$svisit your account page%2$s and verify it.', 'polylang-pro' ),
							sprintf( '<a href="%s" target="_blank">', self::ACCOUNT_URL ),
							'</a>'
						);
						break;

					case 'invalid':
					case 'site_inactive':
						$atts['message'] = sprintf(
							/* translators: %1$s is a product name, %2$s is link start tag, %3$s is link end tag. */
							__( 'Your %1$s license key is not active for this URL. Please %2$svisit your account page%3$s to manage your license key URLs.', 'polylang-pro' ),
							$this->name,
							sprintf( '<a href="%s" target="_blank">', self::ACCOUNT_URL ),
							'</a>'
						);
						break;

					case 'item_name_mismatch':
						/* translators: %s is a product name */
						$atts['message'] = sprintf( __( 'This is not a %s license key.', 'polylang-pro' ), $this->name );
						break;

					case 'no_activations_left':
						$atts['message'] = sprintf(
							/* translators: %1$s is link start tag, %2$s is link end tag */
							__( 'Your license key has reached its activation limit. %1$sView possible upgrades%2$s now.', 'polylang-pro' ),
							sprintf( '<a href="%s" target="_blank">', self::ACCOUNT_URL ),
							'</a>'
						);
						break;
				}
			} else {
				$atts['row_class']   = 'license-valid';
				$atts['button_text'] = __( 'Deactivate', 'polylang-pro' );

				if ( 'lifetime' === $license->expires ) {
					$atts['message'] = __( 'The license key never expires.', 'polylang-pro' );
				} elseif ( $expiration > $now && $expiration - $now < ( DAY_IN_SECONDS * 30 ) ) {
					$atts['row_class'] = 'notice-warning notice-alt';
					$atts['message']   = sprintf(
						/* translators: %1$s is a date, %2$s is link start tag, %3$s is link end tag. */
						__( 'Your license key will expire soon! Precisely, it will expire on %1$s. %2$sRenew your license key today!%3$s', 'polylang-pro' ),
						date_i18n( get_option( 'date_format' ), $expiration ),
						sprintf( '<a href="%s" target="_blank">', self::ACCOUNT_URL ),
						'</a>'
					);
				} else {
					$atts['message'] = sprintf(
						/* translators: %s is a date */
						__( 'Your license key expires on %s.', 'polylang-pro' ),
						date_i18n( get_option( 'date_format' ), $expiration )
					);
				}
			}
		}

		ob_start();
		include __DIR__ . '/views/view-field-row.php';
		return (string) ob_get_clean();
	}
}
