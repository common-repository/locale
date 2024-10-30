<?php
/**
 * Plugin Name: Locale
 * Plugin URI:  https://www.locale.to/connectors/
 * Description: Translate your content from a WordPress Multisite and MultilingualPress.
 * Version:     1.0.0
 * Author:      Locale
 * Author URI:  https://www.locale.to/
 * Text Domain: Locale
 * License:     GPLv2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires PHP: 5.6
 * Domain Path: /languages
 *
 * @package Locale
 */

// phpcs:disable

use Locale\Plugin;
use Locale\Service\ServiceProviders;

$bootstrap = \Closure::bind( function () {

	/**
	 * Admin Notice
	 *
	 * @param string $message  The message to show in the notice.
	 * @param string $severity The severity of the notice. Can be one of `success`, `warning`, `error`.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	function locale_admin_notice( $message, $severity ) {

		printf(
			'<div class="notice notice-%1$s"><p>%2$s</p></div>',
			sanitize_html_class( sanitize_key( $severity ) ),
			wp_kses_post( $message )
		);
	}

	/**
	 * Test Plugin Stuffs
	 *
	 * @return bool True when ok, false otherwise.
	 * @since 1.0.0
	 */
	function locale_plugin_tests_pass() {

		$requirements = new Locale\Requirements();

		// Check the requirements and in case prevent code execution by returning.
		if ( ! $requirements->is_php_version_ok() ) {
			add_action( 'admin_notices', function () use ( $requirements ) {

				locale_admin_notice( sprintf( esc_html__( // phpcs:ignore
					'Locale requires PHP version %1$s or higher. You are running version %2$s.',
					'locale'
				),
					Locale\Requirements::PHP_MIN_VERSION,
					Locale\Requirements::PHP_CURR_VERSION
				), 'error' );
			} );

			return false;
		}

		// Show Notice in case Token or URL isn't set.
		if ( ! get_option( \Locale\Setting\PluginSettings::API_KEY ) ) {
			add_action( 'admin_notices', function () use ( $requirements ) {

				locale_admin_notice(
					wp_kses( sprintf( __( // phpcs:ignore
						'Locale seems to be configured incorrectly. Please set an API key from %s in order to request translations.',
						'locale'
					),
						'<strong><a href="' . esc_url( menu_page_url( \Locale\Pages\PageOptions::SLUG,
							false ) ) . '">' . esc_html__( 'here', 'locale' ) . '</a></strong>'
					),
						[
							'a'      => [ 'href' => true ],
							'strong' => [],
						]
					),
					'error'
				);
			} );
		}

		return true;
	}

	/**
	 * BootStrap
	 *
	 * @return void
	 * @since 1.0.0
	 */
	function bootstrap() {

		if ( !is_admin() && !is_wp_cli() ) {
			return;
		}

		// Require composer autoloader if exists.
		if ( is_readable( __DIR__ . '/vendor/autoload.php' )
		     && ! class_exists( Plugin::class )
		) {
			require_once __DIR__ . '/vendor/autoload.php';
		}
		if ( ! class_exists( Plugin::class ) ) {
			add_action( 'admin_notices', function () {

				locale_admin_notice(
					esc_html__( 'Locale autoloading failed!', 'locale' ),
					'error'
				);
			} );

			return;
		}

		// Require functions and basic files.
		require_once __DIR__ . '/inc/hooks.php';
		foreach ( glob( __DIR__ . '/inc/functions/*.php' ) as $file ) {
			require_once $file;
		}

		if ( ! locale_plugin_tests_pass() ) {
			return;
		}

		$container = new Pimple\Container();

		$container['locale.plugin'] = function () {
			return new Plugin();
		};

		$providers = new ServiceProviders( $container );
		$providers
			->register( new Locale\JobItem\ServiceProvider() )
			->register( new Locale\Job\ServiceProvider() )
			->register( new Locale\Pages\ServiceProvider() )
			->register( new Locale\Setting\ServiceProvider() )
			->register( new Locale\TableList\ServiceProvider() )
			->register( new Locale\Assets\ServiceProvider() )
			->register( new Locale\Request\ServiceProvider() )
			->register( new Locale\SystemStatus\ServiceProvider() )
			->register( new Locale\Activation\ServiceProvider() )
			->register( new Locale\Module\ServiceProvider() );

		$providers
			->bootstrap()
			->integrate();

		unset( $container );
	}

    /**
     * Check if is WP_CLI
     *
     * @return bool
     * @since 1.2.1
     */
    function is_wp_cli() {
        return defined('WP_CLI') && WP_CLI;
    }

	/**
	 * Activate Plugin
	 *
	 * @return void
	 * @since 1.0.0
	 */
	function activate() {

		add_action( 'activated_plugin', function ( $plugin ) {

			if ( plugin_basename( __FILE__ ) === $plugin ) {
				bootstrap();
			}
		}, 0 );
	}

	add_action( 'plugins_loaded', 'bootstrap', - 1 );

	register_activation_hook( __FILE__, 'activate' );
}, null );

$bootstrap();
